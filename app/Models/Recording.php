<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recording extends Model
{
    use HasTranslations, SoftDeletes;

    protected $fillable = [
        'disease_id', 'session_number', 'title', 'audio_path',
        'duration_seconds', 'is_general', 'plays_count', 'created_by',
    ];

    public array $translatable = ['title'];

    protected function casts(): array
    {
        return [
            'disease_id'       => 'integer',
            'session_number'   => 'integer',
            'duration_seconds' => 'integer',
            'is_general'       => 'boolean',
            'plays_count'      => 'integer',
            'created_by'       => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Recording $recording) {
            if (! $recording->session_number) {
                $last = static::where('disease_id', $recording->disease_id)
                    ->max('session_number');
                $recording->session_number = ($last ?? 0) + 1;
            }
        });
    }

    public function disease(): BelongsTo
    {
        return $this->belongsTo(Disease::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeFree(Builder $query): Builder
    {
        return $query->where('session_number', 1);
    }

    public function scopePremium(Builder $query): Builder
    {
        return $query->where('session_number', '>', 1);
    }

    public function scopeGeneral(Builder $query): Builder
    {
        return $query->where('is_general', true);
    }

    public function isFreeSession(): bool
    {
        return $this->session_number === 1;
    }

    public function streamUrl(): ?string
    {
        if (! $this->audio_path) {
            return null;
        }

        return str_starts_with($this->audio_path, 'http')
            ? $this->audio_path
            : asset('storage/' . ltrim($this->audio_path, '/'));
    }

    public function canBeAccessedBy(?User $user): bool
    {
        if ($this->isFreeSession()) {
            return true;
        }

        return $user !== null && ($user->isSubscribed() || $user->hasActiveTrial());
    }
}
