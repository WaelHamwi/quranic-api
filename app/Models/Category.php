<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\Concerns\HasTranslations;

class Category extends Model
{
    use HasTranslations, SoftDeletes;

    protected $fillable = ['name', 'slug', 'icon', 'type', 'display_order', 'is_active'];

    protected static function booted(): void
    {
        static::creating(fn (self $r) => static::assignSlug($r));
        static::updating(function (self $r): void {
            if ($r->isDirty('name')) {
                static::assignSlug($r);
            }
        });
    }

    private static function assignSlug(self $record): void
    {
        $en = $record->getTranslation('name', 'en', false);
        $base = $en
            ? Str::slug($en)
            : Str::slug(Str::transliterate($record->getTranslation('name', 'ar', false) ?? ''));

        if (! $base) {
            return;
        }

        $slug = $base;
        $n    = 1;
        while (
            static::withTrashed()
                ->where('slug', $slug)
                ->when($record->exists, fn ($q) => $q->where('id', '!=', $record->id))
                ->exists()
        ) {
            $slug = $base . '-' . $n++;
        }

        $record->slug = $slug;
    }

    public array $translatable = ['name'];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'is_active'     => 'boolean',
        ];
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class);
    }

    /** Recordings attached directly to this category (type = 'direct'). */
    public function recordings(): HasMany
    {
        return $this->hasMany(Recording::class);
    }

    public function isDirect(): bool
    {
        return $this->type === 'direct';
    }

    /** Absolute URL to the uploaded icon (SVG/PNG). Legacy heroicon names → null. */
    public function iconUrl(): ?string
    {
        if (! $this->icon || str_starts_with($this->icon, 'heroicon')) {
            return null;
        }

        return str_starts_with($this->icon, 'http')
            ? $this->icon
            : asset('storage/' . ltrim($this->icon, '/'));
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
