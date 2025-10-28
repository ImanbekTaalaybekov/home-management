<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
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
     * @param string $androidChannelId
     */
    public function send($tokens, string $title, string $body, array $data = [], string $androidChannelId = 'default_sound_channel')
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

        $androidConfig = AndroidConfig::fromArray([
            'priority' => 'high',
            'notification' => [
                'channel_id' => $androidChannelId,
                'sound'      => 'default',
            ],
        ]);

        $apnsConfig = ApnsConfig::fromArray([
            'headers' => [
                'apns-push-type' => 'alert',
                'apns-priority'  => '10',
            ],
            'payload' => [
                'aps' => [
                    'sound' => 'default',
                ],
            ],
        ]);

        $baseMessage = CloudMessage::new()
            ->withNotification($notification)
            ->withAndroidConfig($androidConfig)
            ->withApnsConfig($apnsConfig)
            ->withData($data);

        foreach ($tokens as $token) {
            try {
                $this->messaging->send($baseMessage->withChangedTarget('token', $token));
            } catch (Throwable $e) {
                Log::error("FCM V1 push send error: ".$e->getMessage());
            }
        }
    }
}