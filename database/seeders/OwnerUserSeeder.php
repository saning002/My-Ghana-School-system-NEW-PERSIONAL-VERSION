<?php

namespace Database\Seeders;

use App\Models\OwnerUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnerUserSeeder extends Seeder
{
    public function run(): void
    {
        OwnerUser::updateOrCreate(
            ['email' => env('OWNER_EMAIL', 'owner@schoolsystem.com')],
            [
                'name'     => env('OWNER_NAME', 'System Owner'),
                'password' => Hash::make(env('OWNER_PASSWORD', 'ChangeMe@2024!')),
            ]
        );

        $this->command->info('Owner user created. Email: ' . env('OWNER_EMAIL', 'owner@schoolsystem.com'));
        $this->command->warn('IMPORTANT: Change the owner password immediately after first login!');
    }
}
