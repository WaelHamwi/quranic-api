<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RecordingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                    => $this->id,
            'disease_id'            => $this->disease_id,
            'category_id'           => $this->category_id,
            'subcategory_id'        => $this->subcategory_id,
            'session_number'        => $this->session_number,
            'title'                 => $this->getTranslations('title'),
            'description'           => $this->getTranslations('description'),
            'audio_url'             => $this->streamUrl(),
            'duration_seconds'      => $this->duration_seconds,
            'is_general'            => $this->is_general,
            'is_free'               => $this->isFreeSession(),
            'requires_subscription' => ! $this->isFreeSession(),
            'plays_count'           => $this->plays_count,
        ];
    }
}
