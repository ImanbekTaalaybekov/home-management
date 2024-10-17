<?php

namespace App\Http\Resources\Features;

use Illuminate\Http\Resources\Json\JsonResource;

abstract class TranslatableJsonResource extends JsonResource
{
    /**
     * @param array $fields
     * @return array[]
     */
    public function toArrayTranslatable(array $fields)
    {
        $data = [];
        foreach ($fields as $field) {
            $data[$field] = json_decode($this->getRawOriginal($field), true);
        }
        return ['translations' => $data];
    }
}
