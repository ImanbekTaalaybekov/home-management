<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index()
    {
        $user = Auth::guard('sanctum')->user();

        $announcements = Announcement::with('photos')
            ->where('residential_complex_id', $user->residential_complex_id)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

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
            'residential_complex_id' => $user->residential_complex_id,
            'created_by' => $user->id
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

        $announcement = Announcement::with(['photos', 'createdBy'])
            ->where('id', $id)
            ->where('residential_complex_id', $user->residential_complex_id)
            ->firstOrFail();

        return response()->json([
            'announcement' => $announcement,
            'deletable' => $announcement->created_by === $user->id
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::guard('sanctum')->user();

        $announcement = Announcement::where('id', $id)
            ->where('residential_complex_id', $user->residential_complex_id)
            ->firstOrFail();

        $announcement->photos()->delete();
        $announcement->delete();

        return response()->json(['message' => 'Объявление успешно удалено.']);
    }

    public function showOwn()
    {
        $user = Auth::guard('sanctum')->user();

        $announcements = Announcement::with('photos')
            ->where('residential_complex_id', $user->residential_complex_id)
            ->where('created_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($announcements);
    }
}