<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Verse extends Model
{
    use HasTranslations, SoftDeletes;

    protected $fillable = [
        'surah_id',
        'verse_number',
        'text',
    ];

    public array $translatable = ['text'];

    protected $casts = [
        'verse_number' => 'integer',
    ];

    public function surah(): BelongsTo
    {
        return $this->belongsTo(Surah::class);
    }

    public function scopeBySurah(Builder $query, int $surahId): Builder
    {
        return $query->where('surah_id', $surahId)->orderBy('verse_number');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('text->ar', 'like', "%{$term}%")
              ->orWhere('text->en', 'like', "%{$term}%");
        });
    }
}
