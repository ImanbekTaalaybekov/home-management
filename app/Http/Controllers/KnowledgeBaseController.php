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
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif'
        ]);

        $article = KnowledgeBase::create($request->only(['title', 'content', 'category_id']));

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('photos/knowledge-base', 'public');
                $article->photos()->create(['path' => $path]);
            }
        }

        return response()->json($article->load('category'), 201);
    }

    public function indexArticles(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:knowledge_base_categories,id',
        ]);

        $categoryId = $request->category_id;

        $articles = KnowledgeBase::with(['category', 'photos'])
            ->where('category_id', $categoryId)
            ->get();

        return response()->json($articles);
    }

    public function showArticle($id)
    {
        $article = KnowledgeBase::with(['category', 'photos'])->findOrFail($id);

        return response()->json($article);
    }
}
