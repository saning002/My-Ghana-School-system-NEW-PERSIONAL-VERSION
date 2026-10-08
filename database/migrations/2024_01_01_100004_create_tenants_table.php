<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();

            // School identity
            $table->string('name');                          // School full name
            $table->string('slug')->unique();                // URL slug: accra-academy
            $table->string('subdomain')->unique();           // accra-academy.yourdomain.com
            $table->string('logo_path')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('admin_name');
            $table->string('admin_email');
            $table->string('admin_phone')->nullable();

            // Database credentials (encrypted)
            $table->string('db_host')->default('127.0.0.1');
            $table->string('db_port')->default('3306');
            $table->string('db_name');
            $table->string('db_username');
            $table->text('db_password');                    // stored encrypted

            // Plan & subscription
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['active', 'suspended', 'trial', 'expired'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();

            // Backup settings
            $table->enum('backup_frequency', ['daily', 'weekly', 'monthly', 'off'])->default('weekly');
            $table->timestamp('last_backup_at')->nullable();

            // Meta
            $table->text('notes')->nullable();              // Owner's internal notes
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
