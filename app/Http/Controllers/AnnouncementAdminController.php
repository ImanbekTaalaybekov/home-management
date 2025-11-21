<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AnnouncementAdminController extends Controller
{
    public function index(Request $request)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $query = Announcement::with(['photos', 'createdBy', 'residentialComplex'])
            ->where(function ($q) use ($admin) {
                $q->whereNull('residential_complex_id')
                    ->orWhereHas('residentialComplex', function ($qq) use ($admin) {
                        $qq->where('client_id', $admin->client_id);
                    });
            });

        if ($request->filled('residential_complex_id')) {
            $query->where('residential_complex_id', $request->residential_complex_id);
        }

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $announcements = $query
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($announcements);
    }

    public function remove($id)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $announcement = Announcement::with('photos', 'residentialComplex')
            ->where('id', $id)
            ->where(function ($q) use ($admin) {
                $q->whereNull('residential_complex_id')
                    ->orWhereHas('residentialComplex', function ($qq) use ($admin) {
                        $qq->where('client_id', $admin->client_id);
                    });
            })
            ->first();

        if (!$announcement) {
            return response()->json(['message' => 'Объявление не найдено'], 404);
        }

        foreach ($announcement->photos as $photo) {
            if ($photo->path && Storage::disk('public')->exists($photo->path)) {
                Storage::disk('public')->delete($photo->path);
            }
            $photo->delete();
        }

        $announcement->delete();

        return response()->json(['message' => 'Объявление и фото удалены']);
    }
}