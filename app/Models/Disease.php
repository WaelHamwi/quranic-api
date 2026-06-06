<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\Concerns\HasTranslations;

class Disease extends Model
{
    use HasTranslations, SoftDeletes;

    protected $fillable = [
        'subcategory_id', 'name', 'slug', 'icon',
        'display_order', 'is_active',
    ];

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
            'subcategory_id' => 'integer',
            'display_order'  => 'integer',
            'is_active'      => 'boolean',
        ];
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    /** Absolute URL to the uploaded icon (SVG/PNG), or null when none is set. */
    public function iconUrl(): ?string
    {
        if (! $this->icon) {
            return null;
        }

        return str_starts_with($this->icon, 'http')
            ? $this->icon
            : asset('storage/' . ltrim($this->icon, '/'));
    }

    public function recordings(): HasMany
    {
        return $this->hasMany(Recording::class);
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(DiseaseAlias::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
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
