<?php
namespace App\Enums;

use App\Traits\EnumTrait;

enum ExperienceLevelEnum: string
{
    use EnumTrait;


    case WITHOUT_EXPERIENCE = 'without_experience';
    case SMALL = 'small';
    case MEDIUM = 'medium';
    case LARGE = 'large';

    public function title(string $lang = null): string
    {
        $value = match ($this) {
            self::WITHOUT_EXPERIENCE => 'без опыта',
            self::SMALL => '1-3 года',
            self::MEDIUM => '3-6 лет',
            self::LARGE => '6 и более лет',
        };

        return __($value, locale: $lang);
    }
}
