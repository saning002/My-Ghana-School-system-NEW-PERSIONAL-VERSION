<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_session_id',
        'month',      // kept for backward compat — mirrors 'name'
        'name',
        'is_active',
        'sequence',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sequence'  => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function session()
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function examScores()
    {
        return $this->hasMany(ExamScore::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Human-readable label: "2024/2025 — Semester 1"
     */
    public function getFullLabelAttribute(): string
    {
        $sessionYear = $this->session?->year ?? '—';
        $periodName  = $this->name ?: $this->month ?: "Period {$this->id}";
        return "{$sessionYear} — {$periodName}";
    }

    /**
     * Activate this period and deactivate all others (system-wide, one active at a time).
     * Optionally scoped to a branch admin (future use — currently global).
     */
    public function activate(): void
    {
        // Deactivate all others first
        self::where('is_active', true)->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate this period.
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }
}
