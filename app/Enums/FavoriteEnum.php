<?php
namespace App\Enums;

use App\Models\Building;
use App\Models\Resume;
use App\Models\Vacancy;
use App\Traits\EnumTrait;

enum FavoriteEnum: string
{
    use EnumTrait;


    case RESUME = 'resume';
    case VACANCY = 'vacancy';
    case BUILDING = 'building';

    public function modelClass(): string
    {
        return match ($this) {
            self::RESUME => Resume::class,
            self::VACANCY => Vacancy::class,
            self::BUILDING => Building::class,
        };
    }

    public function title(string $lang = null) :string
    {
        $value = match ($this) {
            self::RESUME => 'резюме',
            self::VACANCY => 'вакансия',
            self::BUILDING => 'заведение',
        };

        return __($value, locale: $lang);
    }
}
