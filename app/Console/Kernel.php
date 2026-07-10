<?php

namespace App\Console;

use App\Models\Setting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        \App\Console\Commands\DailyIncomeDis::class,
        \App\Console\Commands\LevelIncomeDis::class,
        \App\Console\Commands\BinaryDailyClosing::class,
    ];
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('app:daily-income-dis')->daily();
        $schedule->command('app:level-income-dis')->daily();
        $schedule->command('binary:daily-closing')->dailyAt(Setting::get('binary_closing_time', '23:59'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
