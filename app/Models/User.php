<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Dynamically resolve the DB connection.
     * Uses the tenant connection if TenantMiddleware has already configured it.
     * This ensures Auth::user() always loads from the correct school DB.
     * No extra DB queries — just checks the current config state.
     */
    public function getConnectionName()
    {
        // If TenantMiddleware already switched to tenant DB, use it
        $default = \Illuminate\Support\Facades\DB::getDefaultConnection();
        if ($default === 'tenant') {
            return 'tenant';
        }

        // Check if tenant connection is already configured (has a database set)
        $tenantDb = config('database.connections.tenant.database', '');
        if (!empty($tenantDb) && session()->has('tenant_slug')) {
            return 'tenant';
        }

        // Session has tenant_slug but tenant connection not configured yet.
        // This happens when Auth::user() is called before TenantMiddleware runs.
        // Configure the connection on the fly — but only ONCE per request
        // by caching the result in the app container to avoid repeated DB queries.
        if (session()->has('tenant_slug')) {
            try {
                // Use app container as a per-request cache
                if (!app()->bound('_tenant_conn_configured')) {
                    $slug   = session('tenant_slug');
                    $tenant = \App\Models\Tenant::on('pgsql')->where('slug', $slug)->first();
                    if ($tenant) {
                        \Illuminate\Support\Facades\DB::purge('tenant');
                        \Illuminate\Support\Facades\Config::set(
                            'database.connections.tenant',
                            $tenant->dbConfig()
                        );
                        app()->instance('_tenant_conn_configured', true);
                        app()->instance('tenant', $tenant);
                    }
                }
                // Check again after potentially configuring it
                $tenantDb = config('database.connections.tenant.database', '');
                if (!empty($tenantDb)) {
                    return 'tenant';
                }
            } catch (\Throwable $e) {
                // Fall through to default
            }
        }

        return $this->connection ?? config('database.default');
    }

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
