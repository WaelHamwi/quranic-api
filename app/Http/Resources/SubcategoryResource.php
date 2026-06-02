<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubcategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'category_id'   => $this->category_id,
            'name'          => $this->getTranslations('name'),
            'slug'          => $this->slug,
            'display_order' => $this->display_order,
            'category'      => new CategoryResource($this->whenLoaded('category')),
            'diseases'      => DiseaseResource::collection($this->whenLoaded('diseases')),
        ];
    }
}
