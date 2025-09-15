<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class CleanupNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:cleanup';

    /**
     * The console command description.
     */
    protected $description = 'Clean up expired and old notifications';

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning up notifications...');

        // Delete expired notifications
        $expiredCount = $this->notificationService->deleteExpired();
        $this->line("Deleted {$expiredCount} expired notifications");

        // Clean up old notifications (keep only last 100 per user)
        $this->notificationService->cleanupOldNotifications();
        $this->line("Cleaned up old notifications (kept last 100 per user)");

        $this->info('Notification cleanup completed!');
    }
}
