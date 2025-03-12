<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $announcements = Announcement::where('residential_complex_id', $user->residential_complex_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($announcements);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'residential_complex_id' => 'required|exists:residential_complexes,id'
        ]);

        $announcement = Announcement::create($request->all());

        return response()->json($announcement, 201);
    }

    public function show($id, Request $request)
    {
        $user = $request->user();

        $announcement = Announcement::where('id', $id)
            ->where('residential_complex_id', $user->residential_complex_id)
            ->firstOrFail();

        return response()->json($announcement);
    }
}