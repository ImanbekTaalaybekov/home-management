<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $complaint = Complaint::create([
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('photos');
                $complaint->photos()->create(['path' => $path]);
            }
        }

        return response()->json($complaint, 201);
    }
}
