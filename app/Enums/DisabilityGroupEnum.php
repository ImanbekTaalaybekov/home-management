<?php
namespace App\Enums;

use App\Traits\EnumTrait;

enum DisabilityGroupEnum: int
{
    use EnumTrait;


    case FIRST = 1;
    case SECOND = 2;
    case THIRD = 3;


    public function title(string $lang = null): string
    {
        $value = match ($this) {
            self::FIRST => 'I',
            self::SECOND => 'II',
            self::THIRD => 'III',
        };

        return __($value, locale: $lang);
    }

}
