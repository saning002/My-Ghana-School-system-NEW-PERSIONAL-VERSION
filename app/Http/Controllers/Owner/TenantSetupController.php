<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantSetupController extends Controller
{
    /**
     * Run migrations on a tenant's database.
     */
    public function runMigrations(Tenant $tenant)
    {
        $results = [];

        try {
            // Connect to tenant DB
            DB::purge('tenant');
            Config::set('database.connections.tenant', $tenant->dbConfig());
            DB::setDefaultConnection('tenant');
            DB::connection('tenant')->getPdo();
            $results['connection'] = 'Connected to ' . $tenant->db_name;
        } catch (\Throwable $e) {
            DB::setDefaultConnection('pgsql');
            return back()->with('setup_error', 'DB Connection failed: ' . $e->getMessage());
        }

        // For PostgreSQL: rollback any aborted transaction before running migrations
        try {
            DB::connection('tenant')->statement('ROLLBACK');
        } catch (\Throwable $e) {}

        // Ensure migrations table exists so Laravel can track what's been run
        try {
            Artisan::call('migrate:install', ['--database' => 'tenant']);
        } catch (\Throwable $e) {}

        // If users table already exists, mark existing migrations as run to avoid duplicate errors
        $usersExists = false;
        try {
            $usersExists = DB::connection('tenant')->getSchemaBuilder()->hasTable('users');
        } catch (\Throwable $e) {}

        if ($usersExists) {
            // Mark all migrations as already run so only NEW ones execute
            $migrationFiles = glob(database_path('migrations/*.php'));
            foreach ($migrationFiles as $file) {
                $migrationName = pathinfo($file, PATHINFO_FILENAME);
                $alreadyLogged = DB::connection('tenant')
                    ->table('migrations')
                    ->where('migration', $migrationName)
                    ->exists();
                if (!$alreadyLogged) {
                    DB::connection('tenant')->table('migrations')->insert([
                        'migration' => $migrationName,
                        'batch'     => 1,
                    ]);
                }
            }
            $results['existing_tables'] = 'Existing DB detected — marked all migrations as run, will only execute new ones';
        }

        try {
            // Run migrations — each migration runs in its own transaction
            Artisan::call('migrate', [
                '--force'    => true,
                '--database' => 'tenant',
                '--isolated' => false,
            ]);
            $output = trim(Artisan::output());
            $results['migrations'] = $output ?: 'All migrations ran successfully (nothing new to run)';
        } catch (\Throwable $e) {
            // Try running again after explicit rollback
            try {
                DB::connection('tenant')->statement('ROLLBACK');
            } catch (\Throwable $e2) {}

            // Re-attempt
            try {
                Artisan::call('migrate', ['--force' => true, '--database' => 'tenant']);
                $results['migrations'] = trim(Artisan::output()) ?: 'Migrations completed on retry';
            } catch (\Throwable $e3) {
                $results['migrations'] = 'FAILED: ' . $e3->getMessage();
            }
        }

        // Run seeder — only if migrations succeeded (settings table must exist)
        try {
            // Check settings table exists before seeding
            $settingsExists = DB::connection('tenant')
                ->getSchemaBuilder()
                ->hasTable('settings');

            if ($settingsExists) {
                Artisan::call('db:seed', [
                    '--class'    => 'SettingsSeeder',
                    '--database' => 'tenant',
                    '--force'    => true,
                ]);
                $results['seeder'] = 'Settings seeded OK';
            } else {
                $results['seeder'] = 'SKIPPED — settings table not found (migrations may have partially failed, try again)';
            }
        } catch (\Throwable $e) {
            $results['seeder'] = 'Seeder failed: ' . $e->getMessage();
        }

        // Reset to central DB
        DB::setDefaultConnection('pgsql');
        DB::purge('tenant');

        // Determine overall status
        $failed = str_contains($results['migrations'] ?? '', 'FAILED');
        $message = implode(' | ', array_filter($results));

        AuditLog::record(
            'tenant.migrations_run',
            "Migrations on '{$tenant->name}': " . $message,
            $tenant->id
        );

        if ($failed) {
            return back()
                ->with('setup_error', 'Some migrations failed: ' . $message)
                ->with('active_tab', 'setup');
        }

        return back()
            ->with('setup_success', 'Migrations complete: ' . $message)
            ->with('active_tab', 'setup');
    }

    /**
     * Create the initial super admin for a tenant's school.
     */
    public function createAdmin(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'admin_full_name' => 'required|string|max:255',
            'admin_email'     => 'required|email|max:255',
            'admin_password'  => 'required|string|min:8|confirmed',
        ]);

        try {
            // Connect to tenant DB
            DB::purge('tenant');
            Config::set('database.connections.tenant', $tenant->dbConfig());
            DB::setDefaultConnection('tenant');
            DB::connection('tenant')->getPdo();
        } catch (\Throwable $e) {
            DB::setDefaultConnection('pgsql');
            return back()->with('setup_error', 'DB Connection failed: ' . $e->getMessage())
                         ->with('active_tab', 'setup');
        }

        try {
            // Check if email already exists
            $exists = DB::connection('tenant')
                ->table('users')
                ->where('email', $validated['admin_email'])
                ->exists();

            if ($exists) {
                DB::setDefaultConnection('pgsql');
                DB::purge('tenant');
                return back()
                    ->with('setup_error', "An admin with email '{$validated['admin_email']}' already exists in this school's database.")
                    ->with('active_tab', 'setup');
            }

            DB::connection('tenant')->table('users')->insert([
                'full_name'      => $validated['admin_full_name'],
                'email'          => $validated['admin_email'],
                'password'       => Hash::make($validated['admin_password']),
                'role'           => 'admin',
                'is_super_admin' => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

        } catch (\Throwable $e) {
            DB::setDefaultConnection('pgsql');
            DB::purge('tenant');
            return back()
                ->with('setup_error', 'Failed to create admin: ' . $e->getMessage())
                ->with('active_tab', 'setup');
        }

        // Reset to central DB
        DB::setDefaultConnection('pgsql');
        DB::purge('tenant');

        AuditLog::record(
            'tenant.admin_created',
            "Super admin '{$validated['admin_email']}' created for '{$tenant->name}'",
            $tenant->id
        );

        return back()
            ->with('setup_success', "Super admin '{$validated['admin_email']}' created successfully for {$tenant->name}.")
            ->with('active_tab', 'setup');
    }

    /**
     * Reset an existing admin's password in the tenant DB.
     */
    public function resetAdminPassword(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'reset_email'    => 'required|email',
            'reset_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            DB::purge('tenant');
            Config::set('database.connections.tenant', $tenant->dbConfig());
            DB::setDefaultConnection('tenant');

            $updated = DB::connection('tenant')
                ->table('users')
                ->where('email', $validated['reset_email'])
                ->update([
                    'password'   => Hash::make($validated['reset_password']),
                    'updated_at' => now(),
                ]);

            DB::setDefaultConnection('pgsql');
            DB::purge('tenant');

            if ($updated === 0) {
                return back()
                    ->with('setup_error', "No user found with email '{$validated['reset_email']}' in this school's database.")
                    ->with('active_tab', 'setup');
            }

        } catch (\Throwable $e) {
            DB::setDefaultConnection('pgsql');
            DB::purge('tenant');
            return back()
                ->with('setup_error', 'Password reset failed: ' . $e->getMessage())
                ->with('active_tab', 'setup');
        }

        AuditLog::record(
            'tenant.admin_password_reset',
            "Password reset for '{$validated['reset_email']}' in '{$tenant->name}'",
            $tenant->id
        );

        return back()
            ->with('setup_success', "Password for '{$validated['reset_email']}' updated successfully.")
            ->with('active_tab', 'setup');
    }

    /**
     * List all admins in a tenant's database.
     */
    public function listAdmins(Tenant $tenant)
    {
        $admins = collect();

        try {
            DB::purge('tenant');
            Config::set('database.connections.tenant', $tenant->dbConfig());
            DB::setDefaultConnection('tenant');

            $admins = DB::connection('tenant')
                ->table('users')
                ->where('role', 'admin')
                ->orderByDesc('is_super_admin')
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'email', 'is_super_admin', 'created_at']);

            DB::setDefaultConnection('pgsql');
            DB::purge('tenant');

        } catch (\Throwable $e) {
            DB::setDefaultConnection('pgsql');
        }

        return response()->json($admins);
    }
}
