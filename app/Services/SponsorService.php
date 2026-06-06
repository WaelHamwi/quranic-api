<?php

namespace App\Services;

use App\Models\SponsorScreenConfig;
use App\Repositories\Contracts\SponsorRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SponsorService
{
    private const CACHE_ALL = 'sponsors.v1.all';

    private const CACHE_SCREEN = 'sponsors.v1.screen';

    private const CACHE_TTL = 300;

    public function __construct(private SponsorRepositoryInterface $repository) {}

    public function getAll(): Collection
    {
        return Cache::remember(self::CACHE_ALL, self::CACHE_TTL, fn () => $this->repository->getAll());
    }

    public function screenConfig(): SponsorScreenConfig
    {
        return Cache::remember(self::CACHE_SCREEN, self::CACHE_TTL, fn () => $this->repository->screenConfig());
    }

    /**
     * Drop the cached sponsor payloads so admin edits (e.g. toggling the
     * sponsor screen on/off) take effect on the very next API request.
     */
    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_ALL);
        Cache::forget(self::CACHE_SCREEN);
    }
}
