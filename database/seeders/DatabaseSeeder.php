<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Only seeds essential defaults — no demo/test data.
        // Safe to run on production for any real school.
        $this->call(SettingsSeeder::class);
        $this->call(InitialAdminSeeder::class);

        // GhanaDemoSeeder is NOT called here.
        // To load demo data in development only, run manually:
        //   php artisan db:seed --class=GhanaDemoSeeder
    }
}
