<?php
namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public function sendGlobalNotification($title, $message)
    {
        Notification::create([
            'title' => $title,
            'message' => $message,
            'type' => 'global',
        ]);
    }

    public function sendComplexNotification($complexId, $title, $message)
    {
        Notification::create([
            'title' => $title,
            'message' => $message,
            'type' => 'complex',
            'residential_complex_id' => $complexId,
        ]);
    }

    public function sendPersonalNotification($userId, $title, $message)
    {
        Notification::create([
            'title' => $title,
            'message' => $message,
            'type' => 'personal',
            'user_id' => $userId,
        ]);
    }
}
