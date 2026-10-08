<?php

if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.\n");
}

// 1. Boot Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Services\CloudinaryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

echo "=== Student Photo Import & Matching Tool ===\n\n";

// 2. Set database connection to external Render DB
config(['database.connections.pgsql.host' => 'dpg-d7tjfh3eo5us73bevo70-a.oregon-postgres.render.com']);
// Force reconnect to apply the config change
DB::purge('pgsql');
DB::reconnect('pgsql');

// Verify connection
try {
    $studentCount = Student::count();
    echo "Connected to production database. Total students currently in system: $studentCount\n";
} catch (\Exception $e) {
    die("Error connecting to production database: " . $e->getMessage() . "\n");
}

// 3. Resolve Cloudinary URL
$cloudinaryUrl = $argv[1] ?? env('CLOUDINARY_URL') ?? config('cloudinary.cloud_url');
if (!$cloudinaryUrl || !str_starts_with($cloudinaryUrl, 'cloudinary://')) {
    echo "Error: CLOUDINARY_URL is not set or is invalid.\n";
    echo "Usage:\n";
    echo "  php scripts/import_student_photos.php <CLOUDINARY_URL>\n\n";
    echo "Please provide your Cloudinary API environment variable (e.g. cloudinary://API_KEY:API_SECRET@CLOUD_NAME) as the first argument or set it in your local .env file.\n";
    exit(1);
}

config(['cloudinary.cloud_url' => $cloudinaryUrl]);
echo "Cloudinary Configured: Yes\n\n";

// 4. Locate and unzip the archive
$zipPath = 'c:\Users\sammy\Desktop\kasoa_students_photos_RENAMED.zip';
if (!file_exists($zipPath)) {
    die("Error: Zip file not found at '$zipPath'.\n");
}

$tempDir = storage_path('app/temp_photos');
if (file_exists($tempDir)) {
    // Clear old temp files
    array_map('unlink', glob("$tempDir/*.*"));
    rmdir($tempDir);
}
mkdir($tempDir, 0755, true);

echo "Extracting $zipPath to temporary directory...\n";
$zip = new ZipArchive();
if ($zip->open($zipPath) === TRUE) {
    $zip->extractTo($tempDir);
    $zip->close();
    echo "Extraction complete!\n\n";
} else {
    die("Error: Failed to open zip file.\n");
}

// Get all files extracted
$files = array_filter(scandir($tempDir), function($f) use ($tempDir) {
    return is_file("$tempDir/$f") && !str_starts_with($f, '.');
});

echo "Found " . count($files) . " files in zip archive.\n";

// Helper for name matching
function matchStudentName($filename, $students) {
    // Normalize filename name part
    // E.g. "Abaka Assin Doris_passport.JPG" -> "abaka assin doris"
    $namePart = preg_replace('/_(passport|background)\.[a-zA-Z0-9]+$/i', '', $filename);
    $normalizedFile = strtolower(preg_replace('/[^a-zA-Z\s]/', '', $namePart));
    $fileTokens = array_filter(explode(' ', $normalizedFile));

    $bestMatch = null;
    $highestScore = 0;

    foreach ($students as $student) {
        $normalizedStud = strtolower(preg_replace('/[^a-zA-Z\s]/', '', $student->user->full_name));
        $studTokens = array_filter(explode(' ', $normalizedStud));

        // Let's count matching tokens
        $matchCount = 0;
        foreach ($fileTokens as $ft) {
            foreach ($studTokens as $st) {
                if ($ft === $st || levenshtein($ft, $st) <= 1) {
                    $matchCount++;
                    break;
                }
            }
        }

        // Calculate a score
        $score = $matchCount / max(count($fileTokens), count($studTokens));
        if ($score > $highestScore && $score >= 0.5) {
            $highestScore = $score;
            $bestMatch = $student;
        }
    }

    return [$bestMatch, $highestScore];
}

// 5. Match and upload
$students = Student::with('user')->get();
$matches = [];
$failures = [];

foreach ($files as $file) {
    $filePath = "$tempDir/$file";
    
    // Determine type (passport or background)
    $isPassport = stripos($file, '_passport') !== false;
    $isBackground = stripos($file, '_background') !== false;
    
    if (!$isPassport && !$isBackground) {
        // Guess based on name
        if (stripos($file, 'passport') !== false) {
            $isPassport = true;
        } else {
            $isBackground = true;
        }
    }

    list($student, $score) = matchStudentName($file, $students);
    
    if ($student) {
        $studentId = $student->id;
        if (!isset($matches[$studentId])) {
            $matches[$studentId] = [
                'student' => $student,
                'passport_file' => null,
                'background_file' => null,
            ];
        }
        
        if ($isPassport) {
            $matches[$studentId]['passport_file'] = $filePath;
        } else {
            $matches[$studentId]['background_file'] = $filePath;
        }
        echo "Matched file '$file' to student '{$student->user->full_name}' (Confidence: " . round($score * 100) . "%)\n";
    } else {
        $failures[] = $file;
        echo "Warning: Could not find student matching file '$file'\n";
    }
}

echo "\n--- Match Phase Complete ---\n";
echo "Students matched: " . count($matches) . "\n";
echo "Unmatched files: " . count($failures) . "\n\n";

if (count($matches) === 0) {
    echo "No matching students found. Stopping upload process.\n";
    cleanup($tempDir);
    exit(0);
}

echo "Starting upload to Cloudinary and database update...\n";
$successCount = 0;

foreach ($matches as $studentId => $data) {
    /** @var Student $student */
    $student = $data['student'];
    $updates = [];

    echo "Processing '{$student->user->full_name}'...\n";

    if ($data['passport_file']) {
        echo "  - Uploading passport photo...\n";
        $fileObj = new UploadedFile($data['passport_file'], basename($data['passport_file']), mime_content_type($data['passport_file']), null, true);
        $url = CloudinaryService::upload($fileObj, 'students/photos');
        if ($url) {
            $updates['photo'] = $url;
            echo "    Successfully uploaded to Cloudinary: $url\n";
        } else {
            echo "    Failed to upload passport photo to Cloudinary.\n";
        }
    }

    if ($data['background_file']) {
        echo "  - Uploading background photo...\n";
        $fileObj = new UploadedFile($data['background_file'], basename($data['background_file']), mime_content_type($data['background_file']), null, true);
        $url = CloudinaryService::upload($fileObj, 'students/backgrounds');
        if ($url) {
            $updates['background_photo'] = $url;
            echo "    Successfully uploaded to Cloudinary: $url\n";
        } else {
            echo "    Failed to upload background photo to Cloudinary.\n";
        }
    }

    if (!empty($updates)) {
        // Save to DB
        $student->update($updates);
        echo "  - Updated student record in database!\n";
        $successCount++;
    }
}

echo "\nUpload complete! Successfully processed $successCount students.\n";

// Cleanup temp files
cleanup($tempDir);

function cleanup($dir) {
    echo "Cleaning up temporary files...\n";
    if (file_exists($dir)) {
        array_map('unlink', glob("$dir/*.*"));
        rmdir($dir);
    }
    echo "Cleanup complete!\n";
}
