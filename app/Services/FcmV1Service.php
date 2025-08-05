<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Throwable;

class FcmV1Service
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(config('services.firebase.credentials'));
        $this->messaging = $factory->createMessaging();
    }

    /**
     * @param array|string $tokens
     * @param string $title
     * @param string $body
     * @param array $data
     */
    public function send($tokens, $title, $body, array $data = [])
    {
        Log::info('FcmV1Service send called', [
            'tokens' => $tokens,
            'title' => $title,
            'body' => $body
        ]);

        if (is_string($tokens)) {
            $tokens = [$tokens];
        }

        $notification = FirebaseNotification::create($title, $body);
        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withData($data);

        foreach ($tokens as $token) {
            try {
                $this->messaging->send($message->withChangedTarget('token', $token));
            } catch (Throwable $e) {
                Log::error("FCM V1 push send error: ".$e->getMessage());
            }
        }
    }
}
