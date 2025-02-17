<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $announcements = Announcement::where(function ($query) use ($user) {
            $query->where('residential_complex_id', $user->residential_complex_id)
                ->where('type', 'complex');
        })
            ->orWhere(function ($query) use ($user) {
                $query->where('building_number', $user->building_number)
                    ->where('type', 'building');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($announcements);
    }
}