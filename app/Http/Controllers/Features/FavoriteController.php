<?php

namespace App\Http\Controllers\Features;

use App\Enums\FavoriteEnum;
use App\Http\Controllers\Controller;
use App\Services\FavoriteService;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{

    public function __construct(protected FavoriteService $service)
    {
    }

    public function addToFavorites(Request $request, FavoriteEnum $type, $id)
    {
        $modelClass = $type->modelClass();
        $model = $modelClass::findOrFail($id);

        if ($this->service->hasExists($model, $request->user())) {
            return response()->json(['message' => 'Already added to favorites']);
        }

        $this->service->addFavorite($model, $request->user());
        return response()->json(['message' => 'Added to favorites']);
    }

    public function removeFromFavorites(Request $request, FavoriteEnum $type, $id)
    {
        $modelClass = $type->modelClass();
        $model = $modelClass::findOrFail($id);

        if (!$this->service->hasExists($model, $request->user())) {
            return response()->json(['message' => 'Not in favorites']);
        }

        $this->service->removeFavorite($model, $request->user());
        return response()->json(['message' => 'Removed from favorites']);
    }
}
