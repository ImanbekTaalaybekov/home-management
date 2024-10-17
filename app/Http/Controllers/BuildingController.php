<?php

namespace App\Http\Controllers\Features;

use App\Http\Controllers\Controller;
use App\Http\Filters\BuildingFilter;
use App\Http\Requests\BuildingFilterRequest;
use App\Http\Resources\Features\BuildingResource;
use App\Models\Building;

class BuildingController extends Controller
{
    public function index(BuildingFilterRequest $request)
    {
        $data = $request->filteredData();

        $limit = request()->query('limit', 20);
        $page = request()->query('page', 1);
        $search = data_get($request->validated(), 'search', null);

        $filter = app()->make( BuildingFilter::class, ['queryParams' => array_filter($data)]);

        if($search !== null){
            $searchedBuildingIds = Building::search($search)->get()->pluck('id')->toArray();
            $buildings = Building::filter($filter)->published()->with('city', 'category')->whereIn('id', $searchedBuildingIds);
            $buildings->orderByPosition($searchedBuildingIds);
        } else {
            $buildings = Building::filter($filter)->published()->with('city', 'category');
        }

        return BuildingResource::collection($buildings->paginate($limit, ['*'], 'page', $page));
    }

    public function show($id)
    {
        $buildings = Building::with('city', 'category')->findOrFail($id);
        return new BuildingResource($buildings);
    }
}
