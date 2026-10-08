<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'surname',
        'other_names',
        'email',
        'password',
        'date_of_birth',
        'place_of_birth',
        'religion',
        'first_language',
        'other_language',
        'phone',
        'address',
        'photo',
        'qualification',
        'bio',
        'gender',
        'marital_status',
        'nationality',
        'role',
        'is_super_admin',
        'church_branch_id',
        'login_background_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'is_super_admin' => 'boolean',
        ];
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo) return null;
        if (filter_var($this->photo, FILTER_VALIDATE_URL)) return $this->photo;
        $path = ltrim(str_replace('\\', '/', $this->photo), '/');
        foreach (['storage/', 'public/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $path = ltrim(substr($path, strlen($prefix)), '/');
                break;
            }
        }
        return '/storage/' . $path;
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function courseAssignments()
    {
        return $this->hasMany(CourseAssignment::class, 'lecturer_id');
    }

    public function branch()
    {
        return $this->belongsTo(ChurchBranch::class, 'church_branch_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true;
    }

    public function isBranchAdmin(): bool
    {
        // A branch admin is an admin who has a specific branch assigned.
        // An admin with NO branch set should see everything (like a super admin).
        return $this->role === 'admin'
            && ! $this->is_super_admin
            && ! empty($this->church_branch_id);
    }

    public function isLecturer(): bool
    {
        return $this->role === 'lecturer';
    }

    /**
     * Returns a Student query scoped to this lecturer's branch AND assigned programs.
     * Teachers ONLY see students from their own branch who are in a program they teach.
     *
     * Usage: $this->user->lecturerStudentQuery()->get()
     *        or pass $programId to scope to a specific program
     */
    public function lecturerStudentQuery(?int $programId = null): \Illuminate\Database\Eloquent\Builder
    {
        $assignedProgramIds = \App\Models\CourseAssignment::where('lecturer_id', $this->id)
            ->pluck('program_id')
            ->unique()
            ->toArray();

        // Class teacher's program too
        $classProgram = \App\Models\ClassTeacherAssignment::where('lecturer_id', $this->id)->value('program_id');
        if ($classProgram && ! in_array($classProgram, $assignedProgramIds)) {
            $assignedProgramIds[] = $classProgram;
        }

        $q = \App\Models\Student::query();

        // Always filter by branch if lecturer has one
        if (! empty($this->church_branch_id)) {
            $q->where('church_branch_id', $this->church_branch_id);
        }

        // Always filter to only programs the lecturer teaches
        if ($programId) {
            // Verify this program is one the lecturer is assigned to
            if (! empty($assignedProgramIds) && ! in_array($programId, $assignedProgramIds)) {
                // Return empty query — lecturer not assigned to this program
                $q->whereRaw('1 = 0');
            } else {
                $q->where('program_id', $programId);
            }
        } elseif (! empty($assignedProgramIds)) {
            $q->whereIn('program_id', $assignedProgramIds);
        }

        return $q;
    }
}
