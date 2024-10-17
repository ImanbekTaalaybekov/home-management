<?php

// app/Console/Commands/StartPushNotificationCommand.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PushNotification;
use App\Jobs\ProcessPushNotification;

class StartPushNotificationCommand extends Command
{
    protected $signature = 'push:send';
    protected $description = 'Send push notifications';

    public function handle()
    {
        $notifications = PushNotification::where(function ($query) {
            $query->where('status', 'idle')
                ->where(function ($query) {
                    $query->whereNull('start_at')
                        ->orWhere('start_at', '<=', now());
                });
        })->get();

        foreach ($notifications as $notification) {
            $notification->update(['status' => 'processing']);
            ProcessPushNotification::dispatch($notification);
        }
    }
}

