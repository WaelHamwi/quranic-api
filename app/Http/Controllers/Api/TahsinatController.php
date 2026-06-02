<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TahsinatCategoryResource;
use App\Http\Resources\TahsinatItemResource;
use App\Services\TahsinatService;
use Illuminate\Http\JsonResponse;

class TahsinatController extends Controller
{
    public function __construct(private TahsinatService $service) {}

    public function categories(): JsonResponse
    {
        try {
            return $this->success(TahsinatCategoryResource::collection($this->service->categories()));
        } catch (\Throwable $e) {
            return $this->error('Server error', 500);
        }
    }

    public function items(string $slug): JsonResponse
    {
        try {
            $category = $this->service->getCategoryBySlug($slug);

            if (! $category) {
                return $this->error('Tahsinat category not found', 404);
            }

            return $this->success(TahsinatItemResource::collection($this->service->itemsByCategorySlug($slug)));
        } catch (\Throwable $e) {
            return $this->error('Server error', 500);
        }
    }
}
