<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\UploadedFile;

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

    public function sendPersonalNotification($personalAccount, $title, $message, array $photos = [], $document = null, $category)
    {
        $user = User::where('personal_account', $personalAccount)->first();

        if (!$user) {
            return null;
        }

        $documentPath = null;
        if ($document instanceof UploadedFile) {
            $documentPath = $document->store('notifications', 'public');
        } elseif (is_string($document) && $document !== '') {
            $documentPath = $document;
        }

        $notification = Notification::create([
            'title' => $title,
            'message' => $message,
            'type' => 'personal',
            'category' => $category,
            'user_id' => $user->id,
            'residential_complex_id' => null,
            'document' => $documentPath,
        ]);

        $this->attachPhotos($notification, $photos);

        if ($user->fcm_token) {
            $this->sendPushNotification($title, $message, [$user->fcm_token]);
        }

        return $notification;
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
