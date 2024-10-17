<?php

namespace App\Http\Controllers\Features;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\News;
use App\Models\Resume;
use App\Models\Vacancy;

class StatisticController extends Controller
{
    public function getStatisticByCity($cityId)
    {
        $builidngCount = Building::published()->where('city_id', $cityId)->count();
        $resumeCount = Resume::publishedWithStatus()->where('city_id', $cityId)->count();
        $vacancyCount = Vacancy::publishedWithStatus()->where('city_id', $cityId)->count();

        return response()->json([
            'building' => $builidngCount,
            'resume' => $resumeCount,
            'vacancy' => $vacancyCount
        ]);
    }
}
