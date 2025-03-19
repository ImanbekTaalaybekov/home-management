<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index()
    {
        $user = Auth::guard('sanctum')->user();

        $announcements = Announcement::with('photos')
            ->where('residential_complex_id', $user->residential_complex_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($announcements);
    }

    public function store(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg',
        ]);

        $announcement = Announcement::create([
            'title' => $request->title,
            'content' => $request->message,
            'residential_complex_id' => $user->residential_complex_id
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('photos/announcement', 'public');

                $announcement->photos()->create([
                    'path' => $path,
                ]);
            }
        }

        return response()->json($announcement->load('photos'), 201);
    }

    public function show($id)
    {
        $user = Auth::guard('sanctum')->user();

        $announcement = Announcement::with('photos')
            ->where('id', $id)
            ->where('residential_complex_id', $user->residential_complex_id)
            ->firstOrFail();

        return response()->json($announcement);
    }
}