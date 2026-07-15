<?php

namespace App\Console;

use App\Console\Commands\ProjectBackup;
use App\Console\Commands\ProjectRestore;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ProjectBackup::class,
        ProjectRestore::class,
        \App\Console\Commands\SendReminders::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Schedule reminders
        $schedule->command('reminders:send')->everyMinute();
        
        // Example: daily backup at 02:00
        // $schedule->command('project:backup --include-public')->dailyAt('02:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
