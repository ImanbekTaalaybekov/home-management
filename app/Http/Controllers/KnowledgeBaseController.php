<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBase;
use App\Models\KnowledgeBaseCategory;
use App\Models\ResidentialComplex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KnowledgeBaseController extends Controller
{
    public function indexCategories()
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $residentialComplex = ResidentialComplex::find($user->residential_complex_id);
        if (!$residentialComplex) {
            return response()->json(['message' => 'Residential complex not found'], 404);
        }

        $clientId = $residentialComplex->client_id;

        $categories = KnowledgeBaseCategory::where('client_id', $clientId)
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    public function indexArticles(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'category_id' => 'required|exists:knowledge_base_categories,id',
        ]);

        $residentialComplex = ResidentialComplex::find($user->residential_complex_id);
        if (!$residentialComplex) {
            return response()->json(['message' => 'Residential complex not found'], 404);
        }

        $clientId = $residentialComplex->client_id;

        $category = KnowledgeBaseCategory::where('id', $request->category_id)
            ->where('client_id', $clientId)
            ->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found or forbidden'], 404);
        }

        $articles = KnowledgeBase::with(['category', 'photos'])
            ->where('category_id', $category->id)
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($articles);
    }

    public function showArticle($id)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $residentialComplex = ResidentialComplex::find($user->residential_complex_id);
        if (!$residentialComplex) {
            return response()->json(['message' => 'Residential complex not found'], 404);
        }

        $clientId = $residentialComplex->client_id;
        $article = KnowledgeBase::with(['category', 'photos'])
            ->where('id', $id)
            ->where('client_id', $clientId)
            ->first();

        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        return response()->json($article);
    }
}