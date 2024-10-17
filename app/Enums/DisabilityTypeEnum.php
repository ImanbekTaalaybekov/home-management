<?php
namespace App\Enums;

use App\Traits\EnumTrait;

enum DisabilityTypeEnum: string
{
    use EnumTrait;


    case WHEELCHAIR_USER = 'wheelchair_user';
    case MUSCULOSKELETAL_SYSTEM = 'musculoskeletal_system';
    case VISUALLY_IMPAIRED= 'visually_impaired';
    case HEARING_IMPAIRED = 'hearing_impaired';
    case TOTALLY_BLIND = 'totally_blind';
    case TOTALLY_DEAF = 'totally_deaf';
    case MENTAL_DISABILITY = 'mental_disability';
    case SPEECH_DISORDER = 'speech_disorder';
    case INVISIBLE_DISABILITY = 'invisible_disability';
    case ANY_FORM = 'any_form';

    public function title(string $lang = null): string
    {
        $value = match ($this) {
            self::WHEELCHAIR_USER => 'Пользователь инвалидной коляски',
            self::MUSCULOSKELETAL_SYSTEM => 'Опорно-двигательный аппарат',
            self::VISUALLY_IMPAIRED => 'Слабовидящий',
            self::HEARING_IMPAIRED => 'Слабослышащий',
            self::TOTALLY_BLIND => 'Тотально незрячий',
            self::TOTALLY_DEAF => 'Тотально неслышащий',
            self::MENTAL_DISABILITY => 'Ментальная инвалидность',
            self::SPEECH_DISORDER => 'Речевое нарушение',
            self::INVISIBLE_DISABILITY => 'Невидимая инвалидность (болезни органов)',
            self::ANY_FORM => 'Любая форма инвалидности',
            default => 'N/A',
        };

        return __($value, locale: $lang);
    }
}
