<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * InitialAdminSeeder — Production Bootstrap
 * ==========================================
 * Creates the initial Super Admin and Admin accounts from environment variables.
 *
 * IDEMPOTENT: Running this multiple times is completely safe.
 *   - If the account already exists (matched by email) it is NEVER modified.
 *   - No passwords are ever reset.
 *   - No demo/fake data is created.
 *
 * HOW TO PROVISION A NEW INSTALLATION
 * ------------------------------------
 * 1. Create the production database (Render PostgreSQL or any PostgreSQL).
 * 2. Set the following environment variables on Render (or in your .env):
 *
 *      INITIAL_SUPER_ADMIN_NAME=Administrator
 *      INITIAL_SUPER_ADMIN_EMAIL=superadmin@yourschool.com
 *      INITIAL_SUPER_ADMIN_PASSWORD=ChangeMe@2024!
 *
 *      INITIAL_ADMIN_NAME=School Admin
 *      INITIAL_ADMIN_EMAIL=admin@yourschool.com
 *      INITIAL_ADMIN_PASSWORD=ChangeMe@2024!
 *
 * 3. Deploy the application — migrations run automatically via render-build.sh.
 * 4. Run this seeder (render-build.sh does this automatically):
 *
 *      php artisan db:seed --class=InitialAdminSeeder --force
 *
 * 5. Log in with the credentials you set in the environment variables.
 * 6. IMPORTANT: Change both passwords immediately from Settings after first login.
 * 7. After changing credentials in the app, the env vars are no longer used
 *    (the seeder skips existing accounts entirely).
 *
 * SECURITY NOTES
 * --------------
 * - Passwords are hashed before storage using Laravel's configured hasher (bcrypt).
 * - Passwords are never logged or returned in responses.
 * - Do NOT commit actual credentials to .env or any source file.
 * - After first login, credentials live only in the database — env vars are ignored.
 */
class InitialAdminSeeder extends Seeder
{
    public function run(): void
    {
        $this->provisionAccount(
            name:        env('INITIAL_SUPER_ADMIN_NAME', ''),
            email:       env('INITIAL_SUPER_ADMIN_EMAIL', ''),
            password:    env('INITIAL_SUPER_ADMIN_PASSWORD', ''),
            role:        'admin',
            isSuperAdmin: true,
            label:       'Super Admin',
        );

        $this->provisionAccount(
            name:        env('INITIAL_ADMIN_NAME', ''),
            email:       env('INITIAL_ADMIN_EMAIL', ''),
            password:    env('INITIAL_ADMIN_PASSWORD', ''),
            role:        'admin',
            isSuperAdmin: false,
            label:       'Admin',
        );
    }

    private function provisionAccount(
        string $name,
        string $email,
        string $password,
        string $role,
        bool   $isSuperAdmin,
        string $label,
    ): void {
        // Skip silently if env vars not configured
        if (empty($email) || empty($password) || empty($name)) {
            $this->command->warn(
                "[InitialAdminSeeder] {$label}: env vars not set — skipping. " .
                'Set INITIAL_' . strtoupper(str_replace(' ', '_', $label)) . '_EMAIL/PASSWORD/NAME to provision.'
            );
            return;
        }

        // Validate email format
        $validator = Validator::make(
            ['email' => $email],
            ['email' => 'required|email']
        );

        if ($validator->fails()) {
            $this->command->error(
                "[InitialAdminSeeder] {$label}: invalid email format '{$email}' — skipping."
            );
            return;
        }

        // Idempotency check — never modify an existing account
        if (User::where('email', $email)->exists()) {
            $this->command->info(
                "[InitialAdminSeeder] {$label} '{$email}' already exists — skipping (no changes made)."
            );
            return;
        }

        // Create the account — password hashed by Laravel's hashed cast on the model
        User::create([
            'full_name'      => $name,
            'email'          => $email,
            'password'       => Hash::make($password),
            'role'           => $role,
            'is_super_admin' => $isSuperAdmin,
        ]);

        // Never log the password — only confirm creation
        $this->command->info(
            "[InitialAdminSeeder] {$label} '{$email}' created successfully."
        );
    }
}
