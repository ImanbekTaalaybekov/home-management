<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\FcmUserToken;
use Illuminate\Http\UploadedFile;

class NotificationService
{
    public function sendGlobalNotification(int $clientId, string $title, string $message, array $photos = [], $document = null, string $category): void
    {
        $notification = Notification::create([
            'client_id' => $clientId,
            'title' => $title,
            'message' => $message,
            'type' => 'global',
            'document' => $document,
            'category' => $category,
        ]);

        $this->attachPhotos($notification, $photos);

        $userIds = User::whereHas('residentialComplex', function ($q) use ($clientId) {
            $q->where('client_id', $clientId);
        })
            ->pluck('id');

        $tokens = FcmUserToken::whereIn('user_id', $userIds)
            ->pluck('fcm_token')
            ->unique()
            ->values()
            ->all();

        $this->sendPushNotification($title, $message, $tokens);
    }

    public function sendComplexNotification(int    $clientId, int    $complexId, string $title, string $message, array  $photos = [],$document = null, string $category): void
    {
        $notification = Notification::create([
            'client_id' => $clientId,
            'title' => $title,
            'message' => $message,
            'type' => 'complex',
            'residential_complex_id' => $complexId,
            'document' => $document,
            'category' => $category,
        ]);

        $this->attachPhotos($notification, $photos);

        $userIds = User::where('residential_complex_id', $complexId)->pluck('id');

        $tokens = FcmUserToken::whereIn('user_id', $userIds)
            ->pluck('fcm_token')
            ->unique()
            ->values()
            ->all();

        $this->sendPushNotification($title, $message, $tokens);
    }

    public function sendPersonalNotification(int    $clientId, string $personalAccount, string $title, string $message, array  $photos = [], $document = null, string $category): ?Notification
    {
        $user = User::where('personal_account', $personalAccount)->first();

        if (!$user) {
            return null;
        }

        if ($user->residentialComplex && $user->residentialComplex->client_id !== $clientId) {
            return null;
        }

        $documentPath = null;
        if ($document instanceof UploadedFile) {
            $documentPath = $document->store('notifications', 'public');
        } elseif (is_string($document) && $document !== '') {
            $documentPath = $document;
        }

        $notification = Notification::create([
            'client_id' => $clientId,
            'title' => $title,
            'message' => $message,
            'type' => 'personal',
            'category' => $category,
            'user_id' => $user->id,
            'residential_complex_id' => null,
            'document' => $documentPath,
        ]);

        $this->attachPhotos($notification, $photos);

        $tokens = FcmUserToken::where('user_id', $user->id)
            ->pluck('fcm_token')
            ->unique()
            ->values()
            ->all();

        $this->sendPushNotification($title, $message, $tokens);

        return $notification;
    }

    private function attachPhotos(Notification $notification, array $photos): void
    {
        foreach ($photos as $path) {
            $notification->photos()->create(['path' => $path]);
        }
    }

    private function sendPushNotification(string $title, string $message, array $tokens): void
    {
        $tokens = array_values(array_unique(array_filter($tokens)));
        if (empty($tokens)) {
            return;
        }

        foreach (array_chunk($tokens, 500) as $chunk) {
            app(FcmV1Service::class)->send($chunk, $title, $message);
        }
    }
}