<?php
namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

Class RandomImageHelper
{
    public static function getRandomImagePath($directory)
    {
        $allImages = glob($directory . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
        return Arr::random($allImages);
    }

    public static function attachRandomImage(Model $model, string $collection, string $fromStoragePath, int $count)
    {
        for ($i = 0; $i < $count; $i++) {
            $imagePath = self::getRandomImagePath(storage_path($fromStoragePath));
            $model->copyMedia($imagePath)->toMediaCollection($collection);
        }
    }

}
