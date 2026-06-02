<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TahsinatItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'category_id'   => $this->tahsinat_category_id,
            'label'         => $this->getTranslations('label'),
            'text'          => $this->getTranslations('text'),
            'repetitions'   => $this->repetitions,
            'hint'          => $this->getTranslations('hint'),
            'display_order' => $this->display_order,
        ];
    }
}
