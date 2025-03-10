<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PushNotification extends Notification
{
    public $fcmMessage;

    public function __construct($fcmMessage)
    {
        $this->fcmMessage = $fcmMessage;
    }

    public function toFcm($notifiable)
    {
        return $this->fcmMessage;
    }
}

