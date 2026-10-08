<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GeneratedDocument extends Model
{
    protected $fillable = [
        'uuid', 'document_type', 'title',
        'student_id', 'academic_period_id',
        'generated_by', 'generated_by_name',
        'status', 'meta',
    ];

    protected $casts = ['meta' => 'array'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($doc) {
            if (empty($doc->uuid)) {
                $doc->uuid = (string) Str::uuid();
            }
        });
    }

    public function student()   { return $this->belongsTo(Student::class); }
    public function period()    { return $this->belongsTo(AcademicPeriod::class, 'academic_period_id'); }

    public function getVerifyUrlAttribute(): string
    {
        return url('/verify/' . $this->uuid);
    }

    public function isValid(): bool { return $this->status === 'valid'; }

    public function revoke(): void { $this->update(['status' => 'revoked']); }
}
