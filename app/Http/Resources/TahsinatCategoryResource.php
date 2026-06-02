<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TahsinatCategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->getTranslations('name'),
            'slug'          => $this->slug,
            'is_self'       => $this->is_self,
            'is_for_others' => $this->is_for_others,
            'random_order'  => $this->random_order,
            'display_order' => $this->display_order,
            'items_count'   => $this->whenCounted('items'),
            'items'         => TahsinatItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
