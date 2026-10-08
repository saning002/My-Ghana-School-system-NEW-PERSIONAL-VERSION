<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Setting;

class ExamScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'course_id', 'program_id', 'attempt',
        'academic_period_id',
        'quiz_score', 'sba_score', 'exam_score',
        // SBA sub-score components
        'test1_score', 'groupwork_score', 'test2_score', 'project_score',
    ];

    public function academicPeriod()
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    protected $casts = [
        'attempt'         => 'integer',
        'quiz_score'      => 'decimal:2',
        'sba_score'       => 'decimal:2',
        'exam_score'      => 'decimal:2',
        'test1_score'     => 'decimal:2',
        'groupwork_score' => 'decimal:2',
        'test2_score'     => 'decimal:2',
        'project_score'   => 'decimal:2',
    ];

    /**
     * Returns true if any of the 4 SBA sub-scores have been entered.
     */
    public function hasSubScores(): bool
    {
        return $this->test1_score !== null
            || $this->groupwork_score !== null
            || $this->test2_score !== null
            || $this->project_score !== null;
    }

    /**
     * Compute the weighted SBA total from the 4 sub-components.
     *
     * Each sub-component weight is stored in settings (0-100).
     * The sub-components are scored out of their respective weights,
     * so the raw sub-score IS the weighted contribution.
     *
     * Final formula per component: (raw / maxForComponent) × weight
     * where maxForComponent = weight (so it simplifies to just: raw)
     * because each component is already entered out of its weight.
     *
     * e.g. Test 1 weight=20, student scores 16 → contributes 16/20×20 = 16
     *
     * The sum of all 4 contributions = the SBA total (out of sum of weights).
     * This total is then used as the "SBA score" in the weighting formula.
     */
    public static function computeSbaFromSubScores(
        ?float $test1,
        ?float $groupwork,
        ?float $test2,
        ?float $project
    ): float {
        // Just sum the raw sub-scores; each is already entered out of its weight
        return (float)($test1 ?? 0)
             + (float)($groupwork ?? 0)
             + (float)($test2 ?? 0)
             + (float)($project ?? 0);
    }

    /**
     * Get the effective SBA/CA score.
     * Prefers computed sub-scores if any are present, else falls back to
     * sba_score → quiz_score.
     */
    public function getEffectiveSbaScore(): float
    {
        if ($this->hasSubScores()) {
            return self::computeSbaFromSubScores(
                $this->test1_score,
                $this->groupwork_score,
                $this->test2_score,
                $this->project_score
            );
        }
        return $this->sba_score !== null ? (float)$this->sba_score : (float)($this->quiz_score ?? 0);
    }

    public function getQuizScoreAttribute($value)
    {
        if ($value !== null && $value !== '') {
            return (float) $value;
        }
        return isset($this->attributes['sba_score']) && $this->attributes['sba_score'] !== null
            ? (float) $this->attributes['sba_score']
            : null;
    }

    public function getCaScoreAttribute(): ?float
    {
        return $this->sba_score !== null ? (float) $this->sba_score : $this->quiz_score;
    }

    public function student() { return $this->belongsTo(Student::class); }
    public function course()  { return $this->belongsTo(Course::class); }
    public function program() { return $this->belongsTo(Program::class); }
}
