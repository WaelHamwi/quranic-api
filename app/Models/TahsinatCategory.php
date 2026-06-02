<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahsinatCategory extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name', 'slug', 'is_self', 'is_for_others',
        'random_order', 'display_order', 'is_active',
    ];

    public array $translatable = ['name'];

    protected function casts(): array
    {
        return [
            'is_self'       => 'boolean',
            'is_for_others' => 'boolean',
            'random_order'  => 'boolean',
            'display_order' => 'integer',
            'is_active'     => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(TahsinatItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSelf(Builder $query): Builder
    {
        return $query->where('is_self', true);
    }

    public function scopeForOthers(Builder $query): Builder
    {
        return $query->where('is_for_others', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order')->orderBy('id');
    }
}
