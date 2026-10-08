<?php

namespace App\Console\Commands;

use App\Http\Controllers\Owner\BackupController;
use App\Models\Backup;
use App\Models\Tenant;
use Illuminate\Console\Command;

class BackupAllTenants extends Command
{
    protected $signature   = 'tenants:backup {--frequency=all : daily|weekly|monthly|all}';
    protected $description = 'Run scheduled backups for all active tenants based on their backup_frequency setting.';

    public function handle(BackupController $backupController): int
    {
        $frequency = $this->option('frequency');

        $query = Tenant::where('status', 'active');

        if ($frequency !== 'all') {
            $query->where('backup_frequency', $frequency);
        } else {
            $query->whereIn('backup_frequency', ['daily', 'weekly', 'monthly']);
        }

        // Filter weekly: only run on Sundays
        // Filter monthly: only run on 1st of month
        $tenants = $query->get()->filter(function (Tenant $tenant) use ($frequency) {
            if ($frequency !== 'all') return true;

            return match($tenant->backup_frequency) {
                'daily'   => true,
                'weekly'  => now()->dayOfWeek === 0, // Sunday
                'monthly' => now()->day === 1,
                default   => false,
            };
        });

        if ($tenants->isEmpty()) {
            $this->info('No tenants to back up at this time.');
            return 0;
        }

        $this->info("Backing up {$tenants->count()} tenant(s)...");
        $bar = $this->output->createProgressBar($tenants->count());
        $bar->start();

        $success = 0;
        $failed  = 0;

        foreach ($tenants as $tenant) {
            $backup = $backupController->runBackup($tenant, 'scheduled');

            if ($backup->status === 'completed') {
                $success++;
            } else {
                $failed++;
                $this->newLine();
                $this->error("Failed: {$tenant->name} — {$backup->error_message}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done. Success: {$success} | Failed: {$failed}");

        // Prune old backups (keep last 10 per tenant)
        $this->pruneOldBackups();

        return $failed > 0 ? 1 : 0;
    }

    private function pruneOldBackups(): void
    {
        $tenantIds = Tenant::pluck('id');

        foreach ($tenantIds as $tenantId) {
            $toDelete = Backup::where('tenant_id', $tenantId)
                ->where('trigger', 'scheduled')
                ->where('status', 'completed')
                ->orderByDesc('created_at')
                ->skip(10)
                ->take(999)
                ->get();

            foreach ($toDelete as $old) {
                if (\Illuminate\Support\Facades\Storage::exists($old->file_path)) {
                    \Illuminate\Support\Facades\Storage::delete($old->file_path);
                }
                $old->delete();
            }
        }
    }
}
