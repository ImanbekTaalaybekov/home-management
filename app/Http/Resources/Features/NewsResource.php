<?php

namespace App\Http\Resources\Features;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jenssegers\Date\Date;

class NewsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'category' => NewsCategoryResource::make($this->whenLoaded('category')),
            'published_at' => Date::parse($this->published_at)->translatedFormat('j F Y'),
            'images' => $this->getMedia('images')->map(function ($media) {
                return [
                    'small' => $media->getUrl('thumb'),
                    'preview' => $media->getUrl('preview'),
                ];
            }),
            'is_published' => $this->is_published,
        ];
    }
}
