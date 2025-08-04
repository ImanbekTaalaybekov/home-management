<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::guard('sanctum')->user();
        $notifications = Notification::with('photos')
        ->where('user_id', $user->id)
            ->orWhere('residential_complex_id', $user->residential_complex_id)
            ->orWhere('type', 'global')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($notifications);
    }

    public function show($id)
    {
        $notification = Notification::with('photos')->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Уведомление не найдено'], 404);
        }

        return new NotificationResource($notification);
    }

    public function store(Request $request, NotificationService $notificationService)
    {
        $request->validate([
            'type' => 'required|in:global,complex,personal',
            'category' => 'required|in:technical,common',
            'title' => 'required|string',
            'message' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
            'residential_complex_id' => 'nullable|exists:residential_complexes,id',
            'photos' => 'nullable|array',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg',
            'document' => 'nullable|file|mimes:pdf',
        ]);

        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photos[] = $photo->store('photos/notification', 'public');
            }
        }

        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('documents/notification', 'public');
        }

        switch ($request->type) {
            case 'global':
                $notificationService->sendGlobalNotification($request->title, $request->message, $photos, $documentPath);
                break;
            case 'complex':
                $notificationService->sendComplexNotification($request->residential_complex_id, $request->title, $request->message, $photos, $documentPath);
                break;
            case 'personal':
                $notificationService->sendPersonalNotification($request->user_id, $request->title, $request->message, $photos, $documentPath);
                break;
        }

        return response()->json(['message' => 'Уведомление отправлено'], 201);
    }
}
