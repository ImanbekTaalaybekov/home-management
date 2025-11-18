<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\NotificationStatus;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'type' => 'required|in:global,complex,personal',
            'category' => 'required|in:technical,common',
            'title' => 'required|string',
            'message' => 'required|string',
            'personal_account' => 'nullable',
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

        $clientId = $admin->client_id;

        switch ($request->type) {
            case 'global':
                $notificationService->sendGlobalNotification(
                    $clientId,
                    $request->title,
                    $request->message,
                    $photos,
                    $documentPath,
                    $request->category
                );
                break;

            case 'complex':
                $notificationService->sendComplexNotification(
                    $clientId,
                    $request->residential_complex_id,
                    $request->title,
                    $request->message,
                    $photos,
                    $documentPath,
                    $request->category
                );
                break;

            case 'personal':
                $notificationService->sendPersonalNotification(
                    $clientId,
                    $request->personal_account,
                    $request->title,
                    $request->message,
                    $photos,
                    $documentPath,
                    $request->category
                );
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


    public function statusIcon()
    {
        $user = Auth::guard('sanctum')->user();

        $unreadCount = Notification::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhere('residential_complex_id', $user->residential_complex_id)
                ->orWhere('type', 'global');
        })
            ->whereNotIn('id', DB::table('notification_statuses')
                ->select('notification_id')
                ->where('user_id', $user->id))
            ->count();

        return response()->json([
            'unread_count' => $unreadCount,
        ]);
    }

    public function indexAdmin(Request $request)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'type'     => 'nullable|in:global,complex,personal',
            'category' => 'nullable|in:technical,common',
        ]);

        $query = Notification::query()
            ->where('client_id', $admin->client_id);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($search = trim((string)$request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhere('personal_account', 'like', "%{$search}%");
            });
        }

        return response()->json(
            $query->orderBy('created_at', 'desc')->paginate(20)
        );
    }


    public function update(Request $request, $id)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $notification = Notification::where('id', $id)
            ->where('client_id', $admin->client_id)
            ->first();

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $request->validate([
            'type' => 'nullable|in:global,complex,personal',
            'category' => 'nullable|in:technical,common',
            'title' => 'nullable|string',
            'message' => 'nullable|string',
            'personal_account' => 'nullable',
            'residential_complex_id' => 'nullable|exists:residential_complexes,id',
            'photos' => 'nullable|array',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg',
            'document' => 'nullable|file|mimes:pdf',
        ]);

        $data = $request->only([
            'type',
            'category',
            'title',
            'message',
            'personal_account',
            'residential_complex_id',
        ]);

        if ($request->hasFile('photos')) {
            if (is_array($notification->photos)) {
                foreach ($notification->photos as $oldPhoto) {
                    Storage::disk('public')->delete($oldPhoto);
                }
            }

            $newPhotos = [];
            foreach ($request->file('photos') as $photo) {
                $newPhotos[] = $photo->store('photos/notification', 'public');
            }
            $data['photos'] = $newPhotos;
        }

        if ($request->hasFile('document')) {
            if ($notification->document) {
                Storage::disk('public')->delete($notification->document);
            }
            $data['document'] = $request->file('document')->store('documents/notification', 'public');
        }

        $notification->update($data);

        return response()->json([
            'message' => 'Notification updated successfully',
            'data'    => $notification,
        ]);
    }

    public function destroy($id)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $notification = Notification::where('id', $id)
            ->where('client_id', $admin->client_id)
            ->first();

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        if (is_array($notification->photos)) {
            foreach ($notification->photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }

        if ($notification->document) {
            Storage::disk('public')->delete($notification->document);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification deleted successfully']);
    }
}
