<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_fee_exemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            // scholarship | waiver | partial | other
            $table->string('exemption_type', 40)->default('scholarship');
            // null = full exemption (pays 0); a value = fixed daily amount override
            $table->decimal('daily_override', 10, 2)->nullable();
            $table->string('reason', 300)->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();  // null = no expiry
            $table->unsignedBigInteger('granted_by');
            $table->timestamps();

            $table->unique('student_id'); // one active exemption per student
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fee_exemptions');
    }
};
