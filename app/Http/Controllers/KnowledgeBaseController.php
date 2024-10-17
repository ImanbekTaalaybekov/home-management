<?php

namespace App\Http\Controllers\Features;

use App\Enums\KnowledgeBaseObject;
use App\Http\Controllers\Controller;
use App\Http\Resources\Features\KnowledgeCategoryResource;
use App\Http\Resources\Features\KnowledgeSearchResource;
use App\Models\KnowledgeCategory;
use Illuminate\Http\Request;


class KnowledgeBaseController extends Controller
{
    public function knowledgeCategory(Request $request)
    {
        $search = $request->query('search');
        if ($search) {
            $categories = KnowledgeCategory::search($search)->get();
            return KnowledgeSearchResource::collection($categories);
        }
        $categories = KnowledgeCategory::defaultOrder()->get()->toTree();
        return KnowledgeCategoryResource::collection($categories);
    }

    public function knowledgeObject(int $id)
    {
        $category = KnowledgeCategory::with('bindable')->findOrFail($id);
        $bindable = $category->bindable;

        if ($bindable) {
            $bindTypeValue = KnowledgeBaseObject::fromClassName($category->bind_type)->value;

            $data = [
                'object_type' => $bindTypeValue,
                'object' => $bindable->toResource()
            ];
            return response()->json(['data' => $data]);
        }
        return response()->json(['error' => 'Bindable type error'], 404);
    }
}
