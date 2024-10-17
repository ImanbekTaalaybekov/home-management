<?php

namespace App\Http\Controllers\Features;

use App\Http\Controllers\Controller;
use App\Http\Resources\Features\CityResource;
use App\Models\City;

class CityController extends Controller
{
    public function index()
    {
        $cities = City::sortByPriority()->published()->get();
        return CityResource::collection($cities);
    }
}
