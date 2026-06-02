<?php

namespace App\Repositories;

use App\Models\TahsinatCategory;
use App\Repositories\Contracts\TahsinatRepositoryInterface;
use Illuminate\Support\Collection;

class TahsinatRepository implements TahsinatRepositoryInterface
{
    public function categories(): Collection
    {
        return TahsinatCategory::active()->ordered()->withCount('items')->get();
    }

    public function findCategoryBySlug(string $slug): ?TahsinatCategory
    {
        return TahsinatCategory::active()
            ->where('slug', $slug)
            ->with(['items' => fn ($q) => $q->ordered()])
            ->first();
    }

    public function itemsByCategorySlug(string $slug): Collection
    {
        $category = TahsinatCategory::active()->where('slug', $slug)->first();

        if (! $category) {
            return new Collection();
        }

        $items = $category->items()->ordered()->get();

        return $category->random_order ? $items->shuffle()->values() : $items;
    }
}
