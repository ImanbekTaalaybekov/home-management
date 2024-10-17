<?php

namespace App\Http\Resources\Features;

use Illuminate\Http\Resources\Json\JsonResource;
use Jenssegers\Date\Date;

class BuildingResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'phone_numbers' => json_decode($this->phone_numbers, true),
            'address' => $this->address,
            'working_hours' => $this->working_hours,
            'city' => CityResource::make($this->whenLoaded('city')),
            'category' => BuildingCategoryResource::make($this->whenLoaded('category')),
            'images' => $this->getMedia('images')->map(function ($media) {
                return [
                    'small' => $media->getUrl('small'),
                    'preview' => $media->getUrl('preview'),
                    'large' =>  $media->getUrl('large'),
                ];
            }),
            'created_at' => Date::parse($this->created_at)->translatedFormat('j F Y'),
            'has_favorite' => auth('sanctum')->check() && $this->favorites()->where('user_id', auth('sanctum')->id())->exists(),
        ];
    }
}
