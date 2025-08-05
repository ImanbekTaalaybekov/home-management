<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function sendGlobalNotification($title, $message, array $photos = [], $document = null, $category)
    {
        $notification = Notification::create([
            'title' => $title,
            'message' => $message,
            'type' => 'global',
            'document' => $document,
            'category' => $category,
        ]);

        $this->attachPhotos($notification, $photos);

        $tokens = User::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
        $this->sendPushNotification($title, $message, $tokens);
    }

    public function sendComplexNotification($complexId, $title, $message, array $photos = [], $document = null, $category)
    {
        $notification = Notification::create([
            'title' => $title,
            'message' => $message,
            'type' => 'complex',
            'residential_complex_id' => $complexId,
            'document' => $document,
            'category' => $category,
        ]);

        $this->attachPhotos($notification, $photos);

        $tokens = User::where('residential_complex_id', $complexId)
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        $this->sendPushNotification($title, $message, $tokens);
    }

    public function sendPersonalNotification($userId, $title, $message, array $photos = [], $document = null, $category)
    {
        $notification = Notification::create([
            'title' => $title,
            'message' => $message,
            'type' => 'personal',
            'user_id' => $userId,
            'document' => $document,
            'category' => $category,
        ]);

        $this->attachPhotos($notification, $photos);

        $user = User::find($userId);
        if (!$user || !$user->fcm_token) return;

        $this->sendPushNotification($title, $message, [$user->fcm_token]);
    }

    private function attachPhotos(Notification $notification, array $photos)
    {
        foreach ($photos as $path) {
            $notification->photos()->create(['path' => $path]);
        }
    }

    private function sendPushNotification($title, $message, array $tokens)
    {
        if (empty($tokens)) return;
        app(FcmV1Service::class)->send($tokens, $title, $message);
    }
}
