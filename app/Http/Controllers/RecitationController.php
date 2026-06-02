<?php

namespace App\Http\Controllers;

use App\Http\Resources\RecitationResource;
use App\Models\Recitation;
use App\Services\RecitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecitationController extends Controller
{
    public function __construct(private RecitationService $service) {}

    public function bySurah(int $surahId): JsonResponse
    {
        try {
            $recitations = $this->service->getBySurah($surahId);

            return $this->success(RecitationResource::collection($recitations));
        } catch (\Throwable $e) {
            Log::error('RecitationController@bySurah surah=' . $surahId . ': ' . $e->getMessage());
            return $this->error('Server error', 500);
        }
    }

    /**
     * Stream audio for a recitation.
     * - Local files   → served directly from public disk (Range-request aware).
     * - CDN URLs      → proxied through the server with Range header forwarding
     *                   so the mobile never makes a raw CDN request.
     */
    public function audio(Request $request, int $id): BinaryFileResponse|StreamedResponse|JsonResponse
    {
        try {
            $recitation = Recitation::find($id);

            if (!$recitation) {
                return $this->error('Recitation not found', 404);
            }

            $path = (string) $recitation->audio_path;

            // ── Local file ────────────────────────────────────────────────────
            if (!str_starts_with($path, 'http')) {
                /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                $disk = Storage::disk('public');
                if (!$disk->exists($path)) {
                    return $this->error('Audio file not found', 404);
                }
                $absPath = storage_path('app/public/' . ltrim($path, '/'));
                return response()->file($absPath, ['Content-Type' => 'audio/mpeg']);
            }

            // ── CDN URL — proxy with Range support ───────────────────────────
            $clientHeaders = [];
            if ($rangeHeader = $request->header('Range')) {
                $clientHeaders['Range'] = $rangeHeader;
            }

            $cdnResponse = Http::withOptions([
                'verify'  => false,
                'stream'  => true,
                'timeout' => 60,
            ])->withHeaders($clientHeaders)->get($path);

            $status = $cdnResponse->status();
            if ($status >= 400) {
                Log::error("RecitationController@audio CDN returned {$status} for recitation {$id}: {$path}");
                return $this->error('Audio source unavailable', 502);
            }

            $responseHeaders = ['Content-Type' => 'audio/mpeg', 'Accept-Ranges' => 'bytes'];
            if ($len   = $cdnResponse->header('Content-Length'))  { $responseHeaders['Content-Length']  = $len; }
            if ($range = $cdnResponse->header('Content-Range'))   { $responseHeaders['Content-Range']   = $range; }

            $stream = $cdnResponse->toPsrResponse()->getBody();

            return response()->stream(function () use ($stream) {
                while (!$stream->eof()) {
                    echo $stream->read(8192);
                    if (connection_aborted()) {
                        break;
                    }
                }
            }, $status, $responseHeaders);

        } catch (\Throwable $e) {
            Log::error('RecitationController@audio id=' . $id . ': ' . $e->getMessage());
            return $this->error('Server error', 500);
        }
    }

    public function download(int $id): StreamedResponse|JsonResponse
    {
        try {
            $recitation = Recitation::find($id);

            if (!$recitation) {
                return $this->error('Recitation not found', 404);
            }

            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('public');

            if (!$disk->exists($recitation->audio_path)) {
                return $this->error('Audio file not found', 404);
            }

            return $disk->download($recitation->audio_path);
        } catch (\Throwable $e) {
            Log::error('RecitationController@download id=' . $id . ': ' . $e->getMessage());
            return $this->error('Server error', 500);
        }
    }
}
