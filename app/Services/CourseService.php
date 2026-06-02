<?php

namespace App\Services;

use App\Repositories\Contracts\CourseRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CourseService
{
    public function __construct(private CourseRepositoryInterface $repository) {}

    public function getAll(): Collection
    {
        return Cache::remember('courses.v1.all', 300, fn () => $this->repository->getAll());
    }
}
