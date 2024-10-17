<?php

namespace App\Http\Controllers\Features;

use App\Enums\CurrencyEnum;
use App\Enums\DisabilityGroupEnum;
use App\Enums\DisabilityTypeEnum;
use App\Enums\EmploymentTypeEnum;
use App\Enums\ExperienceLevelEnum;
use App\Enums\FavoriteEnum;
use App\Enums\KnowledgeBaseObject;
use App\Enums\Language;
use App\Enums\PushNotificationStatus;
use App\Enums\ResumeStatusEnum;
use App\Enums\UserNotification;
use App\Enums\VacancyStatusEnum;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class EnumController extends Controller
{
   public function index()
   {
    $commonClasses = [
        CurrencyEnum::class,
        DisabilityGroupEnum::class,
        DisabilityTypeEnum::class,
        EmploymentTypeEnum::class,
        ExperienceLevelEnum::class,
        FavoriteEnum::class,
        KnowledgeBaseObject::class,
        Language::class,
        PushNotificationStatus::class,
    ];

    $resumeAssociatedClasses = [
        ResumeStatusEnum::class,
    ];

    $vacancyAssociatedClasses = [
        VacancyStatusEnum::class,
    ];

    $userAssociatedClasses = [
        UserNotification::class,
    ];


    $enums = [
        'common' => $this->getEnumGroup($commonClasses),
        'associated' => [
            'resume' => $this->getEnumGroup(($resumeAssociatedClasses)),
            'vacancy' => $this->getEnumGroup(($vacancyAssociatedClasses)),
            'user' => $this->getEnumGroup(($userAssociatedClasses)),
        ]
    ];

       return response()->json($enums);
   }

    protected function getEnumGroup(array $classes)
    {
        return collect($classes)->mapWithKeys(function ($class) {
            $key = strtolower(Str::snake(str_replace('Enum', '', class_basename($class))));
            $value = call_user_func([$class, 'getEnum']);
            return [$key => $value];
        });
    }
}
