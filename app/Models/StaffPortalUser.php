<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffPortalUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'staff_portal_users';

    protected $fillable = [
        'full_name', 'email', 'password', 'role',
        'phone', 'is_active', 'church_branch_id',
    ];

    protected $hidden = ['password', 'remember_token'];
    protected $casts  = ['password' => 'hashed', 'is_active' => 'boolean'];

    public static array $roleLabels = [
        'accountant'  => 'Accountant',
        'headmaster'  => 'Headmaster',
        'headteacher' => 'Headteacher',
        'deputy'      => 'Deputy Head',
        'secretary'   => 'Secretary',
    ];

    public static array $allPermissions = [
        // ── Student Reports ───────────────────────────────────────────────────
        'student_reports'        => 'View Student Reports (Conduct & Personality)',
        'student_reports_edit'   => 'Fill In / Edit Student Reports',

        // ── Dashboard ─────────────────────────────────────────────────────────
        'dashboard'              => 'Dashboard Overview',

        // ── Students ──────────────────────────────────────────────────────────
        'students'               => 'View Students',
        'students_create'        => 'Add / Register Students',
        'students_edit'          => 'Edit Student Records',
        'students_delete'        => 'Delete Students',
        'students_approve'       => 'Approve Students (remove NEW badge)',
        'students_promote'       => 'Promote / Demote Students',
        'students_import'        => 'Bulk Import Students',
        'students_id_card'       => 'View / Print Student ID Cards',

        // ── Attendance ────────────────────────────────────────────────────────
        'attendance'             => 'View Attendance Records',
        'attendance_mark'        => 'Mark / Record Attendance',
        'attendance_edit'        => 'Edit Attendance Records',
        'attendance_import'      => 'Import Attendance from Excel',
        'attendance_reports'     => 'Download Attendance Reports',

        // ── Exams & Scores ────────────────────────────────────────────────────
        'exams'                  => 'View Exam Scores & Report Cards',
        'exams_enter'            => 'Enter / Edit Exam Scores',
        'exams_report_card'      => 'Generate & Download Report Cards',
        'exams_bulk_download'    => 'Bulk Download Report Cards',
        'exams_import'           => 'Import Scores from Excel',

        // ── Exam Questions (Teacher Uploads) ──────────────────────────────────
        'exam_questions'         => 'View Exam Question Documents',
        'exam_questions_approve' => 'Approve / Flag Exam Documents',
        'exam_questions_download'=> 'Download Exam Documents',
        'exam_questions_delete'  => 'Delete Exam Documents',

        // ── Fees & Payments ───────────────────────────────────────────────────
        'fees'                   => 'View Fees & Payments',
        'fees_record'            => 'Record Payments',
        'fees_edit'              => 'Edit / Delete Payment Records',
        'fees_daily'             => 'Daily Fee Collection',
        'fees_exemptions'        => 'Manage Fee Exemptions',
        'fees_reports'           => 'Download Financial Reports',

        // ── Reports ───────────────────────────────────────────────────────────
        'reports'                => 'View All Reports',
        'reports_academic'       => 'Academic / Transcript Reports',
        'reports_attendance'     => 'Attendance Summary Reports',
        'reports_financial'      => 'Financial Reports',
        'reports_export'         => 'Export Reports (PDF / Excel)',

        // ── Programs & Courses ────────────────────────────────────────────────
        'programs'               => 'View Programs',
        'programs_manage'        => 'Create / Edit / Delete Programs',
        'courses'                => 'View Courses',
        'courses_manage'         => 'Create / Edit / Delete Courses',

        // ── Lecturers / Teachers ──────────────────────────────────────────────
        'lecturers'              => 'View Lecturers / Teachers',
        'lecturers_manage'       => 'Add / Edit / Delete Lecturers',
        'lecturers_assign'       => 'Assign Lecturers to Courses',
        'class_teachers'         => 'Manage Class Teacher Assignments',

        // ── Scheme of Learning ────────────────────────────────────────────────
        'scheme_of_learning'     => 'View Scheme of Learning',
        'scheme_manage'          => 'Create / Edit / Delete Scheme Entries',
        'scheme_upload_pdf'      => 'Upload Scheme of Learning as PDF',

        // ── Timetable & Calendar ──────────────────────────────────────────────
        'timetable'              => 'View Timetable',
        'timetable_manage'       => 'Create / Edit Timetable Entries',
        'calendar'               => 'View School Calendar / Events',
        'calendar_manage'        => 'Add / Edit School Events',

        // ── Notifications ─────────────────────────────────────────────────────
        'notifications'          => 'View Notifications',
        'notifications_send'     => 'Send Notifications to Students',

        // ── Branches ──────────────────────────────────────────────────────────
        'branches'               => 'View School Branches',
        'branches_manage'        => 'Create / Edit / Delete Branches',

        // ── Work Monitoring ───────────────────────────────────────────────────
        'work_logs'              => 'View Teacher Work Logs',
        'work_logs_manage'       => 'Edit / Manage Work Logs',
        'work_logs_comment'      => 'Add Comments / Feedback to Work Logs',

        // ── Daily Fees ────────────────────────────────────────────────────────
        'daily_fees'             => 'Daily Fees Overview',
        'daily_fees_manage'      => 'Record / Edit Daily Fee Entries',

        // ── Promotions ────────────────────────────────────────────────────────
        'promotions'             => 'View Promotion History',
        'promotions_manage'      => 'Bulk Promote / Repeat Students',

        // ── Academic Sessions ─────────────────────────────────────────────────
        'academic_sessions'      => 'View Academic Sessions & Periods',
        'academic_sessions_manage' => 'Create / Edit Academic Sessions',

        // ── Settings ──────────────────────────────────────────────────────────
        'settings'               => 'View Portal & School Settings',
        'settings_manage'        => 'Edit Portal Settings',
    ];

    /**
     * Permissions that are ON by default for ALL new staff accounts.
     * Admin can turn individual ones off after creation.
     */
    public static array $defaultPermissions = [
        'dashboard',
        'student_reports',
        'students',
        'attendance',
        'attendance_mark',
        'exams',
        'exams_report_card',
        'exam_questions',
        'exam_questions_approve',
        'fees',
        'programs',
        'courses',
        'lecturers',
        'scheme_of_learning',
        'timetable',
        'calendar',
        'notifications',
        'work_logs',
        'work_logs_comment',
        'daily_fees',
        'promotions',
        'academic_sessions',
    ];

    public function permissions()
    {
        return $this->hasMany(StaffPortalPermission::class);
    }

    public function can_access(string $permission): bool
    {
        return $this->permissions()->where('permission', $permission)->exists();
    }

    public function syncPermissions(array $permissions): void
    {
        $this->permissions()->delete();
        foreach ($permissions as $perm) {
            if (array_key_exists($perm, self::$allPermissions)) {
                $this->permissions()->create(['permission' => $perm]);
            }
        }
    }

    /**
     * Grant the default set of permissions to a newly created staff account.
     * Called automatically when no explicit permission list is provided.
     */
    public function grantDefaultPermissions(): void
    {
        $this->syncPermissions(self::$defaultPermissions);
    }

    public function getPermissionListAttribute(): array
    {
        // Use eager-loaded relation if available, otherwise query
        if ($this->relationLoaded('permissions')) {
            return $this->permissions->pluck('permission')->toArray();
        }
        return $this->permissions()->pluck('permission')->toArray();
    }

    public function getRoleLabelAttribute(): string
    {
        return self::$roleLabels[$this->role] ?? ucfirst($this->role);
    }

    public function isBranchAdmin(): bool
    {
        return !is_null($this->church_branch_id);
    }

    public function isSuperAdmin(): bool
    {
        return false;
    }

    public function isLecturer(): bool
    {
        return false;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return null;
    }
}
