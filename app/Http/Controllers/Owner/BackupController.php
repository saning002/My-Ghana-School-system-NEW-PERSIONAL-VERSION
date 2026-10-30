<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Backup;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class BackupController extends Controller
{
    public function index(Request $request)
    {
        $query = Backup::with('tenant')->orderByDesc('created_at');

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $backups   = $query->paginate(30)->withQueryString();
        $schools   = Tenant::orderBy('name')->get(['id', 'name']);
        $totalSize = Backup::where('status', 'completed')->sum('file_size');

        return view('owner.backups.index', compact('backups', 'schools', 'totalSize'));
    }

    /**
     * Trigger a manual SQL backup for a tenant (pg_dump → gzip).
     */
    public function create(Tenant $tenant)
    {
        $backup = $this->runBackup($tenant, 'manual');

        if ($backup->status === 'completed') {
            AuditLog::record('backup.created', "Manual backup completed for '{$tenant->name}'", $tenant->id);
            return back()->with('success', "Backup completed for {$tenant->name}. File: {$backup->filename}");
        }

        return back()->with('error', "Backup failed: {$backup->error_message}");
    }

    /**
     * Download a backup file (SQL.GZ or ZIP).
     */
    public function download(Backup $backup)
    {
        if ($backup->status !== 'completed') {
            return back()->with('error', 'Backup is not available for download.');
        }

        if (!Storage::exists($backup->file_path)) {
            return back()->with('error', 'Backup file not found on disk.');
        }

        AuditLog::record('backup.downloaded', "Backup '{$backup->filename}' downloaded", $backup->tenant_id);

        return Storage::download($backup->file_path, $backup->filename);
    }

    /**
     * Generate and download a CSV/Excel export (ZIP of CSV files per table).
     * This is what you send to schools so they can open their data in Excel.
     */
    public function downloadCsv(Backup $backup)
    {
        $tenant = $backup->tenant;

        try {
            // Connect to tenant DB
            DB::purge('tenant');
            Config::set('database.connections.tenant', $tenant->dbConfig());
            DB::setDefaultConnection('tenant');

            // Tables to export — human-readable ones only
            $tables = [
                'students'            => 'Students',
                'users'               => 'Staff',
                'programs'            => 'Programs',
                'courses'             => 'Subjects_Courses',
                'enrollments'         => 'Enrollments',
                'attendances'         => 'Attendance',
                'exam_scores'         => 'Exam_Scores',
                'payments'            => 'Fee_Payments',
                'student_fees'        => 'Student_Fees',
                'program_fees'        => 'Program_Fees',
                'academic_sessions'   => 'Academic_Sessions',
                'school_events'       => 'School_Events',
            ];

            $zipFilename = Str::slug($tenant->slug) . '_export_' . now()->format('Y-m-d') . '.zip';
            $zipPath     = storage_path('app/tmp/' . $zipFilename);

            // Ensure tmp dir exists
            if (!is_dir(storage_path('app/tmp'))) {
                mkdir(storage_path('app/tmp'), 0775, true);
            }

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Could not create ZIP file.');
            }

            $exportedCount = 0;

            foreach ($tables as $table => $label) {
                try {
                    // Check table exists
                    $exists = DB::connection('tenant')
                        ->select("SELECT to_regclass('public.\"{$table}\"') as t");
                    if (empty($exists[0]->t)) continue;

                    $rows = DB::connection('tenant')->table($table)->get();
                    if ($rows->isEmpty()) {
                        // Add empty CSV with just headers
                        $cols = DB::connection('tenant')
                            ->getSchemaBuilder()
                            ->getColumnListing($table);
                        if (empty($cols)) continue;
                        $csv = implode(',', array_map(fn($c) => '"' . $c . '"', $cols)) . "\n";
                    } else {
                        $cols = array_keys((array) $rows->first());
                        $csv  = implode(',', array_map(fn($c) => '"' . $c . '"', $cols)) . "\n";
                        foreach ($rows as $row) {
                            $values = array_map(function ($v) {
                                if ($v === null) return '';
                                // Escape double quotes, wrap in quotes if contains comma/newline/quote
                                $v = str_replace('"', '""', $v);
                                return '"' . $v . '"';
                            }, (array) $row);
                            $csv .= implode(',', $values) . "\n";
                        }
                    }

                    $zip->addFromString($label . '.csv', $csv);
                    $exportedCount++;
                } catch (\Throwable $e) {
                    // Skip tables that error — don't fail the whole export
                }
            }

            // Add a README
            $readme = "SCHOOL DATA EXPORT\n";
            $readme .= "==================\n";
            $readme .= "School: {$tenant->name}\n";
            $readme .= "Exported: " . now()->format('d F Y H:i') . "\n";
            $readme .= "Tables: {$exportedCount}\n\n";
            $readme .= "HOW TO OPEN:\n";
            $readme .= "1. Extract this ZIP file\n";
            $readme .= "2. Open any .csv file in Microsoft Excel or Google Sheets\n";
            $readme .= "3. Each file contains data from one part of the system\n\n";
            $readme .= "FILES:\n";
            foreach ($tables as $table => $label) {
                $readme .= "  {$label}.csv\n";
            }
            $zip->addFromString('README.txt', $readme);

            $zip->close();

            // Reset DB connection
            DB::setDefaultConnection('pgsql');
            DB::purge('tenant');

            AuditLog::record('backup.csv_downloaded',
                "CSV export downloaded for '{$tenant->name}'", $tenant->id);

            return response()->download($zipPath, $zipFilename, [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(true);

        } catch (\Throwable $e) {
            DB::setDefaultConnection('pgsql');
            return back()->with('error', 'CSV export failed: ' . $e->getMessage());
        }
    }

    /**
     * Push backup to cloud (Backblaze B2 or S3).
     */
    public function cloudPush(Backup $backup)
    {
        if ($backup->status !== 'completed' || !Storage::exists($backup->file_path)) {
            return back()->with('error', 'Backup file not available.');
        }

        try {
            $tenant    = $backup->tenant;
            $cloudPath = 'backups/' . $tenant->slug . '/' . $backup->filename;
            $contents  = Storage::get($backup->file_path);

            $disk = 'b2';
            if ($tenant->b2_key_id && $tenant->b2_bucket_name) {
                Config::set('filesystems.disks.b2', [
                    'driver'         => 'b2',
                    'keyId'          => $tenant->b2_key_id,
                    'applicationKey' => $tenant->b2_application_key,
                    'bucketName'     => $tenant->b2_bucket_name,
                ]);
            } else {
                $disk = 's3';
            }

            Storage::disk($disk)->put($cloudPath, $contents);

            $backup->update([
                'cloud_synced'    => true,
                'cloud_synced_at' => now(),
                'storage_type'    => $disk,
            ]);

            AuditLog::record('backup.cloud_pushed',
                "Backup '{$backup->filename}' pushed to {$disk}", $backup->tenant_id);

            return back()->with('success', "Backup pushed to cloud ({$disk}) successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Cloud push failed: ' . $e->getMessage());
        }
    }

    /**
     * Email backup file to school's admin.
     */
    public function email(Backup $backup)
    {
        if ($backup->status !== 'completed' || !Storage::exists($backup->file_path)) {
            return back()->with('error', 'Backup file not available.');
        }

        $tenant = $backup->tenant;

        try {
            Mail::send('owner.emails.backup', compact('backup', 'tenant'), function ($mail) use ($backup, $tenant) {
                $mail->to($tenant->admin_email, $tenant->admin_name)
                     ->subject("Database Backup — {$tenant->name} — " . now()->format('d M Y'));

                $fileSize = $backup->file_size ?? 0;
                if ($fileSize < 20 * 1024 * 1024) {
                    $mail->attach(Storage::path($backup->file_path), [
                        'as'   => $backup->filename,
                        'mime' => 'application/gzip',
                    ]);
                }
            });

            $backup->update(['emailed' => true, 'emailed_at' => now()]);

            AuditLog::record('backup.emailed',
                "Backup '{$backup->filename}' emailed to {$tenant->admin_email}", $tenant->id);

            return back()->with('success', "Backup emailed to {$tenant->admin_email}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Email failed: ' . $e->getMessage());
        }
    }

    /**
     * Restore tenant DB from a backup SQL file.
     * Uses psql (PostgreSQL) instead of mysql.
     */
    public function restore(Backup $backup)
    {
        if ($backup->status !== 'completed' || !Storage::exists($backup->file_path)) {
            return back()->with('error', 'Backup file not available for restore.');
        }

        $tenant = $backup->tenant;

        try {
            // Safety: take a pre-restore backup first
            $this->runBackup($tenant, 'pre_restore');

            $filePath = Storage::path($backup->file_path);

            // Decompress .gz if needed
            $sqlPath = $filePath;
            if (str_ends_with($filePath, '.gz')) {
                $sqlPath = str_replace('.gz', '', $filePath);
                $gz  = gzopen($filePath, 'rb');
                $out = fopen($sqlPath, 'wb');
                while (!gzeof($gz)) {
                    fwrite($out, gzread($gz, 65536));
                }
                gzclose($gz);
                fclose($out);
            }

            $host     = $tenant->db_host;
            $port     = $tenant->db_port;
            $db       = $tenant->db_name;
            $user     = $tenant->db_username;
            $password = $tenant->db_password;

            // Use psql for PostgreSQL restore
            $env     = 'PGPASSWORD=' . escapeshellarg($password);
            $sslMode = (str_contains($host, 'neon.tech') || str_contains($host, 'render.com')
                        || str_contains($host, 'supabase.co'))
                       ? ' sslmode=require' : '';

            $command = sprintf(
                '%s psql --host=%s --port=%s --username=%s --dbname=%s --file=%s 2>&1',
                $env,
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($user),
                escapeshellarg($db . $sslMode),
                escapeshellarg($sqlPath)
            );

            exec($command, $output, $exitCode);

            // Clean up decompressed file
            if ($sqlPath !== $filePath && file_exists($sqlPath)) {
                unlink($sqlPath);
            }

            if ($exitCode !== 0) {
                throw new \Exception('psql restore failed: ' . implode("\n", $output));
            }

            AuditLog::record('backup.restored',
                "Backup '{$backup->filename}' restored for '{$tenant->name}'", $tenant->id);

            return back()->with('success',
                "Database restored for {$tenant->name} from backup {$backup->filename}.");

        } catch (\Exception $e) {
            AuditLog::record('backup.restore_failed',
                "Restore failed: " . $e->getMessage(), $tenant->id);
            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete a backup record and file.
     */
    public function destroy(Backup $backup)
    {
        $filename = $backup->filename;
        $tenantId = $backup->tenant_id;

        if (Storage::exists($backup->file_path)) {
            Storage::delete($backup->file_path);
        }

        $backup->delete();

        AuditLog::record('backup.deleted', "Backup '{$filename}' deleted", $tenantId);

        return back()->with('success', "Backup '{$filename}' deleted.");
    }

    // ─── Core Backup Runner ────────────────────────────────────────────────────

    /**
     * Run a pg_dump backup of the tenant's PostgreSQL database.
     * Falls back to a PHP-native SQL export if pg_dump is unavailable (Render free tier).
     */
    public function runBackup(Tenant $tenant, string $trigger = 'manual'): Backup
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename  = Str::slug($tenant->slug) . '_' . $timestamp . '.sql.gz';
        $dir       = 'owner/backups/' . $tenant->slug;
        $path      = $dir . '/' . $filename;

        Storage::makeDirectory($dir);
        $fullPath = Storage::path($path);

        $backup = Backup::create([
            'tenant_id'    => $tenant->id,
            'filename'     => $filename,
            'file_path'    => $path,
            'storage_type' => 'local',
            'trigger'      => $trigger,
            'status'       => 'pending',
        ]);

        try {
            $host     = $tenant->db_host;
            $port     = $tenant->db_port;
            $db       = $tenant->db_name;
            $user     = $tenant->db_username;
            $password = $tenant->db_password;

            $isNeon = str_contains($host, 'neon.tech')
                   || str_contains($host, 'render.com')
                   || str_contains($host, 'supabase.co');

            // Try pg_dump first
            $env     = 'PGPASSWORD=' . escapeshellarg($password);
            $sslOpt  = $isNeon ? ' --set=sslmode=require' : '';
            $command = sprintf(
                '%s pg_dump --host=%s --port=%s --username=%s --dbname=%s --format=plain --no-password --clean%s | gzip > %s 2>&1',
                $env,
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($user),
                escapeshellarg($db),
                $sslOpt,
                escapeshellarg($fullPath)
            );

            exec($command, $output, $exitCode);

            // If pg_dump not available (Render free tier), fall back to PHP native export
            if ($exitCode !== 0 || !file_exists($fullPath) || filesize($fullPath) === 0) {
                $this->phpNativeBackup($tenant, $fullPath);
            }

            $fileSize = file_exists($fullPath) ? filesize($fullPath) : 0;

            if ($fileSize === 0) {
                throw new \Exception('Backup produced an empty file.');
            }

            $backup->update([
                'status'    => 'completed',
                'file_size' => $fileSize,
            ]);

            $tenant->update(['last_backup_at' => now()]);

        } catch (\Exception $e) {
            $backup->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }

        return $backup->fresh();
    }

    /**
     * PHP-native backup — reads all tables via PDO and writes INSERT statements.
     * Used as fallback when pg_dump is not available on the server.
     */
    private function phpNativeBackup(Tenant $tenant, string $outputPath): void
    {
        DB::purge('tenant');
        Config::set('database.connections.tenant', $tenant->dbConfig());
        DB::setDefaultConnection('tenant');

        $sql = "-- SaningTech School System Backup\n";
        $sql .= "-- School: {$tenant->name}\n";
        $sql .= "-- Date: " . now()->toDateTimeString() . "\n";
        $sql .= "-- Format: PostgreSQL SQL\n\n";

        try {
            // Get all user tables
            $tables = DB::connection('tenant')
                ->select("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename");

            foreach ($tables as $tableRow) {
                $table = $tableRow->tablename;

                try {
                    $sql .= "\n-- Table: {$table}\n";
                    $sql .= "TRUNCATE TABLE \"{$table}\" CASCADE;\n";

                    $rows = DB::connection('tenant')->table($table)->get();

                    foreach ($rows as $row) {
                        $row   = (array) $row;
                        $cols  = array_keys($row);
                        $vals  = array_map(function ($v) {
                            if ($v === null) return 'NULL';
                            if (is_bool($v)) return $v ? 'TRUE' : 'FALSE';
                            if (is_numeric($v)) return $v;
                            return "'" . str_replace("'", "''", $v) . "'";
                        }, array_values($row));

                        $sql .= 'INSERT INTO "' . $table . '" ("'
                            . implode('","', $cols)
                            . '") VALUES ('
                            . implode(',', $vals)
                            . ");\n";
                    }
                } catch (\Throwable $e) {
                    $sql .= "-- Error backing up {$table}: " . $e->getMessage() . "\n";
                }
            }
        } finally {
            DB::setDefaultConnection('pgsql');
            DB::purge('tenant');
        }

        // Write gzipped
        $gz = gzopen($outputPath, 'wb9');
        gzwrite($gz, $sql);
        gzclose($gz);
    }
}
