<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('website_admission_applications', function (Blueprint $table) {
            $table->id();
            $table->string('child_first_name', 100);
            $table->string('child_last_name', 100);
            $table->date('child_dob');
            $table->string('child_gender', 1);        // M | F
            $table->string('program_applying', 30);   // daycare | nursery | preschool | kindergarten
            $table->string('parent_name', 120);
            $table->string('parent_email');
            $table->string('parent_phone', 30);
            $table->string('relationship', 60);
            $table->text('address');
            $table->string('previous_school', 200)->nullable();
            $table->text('special_needs')->nullable();
            $table->string('how_did_you_hear', 200)->nullable();
            $table->string('status', 20)->default('pending'); // pending | reviewing | accepted | rejected
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('website_admission_applications'); }
};
