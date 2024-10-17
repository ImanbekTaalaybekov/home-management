<?php
namespace App\Enums;

use App\Traits\EnumTrait;

enum VacancyStatusEnum: string
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
            self::REJECTED => 'отклоненнный',
            self::BLOCKED => 'заблокированно',
            self::PUBLISHED => 'опубликовано',
        };

        return __($value, locale: $lang);
    }
}
