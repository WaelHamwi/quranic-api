<?php

namespace App\Services;

use App\Models\SponsorScreenConfig;
use App\Repositories\Contracts\SponsorRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SponsorService
{
    public function __construct(private SponsorRepositoryInterface $repository) {}

    public function getAll(): Collection
    {
        return Cache::remember('sponsors.v1.all', 300, fn () => $this->repository->getAll());
    }

    public function screenConfig(): SponsorScreenConfig
    {
        return Cache::remember('sponsors.v1.screen', 300, fn () => $this->repository->screenConfig());
    }
}
