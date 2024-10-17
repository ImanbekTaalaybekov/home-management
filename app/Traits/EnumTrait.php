<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait EnumTrait
{
    public static function getEnum() :array
    {
        $enumClassName = class_basename(static::class);
        $enumKey = strtolower(Str::snake(str_replace('Enum', '', $enumClassName)));

        return [
                "about" => trans($enumKey, [], 'ru'),
                "key" => $enumKey,
                "values" => collect(self::cases())
                    ->mapWithKeys(function ($case) {
                        return [
                            $case->value => ['ru' => $case->title('ru'), 'kg' => $case->title('kg')]
                        ];
                    })
                    ->toArray(),
        ];
    }
}
