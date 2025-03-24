<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Announcement;
use Carbon\Carbon;

class DeleteOldAnnouncements extends Command
{
    protected $signature = 'announcements:delete-old';
    protected $description = 'Удаляет объявления старше 20 дней';

    public function handle()
    {
        $threshold = Carbon::now()->subDays(20);

        $deleted = Announcement::where('created_at', '<', $threshold)->delete();

        $this->info("Удалено {$deleted} объявлений старше 20 дней.");
    }
}
