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

        // Step 1: Connect
        try {
            DB::purge('tenant');
            Config::set('database.connections.tenant', $tenant->dbConfig());
            DB::setDefaultConnection('tenant');
            DB::connection('tenant')->getPdo();
            $results['connection'] = 'Connected to ' . $tenant->db_name;
        } catch (\Throwable $e) {
            DB::setDefaultConnection('pgsql');
            return back()->with('setup_error', 'DB Connection failed: ' . $e->getMessage())
                         ->with('active_tab', 'setup');
        }

        // Step 2: Kill any aborted transaction
        try {
            DB::connection('tenant')->statement('ROLLBACK');
        } catch (\Throwable $e) {}

        // Step 3: Create migrations tracking table manually (avoid artisan command issues)
        try {
            DB::connection('tenant')->statement('
                CREATE TABLE IF NOT EXISTS "migrations" (
                    "id" SERIAL PRIMARY KEY,
                    "migration" VARCHAR(255) NOT NULL,
                    "batch" INTEGER NOT NULL
                )
            ');
            $results['migrations_table'] = 'Migrations tracking table ready';
        } catch (\Throwable $e) {
            DB::connection('tenant')->statement('ROLLBACK');
        }

        // Step 4: Check what already exists in the DB
        $existingTables = [];
        try {
            $rows = DB::connection('tenant')
                ->select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
            $existingTables = array_column($rows, 'tablename');
        } catch (\Throwable $e) {}

        // Step 5: Selectively mark only already-existing-table migrations as run
        // Then let remaining migrations actually execute
        if (in_array('users', $existingTables)) {
            $results['detected'] = 'Existing tables detected — marking only completed migrations as run';

            // Map migration filenames to table names they create
            $migrationTableMap = [
                '0000_01_01_000000_create_users_table'            => ['users', 'sessions', 'password_reset_tokens'],
                '0000_01_01_000001_create_cache_table'            => ['cache'],
                '0000_01_01_000002_create_jobs_table'             => ['jobs'],
            ];

            $migrationFiles = glob(database_path('migrations/*.php'));
            $batch = 1;

            foreach ($migrationFiles as $file) {
                $name = pathinfo($file, PATHINFO_FILENAME);

                // Check if this migration's table already exists
                $shouldMark = false;

                if (isset($migrationTableMap[$name])) {
                    // Only mark if ALL tables for this migration exist
                    $tables = $migrationTableMap[$name];
                    $shouldMark = count(array_intersect($tables, $existingTables)) === count($tables);
                }
                // For all other migrations, check if their expected table exists
                // by looking for table name patterns in the migration filename
                else {
                    // Extract table name from migration filename (e.g. create_students_table -> students)
                    if (preg_match('/create_(\w+)_table/', $name, $m)) {
                        $shouldMark = in_array($m[1], $existingTables);
                    } elseif (preg_match('/add_\w+_to_(\w+)_table/', $name, $m)) {
                        // Column additions — only mark if the table exists AND the column exists
                        // For safety, we let these re-run (they use IF NOT EXISTS checks)
                        $shouldMark = false;
                    }
                }

                if ($shouldMark) {
                    try {
                        $exists = DB::connection('tenant')
                            ->table('migrations')
                            ->where('migration', $name)
                            ->exists();
                        if (!$exists) {
                            DB::connection('tenant')->table('migrations')->insert([
                                'migration' => $name,
                                'batch'     => $batch,
                            ]);
                        }
                    } catch (\Throwable $e) {
                        try { DB::connection('tenant')->statement('ROLLBACK'); } catch (\Throwable $e2) {}
                    }
                }
            }
        }

        // Step 6: Run any genuinely new migrations
        try {
            // Fresh rollback before migrate to clear any stuck state
            try { DB::connection('tenant')->statement('ROLLBACK'); } catch (\Throwable $e) {}

            Artisan::call('migrate', [
                '--force'    => true,
                '--database' => 'tenant',
            ]);
            $output = trim(Artisan::output());
            $results['migrations'] = $output ?: 'All migrations ran successfully';
        } catch (\Throwable $e) {
            try { DB::connection('tenant')->statement('ROLLBACK'); } catch (\Throwable $e2) {}
            // Try one more time
            try {
                Artisan::call('migrate', ['--force' => true, '--database' => 'tenant']);
                $results['migrations'] = trim(Artisan::output()) ?: 'Migrations completed on retry';
            } catch (\Throwable $e3) {
                $results['migrations_error'] = 'Migration error: ' . $e3->getMessage();
            }
        }

        // Step 7: Seed settings if table exists
        try {
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
                $results['seeder'] = 'Settings table not found — run again after creating tables manually';
            }
        } catch (\Throwable $e) {
            $results['seeder'] = 'Seeder note: ' . $e->getMessage();
        }

        // Reset to central DB
        DB::setDefaultConnection('pgsql');
        DB::purge('tenant');

        $message = implode(' | ', array_filter($results));

        AuditLog::record('tenant.migrations_run', "Migrations on '{$tenant->name}': " . $message, $tenant->id);

        return back()
            ->with('setup_success', $message)
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
