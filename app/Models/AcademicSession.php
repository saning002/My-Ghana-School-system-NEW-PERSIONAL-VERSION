<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicSession extends Model
{
    use HasFactory;

    protected $fillable = ['year', 'mode'];

    protected $casts = ['mode' => 'string'];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function periods()
    {
        return $this->hasMany(AcademicPeriod::class)->orderBy('sequence');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Return the period names for this session's mode.
     *  semester → ['Semester 1', 'Semester 2']
     *  term     → ['Term 1', 'Term 2', 'Term 3']
     */
    public function periodNames(): array
    {
        return match ($this->mode) {
            'term'  => ['Term 1', 'Term 2', 'Term 3'],
            default => ['Semester 1', 'Semester 2'],
        };
    }

    /**
     * Auto-create the correct periods for this session's mode.
     * Safe to call multiple times — skips if periods already exist.
     */
    public function generatePeriods(): void
    {
        if ($this->periods()->count() > 0) {
            return; // already generated
        }

        foreach ($this->periodNames() as $i => $name) {
            AcademicPeriod::create([
                'academic_session_id' => $this->id,
                'month'               => $name,   // keep month in sync for backward compat
                'name'                => $name,
                'sequence'            => $i + 1,
                'is_active'           => false,
            ]);
        }
    }

    /**
     * Get the currently active period across ALL sessions (system-wide).
     */
    public static function activePeriod(): ?AcademicPeriod
    {
        return AcademicPeriod::where('is_active', true)->first();
    }
}
