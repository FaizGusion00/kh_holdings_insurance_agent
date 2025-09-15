<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Process expired policies daily at 2 AM
        $schedule->command('policies:process-expired')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Send renewal reminders daily at 9 AM
        $schedule->command('policies:send-renewal-reminders')
            ->dailyAt('09:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Process renewal commissions every hour
        $schedule->command('policies:process-renewal-commissions')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground();

        // Process monthly commissions on the 1st of each month at 3 AM
        $schedule->command('queue:work --stop-when-empty')
            ->dailyAt('03:00')
            ->withoutOverlapping();

        // Clean up notifications daily at 4 AM
        $schedule->command('notifications:cleanup')
            ->dailyAt('04:00')
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
