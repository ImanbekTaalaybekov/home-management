<?php

namespace App\Http\Resources\Features;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jenssegers\Date\Date;

class KnowledgeContactResource extends JsonResource
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
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'additional_contacts' => $this->additional_contacts,
            'created_at' => Date::parse($this->created_at)->translatedFormat('j F Y'),
        ];
    }
}
