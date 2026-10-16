<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * fee_reminders — log every reminder sent to a student/parent.
 * Run against TENANT DB.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('academic_session_id')->nullable();
            $table->enum('channel', ['email', 'sms', 'whatsapp']);
            $table->string('recipient');              // email address or phone number
            $table->string('recipient_name')->nullable();
            $table->text('message');                  // exact message sent
            $table->decimal('amount_owed', 10, 2)->default(0);
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->string('sent_by')->nullable();    // admin name who sent
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_reminders');
    }
};
