<?php

namespace App\Repositories;

use App\Models\Disease;
use App\Repositories\Contracts\DiseaseRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DiseaseRepository implements DiseaseRepositoryInterface
{
    public function paginate(int $perPage): LengthAwarePaginator
    {
        return Disease::active()
            ->ordered()
            ->with('subcategory')
            ->withCount('recordings')
            ->paginate($perPage);
    }

    public function findBySlug(string $slug): ?Disease
    {
        return Disease::active()
            ->where('slug', $slug)
            ->with([
                'subcategory.category',
                'aliases',
                'recordings' => fn ($q) => $q->orderBy('session_number'),
            ])
            ->first();
    }

    public function search(string $term): Collection
    {
        return Disease::active()
            ->where(function (Builder $query) use ($term) {
                $query->where('name->ar', 'like', "%{$term}%")
                    ->orWhere('name->en', 'like', "%{$term}%")
                    ->orWhereHas('aliases', function (Builder $alias) use ($term) {
                        $alias->where('alias->ar', 'like', "%{$term}%")
                            ->orWhere('alias->en', 'like', "%{$term}%");
                    });
            })
            ->ordered()
            ->with('subcategory')
            ->limit(50)
            ->get();
    }

}
