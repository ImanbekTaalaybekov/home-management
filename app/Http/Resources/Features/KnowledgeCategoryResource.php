<?php

namespace App\Http\Resources\Features;

use Illuminate\Http\Resources\Json\JsonResource;

class KnowledgeCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'children' => KnowledgeCategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}
