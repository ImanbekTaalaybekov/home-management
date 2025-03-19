<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
