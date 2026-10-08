<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CoursesResultsParser;

class ImportCoursesResultsPhp extends Command
{
    protected $signature = 'import:courses-results:php {--no-seed : skip seeder}';
    protected $description = 'Parse DOCX and PDF results using PHP and seed ImportCoursesAndResultsSeeder';

    public function handle(CoursesResultsParser $parser)
    {
        $this->info('Parsing DOCX and PDFs with PHP parser');

        $docx = 'C:\\Users\\sammy\\Desktop\\all sem courses.docx';
        $pdfs = [
            'C:\\Users\\sammy\\Desktop\\All students\\PRE-COLLEGE RESULTS (HO BRANCH).pdf',
            'C:\\Users\\sammy\\Desktop\\All students\\FIRST SEMESTER RESULT (HO BRANCH).pdf',
            'C:\\Users\\sammy\\Desktop\\All students\\SECOND SEMESTER RESULTS (HO BRANCH).pdf',
        ];

        $courses = $parser->parseDocx($docx);
        $results = $parser->parsePdfs($pdfs);

        $out = base_path('scripts') . DIRECTORY_SEPARATOR . 'courses_and_results_preview.json';
        $parser->writePreview($out, $courses, $results);
        $this->info('Wrote preview to ' . $out);

        if ($this->option('no-seed')) {
            $this->info('Skipping seeder');
            return 0;
        }

        $this->info('Running seeder ImportCoursesAndResultsSeeder');
        $exit = $this->call('db:seed', ['--class' => 'ImportCoursesAndResultsSeeder', '--force' => true]);
        return $exit;
    }
}
