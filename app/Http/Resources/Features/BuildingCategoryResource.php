<?php

namespace App\Http\Resources\Features;

use Illuminate\Http\Resources\Json\JsonResource;

class BuildingCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'icon' => $this->icon,
        ];
    }
}
