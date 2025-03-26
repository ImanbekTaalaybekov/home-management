<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class NotificationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'user_id' => $this->user_id,
            'residential_complex_id' => $this->residential_complex_id,
            'photos' => $this->photos, // или использовать ресурс для фото, если нужно
            'has_pdf' => (bool) $this->document,
            'pdf_url' => $this->document ? Storage::url($this->document) : null,
            'created_at' => $this->created_at,
        ];
    }
}

