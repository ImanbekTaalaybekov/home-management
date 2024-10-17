<?php

namespace App\Http\Resources\Features;

use Illuminate\Http\Request;

class KnowledgeDocumentResource extends TranslatableJsonResource
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
            ... $this->toArrayTranslatable(['files']),
        ];
    }
}
