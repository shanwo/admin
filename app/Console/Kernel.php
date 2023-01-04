<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     * php artisan schedule:run
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->exec(date('[Y-m-d H:i:s] ').env('APP_ENV').'Start')->everyMinute();
        $schedule->command('command:export')->everyMinute()->onOneServer();
        $schedule->command('command:order_pay_timeout')->everyMinute()->onOneServer();
        $schedule->exec(date('[Y-m-d H:i:s] ').env('APP_ENV').'End')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
