<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds all fields from the Royal Fame International School
 * physical Student Admission Form that are not yet in the database.
 *
 * Sections covered:
 *  A – Pupil personal info extras (surname, other names, place of birth, religion, languages, class applying for)
 *  B – Previous school details
 *  C – Father's information
 *  D – Mother's information
 *  E – Guardian information
 *  G – Medical information
 *  H – Authorized pickup person
 *  I – School contribution status (fee checkboxes)
 *  J – Consent / declaration metadata
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Extra user-level fields ────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'surname')) {
                $table->string('surname')->nullable()->after('full_name');
            }
            if (! Schema::hasColumn('users', 'other_names')) {
                $table->string('other_names')->nullable()->after('surname');
            }
            if (! Schema::hasColumn('users', 'place_of_birth')) {
                $table->string('place_of_birth')->nullable()->after('date_of_birth');
            }
            if (! Schema::hasColumn('users', 'religion')) {
                $table->string('religion')->nullable()->after('place_of_birth');
            }
            if (! Schema::hasColumn('users', 'first_language')) {
                $table->string('first_language')->nullable()->after('religion');
            }
            if (! Schema::hasColumn('users', 'other_language')) {
                $table->string('other_language')->nullable()->after('first_language');
            }
        });

        // ── Student-level admission fields ────────────────────────────────────
        Schema::table('students', function (Blueprint $table) {
            // Section A extras
            if (! Schema::hasColumn('students', 'class_applying_for')) {
                $table->string('class_applying_for')->nullable()->after('program_id');
            }
            if (! Schema::hasColumn('students', 'passport_photo_attached')) {
                $table->boolean('passport_photo_attached')->default(false)->after('class_applying_for');
            }

            // Section B – Previous school
            if (! Schema::hasColumn('students', 'prev_school_name')) {
                $table->string('prev_school_name')->nullable();
            }
            if (! Schema::hasColumn('students', 'prev_school_address')) {
                $table->string('prev_school_address')->nullable();
            }
            if (! Schema::hasColumn('students', 'last_class_completed')) {
                $table->string('last_class_completed')->nullable();
            }
            if (! Schema::hasColumn('students', 'reason_for_leaving')) {
                $table->string('reason_for_leaving')->nullable();
            }
            if (! Schema::hasColumn('students', 'prev_reports_attached')) {
                $table->boolean('prev_reports_attached')->default(false);
            }
            if (! Schema::hasColumn('students', 'transfer_letter_attached')) {
                $table->boolean('transfer_letter_attached')->default(false);
            }

            // Section C – Father
            if (! Schema::hasColumn('students', 'father_name')) {
                $table->string('father_name')->nullable();
            }
            if (! Schema::hasColumn('students', 'father_occupation')) {
                $table->string('father_occupation')->nullable();
            }
            if (! Schema::hasColumn('students', 'father_employer')) {
                $table->string('father_employer')->nullable();
            }
            if (! Schema::hasColumn('students', 'father_address')) {
                $table->string('father_address')->nullable();
            }
            if (! Schema::hasColumn('students', 'father_phone')) {
                $table->string('father_phone', 30)->nullable();
            }

            // Section D – Mother
            if (! Schema::hasColumn('students', 'mother_name')) {
                $table->string('mother_name')->nullable();
            }
            if (! Schema::hasColumn('students', 'mother_occupation')) {
                $table->string('mother_occupation')->nullable();
            }
            if (! Schema::hasColumn('students', 'mother_employer')) {
                $table->string('mother_employer')->nullable();
            }
            if (! Schema::hasColumn('students', 'mother_address')) {
                $table->string('mother_address')->nullable();
            }
            if (! Schema::hasColumn('students', 'mother_phone')) {
                $table->string('mother_phone', 30)->nullable();
            }

            // Section E – Guardian
            if (! Schema::hasColumn('students', 'guardian_name')) {
                $table->string('guardian_name')->nullable();
            }
            if (! Schema::hasColumn('students', 'guardian_relationship')) {
                $table->string('guardian_relationship')->nullable();
            }
            if (! Schema::hasColumn('students', 'guardian_occupation')) {
                $table->string('guardian_occupation')->nullable();
            }
            if (! Schema::hasColumn('students', 'guardian_address')) {
                $table->string('guardian_address')->nullable();
            }
            if (! Schema::hasColumn('students', 'guardian_phone')) {
                $table->string('guardian_phone', 30)->nullable();
            }

            // Section G – Medical
            if (! Schema::hasColumn('students', 'medical_condition')) {
                $table->string('medical_condition')->nullable();
            }
            if (! Schema::hasColumn('students', 'allergies')) {
                $table->string('allergies')->nullable();
            }
            if (! Schema::hasColumn('students', 'blood_group')) {
                $table->string('blood_group', 10)->nullable();
            }
            if (! Schema::hasColumn('students', 'special_educational_needs')) {
                $table->text('special_educational_needs')->nullable();
            }
            if (! Schema::hasColumn('students', 'family_doctor')) {
                $table->string('family_doctor')->nullable();
            }
            if (! Schema::hasColumn('students', 'doctor_phone')) {
                $table->string('doctor_phone', 30)->nullable();
            }

            // Section H – Authorized pickup
            if (! Schema::hasColumn('students', 'pickup_name')) {
                $table->string('pickup_name')->nullable();
            }
            if (! Schema::hasColumn('students', 'pickup_relationship')) {
                $table->string('pickup_relationship')->nullable();
            }
            if (! Schema::hasColumn('students', 'pickup_phone')) {
                $table->string('pickup_phone', 30)->nullable();
            }

            // Section I – School contribution status
            if (! Schema::hasColumn('students', 'admission_fee_paid')) {
                $table->boolean('admission_fee_paid')->default(false);
            }
            if (! Schema::hasColumn('students', 'furniture_fee_paid')) {
                $table->boolean('furniture_fee_paid')->default(false);
            }
            if (! Schema::hasColumn('students', 'toiletries_paid')) {
                $table->boolean('toiletries_paid')->default(false);
            }
            if (! Schema::hasColumn('students', 'uniform_paid')) {
                $table->boolean('uniform_paid')->default(false);
            }

            // Section J – Consent
            if (! Schema::hasColumn('students', 'consent_signed')) {
                $table->boolean('consent_signed')->default(false);
            }
            if (! Schema::hasColumn('students', 'consent_guardian_name')) {
                $table->string('consent_guardian_name')->nullable();
            }
            if (! Schema::hasColumn('students', 'consent_date')) {
                $table->date('consent_date')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['surname','other_names','place_of_birth','religion','first_language','other_language'] as $col) {
                if (Schema::hasColumn('users', $col)) $table->dropColumn($col);
            }
        });

        Schema::table('students', function (Blueprint $table) {
            $cols = [
                'class_applying_for','passport_photo_attached',
                'prev_school_name','prev_school_address','last_class_completed',
                'reason_for_leaving','prev_reports_attached','transfer_letter_attached',
                'father_name','father_occupation','father_employer','father_address','father_phone',
                'mother_name','mother_occupation','mother_employer','mother_address','mother_phone',
                'guardian_name','guardian_relationship','guardian_occupation','guardian_address','guardian_phone',
                'medical_condition','allergies','blood_group','special_educational_needs','family_doctor','doctor_phone',
                'pickup_name','pickup_relationship','pickup_phone',
                'admission_fee_paid','furniture_fee_paid','toiletries_paid','uniform_paid',
                'consent_signed','consent_guardian_name','consent_date',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('students', $col)) $table->dropColumn($col);
            }
        });
    }
};
