<?php

namespace App\Http\Controllers\Features;

use App\Http\Controllers\Controller;
use App\Http\Resources\Features\NewsResource;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->query('limit', 20);
        $page = $request->query('page', 1);
        $search = $request->query('search');

        $request->validate([
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1',
            'search' => 'nullable|string',
        ]);

        if ($search !== null) {
            $newsQuery = News::search($search)->paginate($limit, page: $page);
        } else {
            $newsQuery = News::published()->with('category')->sortByDate()->paginate($limit, ['*'], 'page', $page);
        }
        return NewsResource::collection($newsQuery);
    }

    public function show($id)
    {
        $news = News::findOrFail($id);
        return new NewsResource($news);
    }
}
