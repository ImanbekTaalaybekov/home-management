<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
{
    public function __construct($resource, protected bool $hideEmail = false)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,
            'role' => $this->role,
            'accesses' => $this->accesses,
            'client_id' => $this->client_id
        ];
    }
}
