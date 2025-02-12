<?php

namespace App\Http\Controllers;

use App\Enums\FavoriteEnum;
use App\Enums\KnowledgeBaseObject;
use App\Enums\Language;
use App\Enums\PushNotificationStatus;
use App\Enums\UserNotification;
use Illuminate\Support\Str;

class EnumController extends Controller
{
   public function index()
   {
    $commonClasses = [
        FavoriteEnum::class,
        KnowledgeBaseObject::class,
        Language::class,
        PushNotificationStatus::class,
    ];

    $userAssociatedClasses = [
        UserNotification::class,
    ];


    $enums = [
        'common' => $this->getEnumGroup($commonClasses),
        'associated' => [
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
