<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'lecturer_id',
        'course_id',
        'program_id',
        'church_branch_id',
        'title',
        'academic_year',
        'term',
        'document_type',
        'file_path',
        'original_filename',
        'file_size',
        'notes',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'file_size'   => 'integer',
    ];

    public function lecturer()
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function churchBranch()
    {
        return $this->belongsTo(ChurchBranch::class);
    }

    /** Human-readable file size */
    public function getFileSizeLabelAttribute(): string
    {
        $bytes = $this->file_size ?? 0;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    /** Icon based on document type */
    public function getIconAttribute(): string
    {
        return $this->document_type === 'pdf'
            ? 'fa-file-pdf text-red-500'
            : 'fa-file-word text-blue-500';
    }
}
