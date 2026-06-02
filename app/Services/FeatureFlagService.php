<?php

namespace App\Services;

use App\Repositories\Contracts\FeatureFlagRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class FeatureFlagService
{
    public function __construct(private FeatureFlagRepositoryInterface $repository) {}

    public function all(): array
    {
        return Cache::remember('features.v1.all', 300, fn () => $this->repository->all());
    }
}
