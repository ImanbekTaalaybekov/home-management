<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBase;
use App\Models\KnowledgeBaseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class KnowledgeBaseAdminController extends Controller
{
    public function storeCategory(Request $request)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (!$admin->client_id) {
            return response()->json(['message' => 'У админа не указан client_id'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = KnowledgeBaseCategory::create([
            'name'      => $request->name,
            'client_id' => $admin->client_id,
        ]);

        return response()->json($category, 201);
    }

    public function indexCategories()
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $categories = KnowledgeBaseCategory::where('client_id', $admin->client_id)
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    public function updateCategory(Request $request, $id)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = KnowledgeBaseCategory::where('id', $id)
            ->where('client_id', $admin->client_id)
            ->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->update([
            'name' => $request->name,
        ]);

        return response()->json($category);
    }

    public function destroyCategory($id)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $category = KnowledgeBaseCategory::where('id', $id)
            ->where('client_id', $admin->client_id)
            ->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }

    public function storeArticle(Request $request)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (!$admin->client_id) {
            return response()->json(['message' => 'У админа не указан client_id'], 403);
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'content'     => 'required',
            'category_id' => 'required|exists:knowledge_base_categories,id',
            'photos'      => 'nullable|array',
            'photos.*'    => 'image|mimes:jpeg,png,jpg,gif'
        ]);

        $category = KnowledgeBaseCategory::where('id', $request->category_id)
            ->where('client_id', $admin->client_id)
            ->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found or forbidden'], 404);
        }

        $article = KnowledgeBase::create([
            'title'       => $request->title,
            'content'     => $request->input('content'),
            'category_id' => $category->id,
            'client_id'   => $admin->client_id,
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('photos/knowledge-base', 'public');
                $article->photos()->create(['path' => $path]);
            }
        }

        return response()->json($article->load('category', 'photos'), 201);
    }

    public function indexArticles(Request $request)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'category_id' => 'required|exists:knowledge_base_categories,id',
        ]);

        $category = KnowledgeBaseCategory::where('id', $request->category_id)
            ->where('client_id', $admin->client_id)
            ->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found or forbidden'], 404);
        }

        $articles = KnowledgeBase::with(['category', 'photos'])
            ->where('category_id', $category->id)
            ->where('client_id', $admin->client_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($articles);
    }

    public function showArticle($id)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $article = KnowledgeBase::with(['category', 'photos'])
            ->where('id', $id)
            ->where('client_id', $admin->client_id)
            ->first();

        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        return response()->json($article);
    }

    public function updateArticle(Request $request, $id)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $article = KnowledgeBase::where('id', $id)
            ->where('client_id', $admin->client_id)
            ->first();

        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        $request->validate([
            'title'       => 'nullable|string|max:255',
            'content'     => 'nullable|string',
            'category_id' => 'nullable|exists:knowledge_base_categories,id',
            'photos'      => 'nullable|array',
            'photos.*'    => 'image|mimes:jpeg,png,jpg,gif'
        ]);

        $data = [];

        if ($request->filled('title')) {
            $data['title'] = $request->title;
        }
        if ($request->filled('content')) {
            $data['content'] = $request->input('content');
        }
        if ($request->filled('category_id')) {
            $category = KnowledgeBaseCategory::where('id', $request->category_id)
                ->where('client_id', $admin->client_id)
                ->first();

            if (!$category) {
                return response()->json(['message' => 'Category not found or forbidden'], 404);
            }

            $data['category_id'] = $category->id;
        }

        if ($request->hasFile('photos')) {
            foreach ($article->photos as $photo) {
                if ($photo->path) {
                    Storage::disk('public')->delete($photo->path);
                }
                $photo->delete();
            }

            foreach ($request->file('photos') as $photoFile) {
                $path = $photoFile->store('photos/knowledge-base', 'public');
                $article->photos()->create(['path' => $path]);
            }
        }

        if (!empty($data)) {
            $article->update($data);
        }

        return response()->json($article->load('category', 'photos'));
    }

    public function destroyArticle($id)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $article = KnowledgeBase::where('id', $id)
            ->where('client_id', $admin->client_id)
            ->first();

        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        foreach ($article->photos as $photo) {
            if ($photo->path) {
                Storage::disk('public')->delete($photo->path);
            }
            $photo->delete();
        }

        $article->delete();

        return response()->json(['message' => 'Article deleted successfully']);
    }
}