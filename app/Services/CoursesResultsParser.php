<?php

namespace App\Services;

use ZipArchive;
use DOMDocument;
use Smalot\PdfParser\Parser as PdfParser;

class CoursesResultsParser
{
    public function parseDocx(string $path): array
    {
        $courses = [];
        if (!file_exists($path)) {
            return $courses;
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return $courses;
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$xml) {
            return $courses;
        }

        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        @$doc->loadXML($xml);
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $paras = $xpath->query('//w:p');
        $currentProgram = 'First Semester Program';

        foreach ($paras as $p) {
            $texts = [];
            foreach ($xpath->query('.//w:t', $p) as $t) {
                $texts[] = $t->nodeValue;
            }
            $line = trim(implode('', $texts));
            if (!$line) {
                continue;
            }

            // Track current program/semester from headings
            $lower = strtolower($line);
            if (str_contains($lower, 'pre-college') || str_contains($lower, 'pre college')) {
                $currentProgram = 'Pre-College Program';
            } elseif (str_contains($lower, 'first semester')) {
                $currentProgram = 'First Semester Program';
            } elseif (str_contains($lower, 'second semester')) {
                $currentProgram = 'Second Semester Program';
            } elseif (str_contains($lower, 'third semester practical') || str_contains($lower, 'practical')) {
                $currentProgram = 'Third Semester Practical Program';
            } elseif (str_contains($lower, 'third semester')) {
                $currentProgram = 'Third Semester Program';
            }

            // Match lines starting with a number, e.g. "1. Course Title"
            if (preg_match('/^\d+[\.\s]+(.+)$/u', $line, $m)) {
                $title = trim($m[1]);
                $title = preg_replace('/^[^\w\s]+/u', '', $title); // strip any leading bullet chars
                $title = trim($title);

                if (!$title) continue;

                // Generate course code from uppercase acronym + alphanumeric hash
                $acronym = '';
                foreach (explode(' ', preg_replace('/[^a-zA-Z0-9\s]/', '', $title)) as $word) {
                    if (!empty($word)) {
                        $acronym .= strtoupper($word[0]);
                    }
                }
                $acronym = substr($acronym, 0, 4);
                $hash = strtoupper(substr(md5($title), 0, 3));
                $code = $acronym . $hash;

                $courses[] = [
                    'code' => $code,
                    'title' => $title,
                    'credit' => 3,
                    'semester' => $currentProgram,
                    'program' => $currentProgram,
                    'raw' => ['line' => $line]
                ];
            }
        }

        return $courses;
    }

    public function parsePdfs(array $paths): array
    {
        $results = [];
        $pdfParser = new PdfParser();

        // Preset column mapping to ensure perfect alignment
        $courseMappings = [
            'Pre-College Program' => [
                'Word Course',
                'Gospel Course',
                'Kingdom Program',
                'Kingdom College',
                'Pre-College'
            ],
            'First Semester Program' => [
                'Word General Course Project',
                'Gospel Course Project',
                'MG2 Course Project',
                'Kingdom Education',
                'Kingdom College'
            ],
            'Second Semester Program' => [
                // Page 1
                'The Word In A Man',
                'Gospel Interpretation Course',
                'MG2 Course Further Classification',
                'The Gospel of Truth',
                // Page 2
                'Kingdom Course',
                'Kingdom Education',
                'Kingdom College'
            ]
        ];

        // Store intermediate results to merge second semester split pages
        $studentScores = [];

        foreach ($paths as $p) {
            if (!file_exists($p)) {
                continue;
            }

            try {
                $pdf = $pdfParser->parseFile($p);
                $pages = $pdf->getPages();
            } catch (\Exception $e) {
                continue;
            }

            foreach ($pages as $pageIndex => $page) {
                $text = $page->getText();
                $lines = preg_split('/\r?\n/', $text);

                // Detect program from page content
                $lowerText = strtolower($text);
                $program = 'First Semester Program';
                if (str_contains($lowerText, 'pre - college') || str_contains($lowerText, 'pre-college')) {
                    $program = 'Pre-College Program';
                } elseif (str_contains($lowerText, 'second semester')) {
                    $program = 'Second Semester Program';
                } elseif (str_contains($lowerText, 'first semester')) {
                    $program = 'First Semester Program';
                }

                $columns = $courseMappings[$program] ?? [];
                if (empty($columns)) {
                    continue;
                }

                // Parse student lines
                for ($i = 0; $i < count($lines); $i++) {
                    $line = trim($lines[$i]);
                    if (!$line) continue;

                    // A line starting with a student row number or containing student name + scores
                    $numberMatched = false;
                    $studentName = '';
                    $scoreString = '';

                    if (preg_match('/^(\d+)\s+([A-Za-z\s]+)([\d\s\-]+)$/u', $line, $m)) {
                        $studentName = trim($m[2]);
                        $scoreString = trim($m[3]);
                    } elseif (preg_match('/^(\d+)\s*$/', $line, $m) && isset($lines[$i+1])) {
                        // The number is on one line, name and scores are on the next line
                        $nextLine = trim($lines[$i+1]);
                        if (preg_match('/^([A-Za-z\s’\'-]+?)([\d\s\-]+)$/u', $nextLine, $m2)) {
                            $studentName = trim($m2[1]);
                            $scoreString = trim($m2[2]);
                            $i++; // skip next line
                        }
                    }

                    if ($studentName && $scoreString) {
                        // Extract scores (handle hyphens representing null or empty values)
                        $scoreParts = preg_split('/\s+/', $scoreString);
                        $numericScores = [];
                        foreach ($scoreParts as $part) {
                            $part = trim($part);
                            if ($part === '-' || $part === '') {
                                $numericScores[] = null;
                            } elseif (is_numeric($part)) {
                                $numericScores[] = (int)$part;
                            }
                        }

                        $cleanName = trim(preg_replace('/\s+/', ' ', $studentName));
                        
                        // Save scores in studentScores mapping
                        if (!isset($studentScores[$cleanName])) {
                            $studentScores[$cleanName] = [
                                'name' => $cleanName,
                                'program' => $program,
                                'scores' => []
                            ];
                        }

                        foreach ($numericScores as $idx => $score) {
                            $studentScores[$cleanName]['scores'][] = $score;
                        }
                    }
                }
            }
        }

        // Map the collected student scores to course names
        foreach ($studentScores as $name => $data) {
            $program = $data['program'];
            $columns = $courseMappings[$program] ?? [];
            $scores = $data['scores'];

            foreach ($columns as $idx => $courseTitle) {
                if (array_key_exists($idx, $scores) && $scores[$idx] !== null) {
                    $results[] = [
                        'source_pdf' => 'HO_BRANCH_RESULTS.pdf',
                        'student_identifier' => '',
                        'student_name' => $name,
                        'course_code' => '',
                        'course_title' => $courseTitle,
                        'quiz_score' => null,
                        'exam_score' => $scores[$idx],
                        'raw_row' => []
                    ];
                }
            }
        }

        return $results;
    }

    public function writePreview(string $outPath, array $courses, array $results)
    {
        $payload = ['courses' => $courses, 'results' => $results];
        file_put_contents($outPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
