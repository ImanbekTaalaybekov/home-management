<?php

namespace App\Services;

use App\Models\Notification;
use App\Notifications\PushNotification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;
use App\Models\User;

class NotificationService
{
    public function sendGlobalNotification($title, $message, array $photos = [], $document = null)
    {
        $notification = Notification::create([
            'title' => $title,
            'message' => $message,
            'type' => 'global',
            'document' => $document,
        ]);

        $this->attachPhotos($notification, $photos);

        $tokens = User::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
        $this->sendPushNotification($title, $message, $tokens);

    }

    public function sendComplexNotification($complexId, $title, $message, array $photos = [], $document = null)
    {
        $notification = Notification::create([
            'title' => $title,
            'message' => $message,
            'type' => 'complex',
            'residential_complex_id' => $complexId,
            'document' => $document,
        ]);

        $this->attachPhotos($notification, $photos);

        $tokens = User::where('residential_complex_id', $complexId)
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        $this->sendPushNotification($title, $message, $tokens);
    }

    public function sendPersonalNotification($userId, $title, $message, array $photos = [], $document = null)
    {
        $notification = Notification::create([
            'title' => $title,
            'message' => $message,
            'type' => 'personal',
            'user_id' => $userId,
            'document' => $document,
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

        $fcmMessage = FcmMessage::create()
            ->notification(
                FcmNotification::create()
                    ->title($title)
                    ->body($message)
            );

        $tokens = array_filter($tokens);
        if (empty($tokens)) return;

        foreach ($tokens as $token) {
            try {
                \Illuminate\Support\Facades\Notification::route('fcm', $token)
                    ->notify(new PushNotification($fcmMessage));
            } catch (\Exception $e) {
                Log::error("FCM push notification failed: " . $e->getMessage());
            }
        }
    }
}

