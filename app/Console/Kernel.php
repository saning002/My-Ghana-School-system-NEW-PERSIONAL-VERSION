<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\ImportCoursesResults;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        ImportCoursesResults::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('import:courses-results')->daily();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }
}
