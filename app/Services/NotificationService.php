<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\FcmUserToken;
use Illuminate\Http\UploadedFile;

class NotificationService
{
    public function sendGlobalNotification($title, $message, array $photos = [], $document = null, $category)
    {
        $notification = Notification::create([
            'title'    => $title,
            'message'  => $message,
            'type'     => 'global',
            'document' => $document,
            'category' => $category,
        ]);

        $this->attachPhotos($notification, $photos);

        $tokens = FcmUserToken::query()
            ->pluck('fcm_token')
            ->unique()
            ->values()
            ->all();

        $this->sendPushNotification($title, $message, $tokens);
    }

    public function sendComplexNotification($complexId, $title, $message, array $photos = [], $document = null, $category)
    {
        $notification = Notification::create([
            'title'                   => $title,
            'message'                 => $message,
            'type'                    => 'complex',
            'residential_complex_id'  => $complexId,
            'document'                => $document,
            'category'                => $category,
        ]);

        $this->attachPhotos($notification, $photos);

        $tokens = FcmUserToken::query()
            ->join('users', 'users.id', '=', 'fcm_user_tokens.user_id')
            ->where('users.residential_complex_id', $complexId)
            ->pluck('fcm_user_tokens.fcm_token')
            ->unique()
            ->values()
            ->all();

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
            'title'                  => $title,
            'message'                => $message,
            'type'                   => 'personal',
            'category'               => $category,
            'user_id'                => $user->id,
            'residential_complex_id' => null,
            'document'               => $documentPath,
        ]);

        $this->attachPhotos($notification, $photos);

        $tokens = FcmUserToken::where('user_id', $user->id)
            ->pluck('fcm_token')
            ->unique()
            ->values()
            ->all();

        if (!empty($tokens)) {
            $this->sendPushNotification($title, $message, $tokens);
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

        foreach (array_chunk($tokens, 500) as $chunk) {
            app(FcmV1Service::class)->send($chunk, $title, $message);
        }
    }
}