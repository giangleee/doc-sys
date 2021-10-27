<?php

namespace App\Console;

use App\Console\Commands\AlertFileSet;
use App\Console\Commands\SendAlertMails;
use App\Console\Commands\SendAlertDefault;
use App\Console\Commands\SendAlertMailWarning;
use App\Console\Commands\ImportServiceUser;
use App\Console\Commands\SyncOrganizationSystem;
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
        SendAlertMails::class,
        SendAlertDefault::class,
        AlertFileSet::class,
        SendAlertMailWarning::class,
        ImportServiceUser::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('import:serviceuser')
            ->dailyAt('3:00');
        $schedule->command('alert:default')
            ->dailyAt('9:50');
        $schedule->command('email:send')
            ->dailyAt('9:59');
        $schedule->command('alert:warning')
            ->dailyAt('10:05');
        $schedule->command('alert:fileset')
            ->monthlyOn(5, '9:55');
        $schedule->command('sync:organization')
            ->monthlyOn(1, '2:00');
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
