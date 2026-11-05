<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_alert_settings', function (Blueprint $table) {
            $table->id();

            // One row per tenant (school)
            $table->boolean('enabled')->default(false);

            // Templates (raw text with {placeholders})
            $table->text('present_message')->nullable();   // sent when student is marked present
            $table->text('absent_message')->nullable();    // sent when student is marked absent

            // Who to SMS on the student record
            // options: guardian_phone | mother_phone | father_phone | both
            $table->string('recipient_field')->default('guardian_phone');

            // Delay in minutes after register is submitted (0 = immediate)
            $table->unsignedSmallInteger('send_delay_minutes')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_alert_settings');
    }
};
