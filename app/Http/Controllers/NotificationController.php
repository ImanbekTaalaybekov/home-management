<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\NotificationStatus;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::guard('sanctum')->user();

        $readIds = NotificationStatus::where('user_id', $user->id)
            ->pluck('notification_id')
            ->toArray();

        $notifications = Notification::with('photos')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('residential_complex_id', $user->residential_complex_id)
                    ->orWhere('type', 'global');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $notifications->getCollection()->transform(function ($notification) use ($readIds) {
            $notification->been_read = in_array($notification->id, $readIds);
            return $notification;
        });

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
                $notificationService->sendGlobalNotification($request->title, $request->message, $photos, $documentPath, $request->category);
                break;
            case 'complex':
                $notificationService->sendComplexNotification($request->residential_complex_id, $request->title, $request->message, $photos, $documentPath, $request->category);
                break;
            case 'personal':
                $notificationService->sendPersonalNotification($request->user_id, $request->title, $request->message, $photos, $documentPath, $request->category);
                break;
        }

        return response()->json(['message' => 'Уведомление отправлено'], 201);
    }

    public function status(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        $request->validate([
            'notification_id' => 'required',
        ]);

        $complaint = NotificationStatus::create([
            'user_id' => $user->id,
            'notification_id' => $request->notification_id,
        ]);

        return response()->json($complaint, 201);
    }
}
