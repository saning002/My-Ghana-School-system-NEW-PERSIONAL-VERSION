<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default: portal is CLOSED by default
        \Illuminate\Support\Facades\DB::table('settings')->insert([
            ['key' => 'portal_access', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'portal_message', 'value' => 'The student portal is currently closed. Please check back later or contact the administration.', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
