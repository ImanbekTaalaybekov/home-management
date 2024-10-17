<?php
namespace App\Enums;

use App\Traits\EnumTrait;

enum ResumeStatusEnum: string
{
    use EnumTrait;


    case MODERATION = 'moderation';
    case REJECTED = 'rejected';
    case BLOCKED = 'blocked';
    case PUBLISHED = 'published';


    public function title(string $lang = null): string
    {
        $value = match ($this) {
            self::MODERATION => 'модерация',
            self::REJECTED => __('отклоненнный'),
            self::BLOCKED => __('заблокированно'),
            self::PUBLISHED => __('опубликовано'),
        };

        return __($value, locale: $lang);
    }
}
