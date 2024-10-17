<?php

namespace App\Http\Controllers\Features;

use App\Http\Controllers\Controller;
use App\Http\Resources\Features\BuildingCategoryResource;
use App\Models\BuildingCategory;

class BuildingCategoryController extends Controller
{
    public function index()
    {
        $categories = BuildingCategory::sortByPriority()->get();
        return BuildingCategoryResource::collection($categories);
    }
}
