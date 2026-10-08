<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('filename');                     // school_slug_2024-01-01_12-00-00.sql.gz
            $table->string('file_path');                    // storage path or cloud path
            $table->bigInteger('file_size')->nullable();    // bytes
            $table->enum('storage_type', ['local', 's3', 'gdrive'])->default('local');
            $table->enum('trigger', ['manual', 'scheduled', 'pre_restore'])->default('manual');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();      // if failed
            $table->boolean('cloud_synced')->default(false);
            $table->timestamp('cloud_synced_at')->nullable();
            $table->boolean('emailed')->default(false);
            $table->timestamp('emailed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
