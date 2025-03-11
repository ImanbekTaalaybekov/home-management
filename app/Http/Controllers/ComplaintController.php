<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function index()
    {
        $user = Auth::user();

        $complaints = Complaint::with('photos')
            ->where('user_id', $user->id)
            ->get();

        return response()->json($complaints);
    }

    public function show($id)
    {
        $complaint = Complaint::with('photos')
            ->where('id', $id);

        if (!$complaint) {
            return response()->json(['message' => 'Жалоба не найдена'], 404);
        }

        return response()->json($complaint);
    }
}
