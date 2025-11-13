<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SuggestionController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        $request->validate([
            'message' => 'required|string',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg',
        ]);

        $suggestion = Suggestion::with('photos')->create([
            'user_id' => $user->id,
            'message' => $request->message,
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('photos/suggestion', 'public');
                $suggestion->photos()->create(['path' => $path]);
            }
        }

        return response()->json($suggestion, 201);
    }

    public function index()
    {
        $user = Auth::user();

        $suggestions = Suggestion::with('photos')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($suggestions);
    }

    public function show($id)
    {
        $suggestion = Suggestion::with('photos')->find($id);

        if (!$suggestion) {
            return response()->json(['message' => 'Предложение не найдено'], 404);
        }

        return response()->json($suggestion);
    }

    public function remove($id)
    {
        $suggestion = Suggestion::with('photos')->find($id);

        if (!$suggestion) {
            return response()->json(['message' => 'Предложение не найдено'], 404);
        }

        foreach ($suggestion->photos as $photo) {
            $fullPath = 'photos/suggestion/' . $photo->path;

            if ($photo->path && Storage::disk('public')->exists($fullPath)) {
                Storage::disk('public')->delete($fullPath);
            }

            $photo->delete();
        }

        $suggestion->delete();

        return response()->json(['message' => 'Предложение удалено']);
    }
}
