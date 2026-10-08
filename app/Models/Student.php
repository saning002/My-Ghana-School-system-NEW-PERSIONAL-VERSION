<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    /**
     * Statuses that exclude a student from all exam-related listings,
     * templates, rankings, and report card generation.
     */
    public const EXAM_EXCLUDED_STATUSES = ['suspended', 'withdrawn', 'manifestation'];

    /**
     * Scope: only students who are eligible to appear in exams.
     * Excludes suspended, withdrawn, and manifestation students.
     */
    public function scopeForExams($query)
    {
        return $query->whereNotIn('status', self::EXAM_EXCLUDED_STATUSES);
    }

    protected $fillable = [
        'user_id',
        'student_id',
        'admission_date',
        'program_id',
        'class_applying_for',
        'passport_photo_attached',
        'status',
        'is_approved',
        'church_branch_id',
        'photo',
        'background_photo',
        'signal',
        'position',
        'date_issued',
        'qualifications',
        'native_town',
        'enrollment_year',
        'study_mode',
        'profession',
        'password',
        // Section B – Previous school
        'prev_school_name',
        'prev_school_address',
        'last_class_completed',
        'reason_for_leaving',
        'prev_reports_attached',
        'transfer_letter_attached',
        // Section C – Father
        'father_name',
        'father_occupation',
        'father_employer',
        'father_address',
        'father_phone',
        // Section D – Mother
        'mother_name',
        'mother_occupation',
        'mother_employer',
        'mother_address',
        'mother_phone',
        // Section E – Guardian
        'guardian_name',
        'guardian_relationship',
        'guardian_occupation',
        'guardian_address',
        'guardian_phone',
        // Section G – Medical
        'medical_condition',
        'allergies',
        'blood_group',
        'special_educational_needs',
        'family_doctor',
        'doctor_phone',
        // Section H – Authorized pickup
        'pickup_name',
        'pickup_relationship',
        'pickup_phone',
        // Section I – School contribution
        'admission_fee_paid',
        'furniture_fee_paid',
        'toiletries_paid',
        'uniform_paid',
        // Section J – Consent
        'consent_signed',
        'consent_guardian_name',
        'consent_date',
    ];

    protected $casts = [
        'admission_date'          => 'date',
        'date_issued'             => 'date',
        'consent_date'            => 'date',
        'password'                => 'hashed',
        'is_approved'             => 'boolean',
        'passport_photo_attached' => 'boolean',
        'prev_reports_attached'   => 'boolean',
        'transfer_letter_attached'=> 'boolean',
        'admission_fee_paid'      => 'boolean',
        'furniture_fee_paid'      => 'boolean',
        'toiletries_paid'         => 'boolean',
        'uniform_paid'            => 'boolean',
        'consent_signed'          => 'boolean',
    ];

    protected $appends = ['photo_url', 'background_photo_url'];

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo) {
            return null;
        }
        // Return a root-relative /storage/... path so views can detect it as
        // a local file and use the appropriate serving route (admin or portal).
        // The actual file is on the persistent disk; the symlink is ensured by
        // the entrypoint and .htaccess now has +FollowSymLinks.
        return $this->resolveMediaUrl($this->photo);
    }

    public function getBackgroundPhotoUrlAttribute(): ?string
    {
        if (! $this->background_photo) {
            return null;
        }
        return $this->resolveMediaUrl($this->background_photo);
    }

    private function resolveMediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $path = str_replace('\\', '/', trim($path));

        // Already a full URL — return as-is
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Normalise to a disk-relative key (strip known prefixes)
        $diskKey = $path;
        foreach (['/storage/', 'storage/', 'public/'] as $prefix) {
            if (str_starts_with($diskKey, $prefix)) {
                $diskKey = ltrim(substr($diskKey, strlen($prefix)), '/');
                break;
            }
        }

        // If the path starts with uploads/ it was stored in public/uploads
        if (str_starts_with($diskKey, 'uploads/')) {
            return '/' . $diskKey;
        }

        // Primary: return /storage/... root-relative URL.
        // The public/storage symlink (ensured by entrypoint + .htaccess FollowSymLinks)
        // maps this to storage/app/public/ on the persistent disk.
        return '/storage/' . ltrim($diskKey, '/');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function churchBranch()
    {
        return $this->belongsTo(ChurchBranch::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function examScores()
    {
        return $this->hasMany(ExamScore::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function customFee()
    {
        return $this->hasOne(StudentFee::class);
    }

    public function promotionHistory()
    {
        return $this->hasMany(PromotionHistory::class);
    }

    public function feeExemption()
    {
        return $this->hasOne(StudentFeeExemption::class);
    }

    public function dailyFeeRecords()
    {
        return $this->hasMany(DailyFeeRecord::class);
    }
}
