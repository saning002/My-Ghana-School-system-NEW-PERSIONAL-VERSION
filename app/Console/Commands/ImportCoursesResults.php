<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ImportCoursesResults extends Command
{
    protected $signature = 'import:courses-results {--no-seed : Run parser only, skip seeder}';
    protected $description = 'Run the Python parser for courses/results and seed the DB with ImportCoursesAndResultsSeeder';

    public function handle()
    {
        $this->info('Running Python parser...');

        $script = base_path('scripts') . DIRECTORY_SEPARATOR . 'parse_courses_and_results.py';
        if (!file_exists($script)) {
            $this->error("Parser script not found at {$script}");
            return 1;
        }

        $process = new Process(['python3', $script]);
        $process->setTimeout(300);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        if (!$process->isSuccessful()) {
            $this->error('Parser failed');
            return 1;
        }

        $this->info('Parser finished');

        if ($this->option('no-seed')) {
            $this->info('Skipping seeder as requested');
            return 0;
        }

        $this->info('Seeding ImportCoursesAndResultsSeeder...');
        $seedProcess = new Process(['php', 'artisan', 'db:seed', '--class=ImportCoursesAndResultsSeeder', '--force']);
        $seedProcess->setTimeout(300);
        $seedProcess->run(function ($type, $buffer) {
            echo $buffer;
        });

        if (!$seedProcess->isSuccessful()) {
            $this->error('Seeding failed');
            return 1;
        }

        $this->info('Import completed');
        return 0;
    }
}
