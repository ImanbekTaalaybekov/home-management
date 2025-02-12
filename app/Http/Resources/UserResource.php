<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function __construct($resource, protected bool $hideEmail = false)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return  [
            'id' => $this->id,
            'status' => $this->status,
            'level' => $this->level,
            'email' => $this->hideEmail ? null : $this->email,
        ];
    }
}

