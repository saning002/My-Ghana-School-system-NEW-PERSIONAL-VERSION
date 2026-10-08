<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Backup;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        $backups  = $query->paginate(30)->withQueryString();
        $schools  = Tenant::orderBy('name')->get(['id', 'name']);
        $totalSize = Backup::where('status', 'completed')->sum('file_size');

        return view('owner.backups.index', compact('backups', 'schools', 'totalSize'));
    }

    /**
     * Trigger a manual on-demand backup for a tenant.
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
     * Download a backup file.
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
     * Push backup to cloud (S3).
     */
    public function cloudPush(Backup $backup)
    {
        if ($backup->status !== 'completed' || !Storage::exists($backup->file_path)) {
            return back()->with('error', 'Backup file not available.');
        }

        try {
            $cloudPath = 'backups/' . $backup->tenant->slug . '/' . $backup->filename;
            $contents  = Storage::get($backup->file_path);

            Storage::disk('s3')->put($cloudPath, $contents);

            $backup->update([
                'cloud_synced'    => true,
                'cloud_synced_at' => now(),
                'storage_type'    => 's3',
            ]);

            AuditLog::record('backup.cloud_pushed', "Backup '{$backup->filename}' pushed to S3", $backup->tenant_id);

            return back()->with('success', 'Backup pushed to cloud (S3) successfully.');
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

                // Attach if under 20MB, otherwise send a note
                $fileSize = $backup->file_size ?? 0;
                if ($fileSize < 20 * 1024 * 1024) {
                    $mail->attach(Storage::path($backup->file_path), [
                        'as'   => $backup->filename,
                        'mime' => 'application/gzip',
                    ]);
                } else {
                    $mail->with(['large_file' => true]);
                }
            });

            $backup->update(['emailed' => true, 'emailed_at' => now()]);

            AuditLog::record('backup.emailed', "Backup '{$backup->filename}' emailed to {$tenant->admin_email}", $tenant->id);

            return back()->with('success', "Backup emailed to {$tenant->admin_email}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Email failed: ' . $e->getMessage());
        }
    }

    /**
     * Restore a tenant's DB from a backup file.
     */
    public function restore(Backup $backup)
    {
        if ($backup->status !== 'completed' || !Storage::exists($backup->file_path)) {
            return back()->with('error', 'Backup file not available for restore.');
        }

        $tenant = $backup->tenant;

        try {
            // First take a pre-restore backup
            $this->runBackup($tenant, 'pre_restore');

            $filePath = Storage::path($backup->file_path);

            // Decompress if gzipped
            $sqlPath = $filePath;
            if (str_ends_with($filePath, '.gz')) {
                $sqlPath = str_replace('.gz', '', $filePath);
                $gz = gzopen($filePath, 'rb');
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

            $command = sprintf(
                'mysql --host=%s --port=%s --user=%s --password=%s %s < %s 2>&1',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($user),
                escapeshellarg($password),
                escapeshellarg($db),
                escapeshellarg($sqlPath)
            );

            exec($command, $output, $exitCode);

            // Clean up decompressed file
            if ($sqlPath !== $filePath && file_exists($sqlPath)) {
                unlink($sqlPath);
            }

            if ($exitCode !== 0) {
                throw new \Exception('mysql restore failed: ' . implode("\n", $output));
            }

            AuditLog::record(
                'backup.restored',
                "Backup '{$backup->filename}' restored for '{$tenant->name}'",
                $tenant->id
            );

            return back()->with('success', "Database restored for {$tenant->name} from backup {$backup->filename}.");
        } catch (\Exception $e) {
            AuditLog::record('backup.restore_failed', "Restore failed: " . $e->getMessage(), $tenant->id);
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

    public function runBackup(Tenant $tenant, string $trigger = 'manual'): Backup
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename  = Str::slug($tenant->slug) . '_' . $timestamp . '.sql.gz';
        $dir       = 'owner/backups/' . $tenant->slug;
        $path      = $dir . '/' . $filename;

        // Ensure directory exists
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

            // Build mysqldump command piped through gzip
            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s | gzip > %s 2>&1',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($user),
                escapeshellarg($password),
                escapeshellarg($db),
                escapeshellarg($fullPath)
            );

            exec($command, $output, $exitCode);

            if ($exitCode !== 0) {
                throw new \Exception('mysqldump failed: ' . implode("\n", $output));
            }

            $fileSize = file_exists($fullPath) ? filesize($fullPath) : 0;

            $backup->update([
                'status'    => 'completed',
                'file_size' => $fileSize,
            ]);

            // Update last backup timestamp on tenant
            $tenant->update(['last_backup_at' => now()]);

        } catch (\Exception $e) {
            $backup->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }

        return $backup->fresh();
    }
}
