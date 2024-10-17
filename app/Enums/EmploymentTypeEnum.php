<?php
namespace App\Enums;

use App\Traits\EnumTrait;

enum EmploymentTypeEnum: string
{
    use EnumTrait;


    case FULL_TIME = 'full-time';
    case PART_TIME = 'part-time';
    case CONTRACT = 'contract';
    case TEMPORARY = 'temporary';
    case SEASONAL = 'seasonal';
    case REMOTE = 'remote';
    case INTERNSHIP = 'internship';
    case PROJECT_BASED = 'project-based';
    case VOLUNTEER = 'volunteer';


    public function title(string $lang = null): string
    {
        $value = match ($this) {
            self::FULL_TIME => 'Полная занятость',
            self::PART_TIME => 'Частичная занятость',
            self::CONTRACT => 'Контракт',
            self::TEMPORARY => 'Временная работа',
            self::SEASONAL => 'Сезонная работа',
            self::REMOTE => 'Удаленная работа',
            self::INTERNSHIP => 'Стажировка',
            self::PROJECT_BASED => 'Проектная занятость',
            self::VOLUNTEER => 'Волонтерская деятельность',
        };

        return __($value, locale: $lang);
    }

}
