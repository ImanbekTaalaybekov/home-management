<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBase;
use App\Models\KnowledgeBaseCategory;
use Illuminate\Http\Request;

class KnowledgeBaseController extends Controller
{
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = KnowledgeBaseCategory::create($request->all());

        return response()->json($category, 201);
    }

    public function indexCategories()
    {
        return response()->json(KnowledgeBaseCategory::all());
    }

    public function storeArticle(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:knowledge_base_categories,id',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $article = KnowledgeBase::create($request->only(['title', 'content', 'category_id']));

        if ($request->has('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('knowledge_base_photos', 'public');
                $article->photos()->create(['path' => $path]);
            }
        }

        return response()->json($article->load('category'), 201);
    }

    public function indexArticles()
    {
        return response()->json(KnowledgeBase::with(['category', 'photos'])->get());
    }

    public function showArticle($id)
    {
        $article = KnowledgeBase::with(['category', 'photos'])->findOrFail($id);

        return response()->json($article);
    }
}
