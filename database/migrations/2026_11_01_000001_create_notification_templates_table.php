<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();

            // Template identity
            $table->string('name');                          // "End of Term Reminder"
            $table->string('category')->default('general'); // general|fee|attendance|results|custom
            $table->string('title');                         // Default subject line
            $table->text('body');                            // Message body with {placeholders}

            // Metadata
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
