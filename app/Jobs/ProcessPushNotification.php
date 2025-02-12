<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PushNotification;

class ProcessPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notification;

    public function __construct(PushNotification $notification)
    {
        $this->notification = $notification;
    }

    public function handle()
    {
        $status = rand(0, 1) ? 'complete' : 'error';
        $this->notification->update(['status' => $status]);
    }
}
