<?php

namespace App\Http\Resources\Features;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsultationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'fullname' => $this->fullname,
            'disability_group' => $this->disability_group,
            'disability_type' => $this->disability_type,
            'question' => $this->question,
            'files' => $this->getMedia('consultation')->map(fn($media) => $media->getUrl())
        ];
    }
}
