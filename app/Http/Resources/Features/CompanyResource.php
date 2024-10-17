<?php

namespace App\Http\Resources\Features;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'user' => new UserResource($this->user, hideEmail: true),
            'city' => new CityResource($this->city),
            'phone' => $this->phone,
            'additional_contacts' => $this->additional_contacts,
            'about' => $this->about,
            'image' => $this->getMedia('company_logo')->map(function ($media) {
                return [
                    'small' => $media->getUrl('small'),
                    'preview' => $media->getUrl('medium'),
                ];
            }),
           'responsible_person' => $this->responsible_person,
           'email' => $this->email
        ];
    }
}
