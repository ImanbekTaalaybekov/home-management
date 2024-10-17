<?php
namespace App\Enums;

use App\Traits\EnumTrait;

enum UserNotification: string
{
    use EnumTrait;


    case NEWSLETTER = 'newsletter';
    case RESUME = 'resume';

    public function title(string $lang = null): string
    {
        $value = match ($this) {
            self::NEWSLETTER => 'Новостная рассылка',
            self::RESUME => 'Резюме',
        };

        return __($value, locale: $lang);
    }
}
