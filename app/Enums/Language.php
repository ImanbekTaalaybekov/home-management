<?php
namespace App\Enums;

use App\Traits\EnumTrait;

enum Language: string
{
    use EnumTrait;


    case RUS = 'ru';
    case KZ = 'kz';

    public function title(string $lang = null): string
    {
        $value = match ($this) {
            self::RUS => 'Русский',
            self::KZ => 'Казахский',
        };

        return __($value, locale: $lang);
    }

    public function icon(): string
    {
        return match ($this) {
            self::RUS => '🇷🇺',
            self::KZ => '🇰🇿',
        };
    }

    public static function default(): self
    {
        return self::RUS;
    }

}
