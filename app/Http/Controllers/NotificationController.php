<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
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

        return response()->json($notification);
    }

    public function store(Request $request, NotificationService $notificationService)
    {
        $request->validate([
            'type' => 'required|in:global,complex,personal',
            'title' => 'required|string',
            'message' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
            'residential_complex_id' => 'nullable|exists:residential_complexes,id',
            'photos' => 'nullable|array',
            'photos.*' => 'nullable',
        ]);

        $photos = $request->photos ?? [];

        switch ($request->type) {
            case 'global':
                $notificationService->sendGlobalNotification($request->title, $request->message, $photos);
                break;
            case 'complex':
                $notificationService->sendComplexNotification($request->residential_complex_id, $request->title, $request->message, $photos);
                break;
            case 'personal':
                $notificationService->sendPersonalNotification($request->user_id, $request->title, $request->message, $photos);
                break;
        }

        return response()->json(['message' => 'Уведомление отправлено'], 201);
    }
}
