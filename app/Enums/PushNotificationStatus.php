<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum PushNotificationStatus: string
{
    use EnumTrait;


    case IDLE = 'idle';
    case COMPLETE = 'complete';
    case ERROR = 'error';
    case PROCESSING = 'processing';

    public static function default(): self
    {
        return self::IDLE;
    }

    public function title(string $lang = null): string
    {
        $value = match ($this) {
            self::IDLE => 'бездействует',
            self::COMPLETE => 'завершен',
            self::ERROR => 'ошибка',
            self::PROCESSING => 'в обработке',
        };

        return __($value, locale: $lang);
    }
}
