<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->getTranslations('title'),
            'description'     => $this->getTranslations('description'),
            'instructor_name' => $this->instructor_name,
            'price'           => $this->price,
            'start_date'      => $this->start_date?->toDateString(),
            'whatsapp_link'   => $this->whatsapp_link,
            'is_coming_soon'  => $this->is_coming_soon,
            'display_order'   => $this->display_order,
        ];
    }
}
