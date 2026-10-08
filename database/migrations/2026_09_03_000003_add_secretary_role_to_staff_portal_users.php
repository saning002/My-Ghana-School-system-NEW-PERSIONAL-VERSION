<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL requires USING clause when converting from a custom enum type to varchar.
        DB::statement("ALTER TABLE staff_portal_users ALTER COLUMN role TYPE VARCHAR(50) USING role::VARCHAR(50)");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE staff_portal_users ALTER COLUMN role TYPE VARCHAR(50) USING role::VARCHAR(50)");
    }
};
