<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\ImportCoursesResults;
use App\Console\Commands\GeneratePerStudentInvoices;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        ImportCoursesResults::class,
        GeneratePerStudentInvoices::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('import:courses-results')->daily();

        // Generate per-student invoices daily — checks if a new term has started
        // and generates invoices for schools on per-student billing.
        // Also marks overdue invoices past their due date.
        $schedule->command('billing:generate-invoices')->dailyAt('06:00');
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }
}
