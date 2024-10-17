<?php

namespace App\Http\Resources\Features;

use Illuminate\Http\Request;
use Jenssegers\Date\Date;

class KnowledgeArticleResource extends TranslatableJsonResource
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
            'description' => $this->description,
            'image_paths' => $this->image_files,
            'created_at' => Date::parse($this->created_at)->translatedFormat('j F Y'),
            ... $this->toArrayTranslatable(['audio_files', 'pdf_files']),
        ];
    }
}
