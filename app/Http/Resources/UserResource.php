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
            'name' => $this->name,
            'personal_account' => $this->personal_account,
            'phone_number' => $this->phone_number,
            'residential_complex_id' => $this->residential_complex_id,
            'block_number' => $this->block_number,
            'apartment_number' => $this->apartment_number,
        ];
    }
}
