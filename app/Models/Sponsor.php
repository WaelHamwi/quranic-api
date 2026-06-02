<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name', 'logo_path', 'website_url', 'target_countries', 'target_genders',
        'is_featured', 'display_on_launch', 'display_order', 'is_active',
    ];

    public array $translatable = ['name'];

    protected function casts(): array
    {
        return [
            'target_countries'  => 'array',
            'target_genders'    => 'array',
            'is_featured'       => 'boolean',
            'display_on_launch' => 'boolean',
            'display_order'     => 'integer',
            'is_active'         => 'boolean',
        ];
    }

    public function logoUrl(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return str_starts_with($this->logo_path, 'http')
            ? $this->logo_path
            : asset('storage/' . ltrim($this->logo_path, '/'));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order')->orderBy('id');
    }
}
