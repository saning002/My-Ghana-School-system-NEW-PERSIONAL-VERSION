# Design Document: KMC Enterprise Upgrade

## Overview

This document describes the technical design for upgrading the Kingdom Ministerial University College (KMC) management system from its current foundational state into a premium enterprise-level platform. The upgrade is additive — it extends the existing Laravel 11 / PHP 8.2 / MySQL stack without replacing working modules.

The core additions are:

- A gold/yellow premium UI theme replacing the current indigo/dark theme
- Database schema extensions for program ordering, fees, exam scores, and promotion history
- Three service classes encapsulating business logic (PromotionService, FeeCalculationService, ReportCardService)
- New controller modules for exams, fees, promotion, and expanded reports
- PDF and Excel export via DomPDF and Maatwebsite Excel
- A branded report card engine with grading, ranking, and aggregate computation

### Key Design Principles

1. **Slim controllers** — all business logic lives in service classes; controllers delegate and return responses within 20 lines.
2. **Additive migrations** — new migrations modify existing tables rather than recreating them, preserving live data.
3. **CDN-only assets** — Tailwind CSS, Alpine.js, and Font Awesome are loaded from CDN; no local build step.
4. **Service container binding** — all three service classes are bound in `AppServiceProvider` and injected via constructor.
5. **Named routes** — all new routes follow the `admin.{resource}.{action}` convention.

---

## Architecture

The system follows a standard Laravel MVC architecture extended with a Service Layer:

```
HTTP Request
    │
    ▼
Route (web.php) ──► Middleware (EnsureUserHasRole: admin)
    │
    ▼
Controller (slim, ≤20 lines)
    │
    ├──► Form Request (validation)
    │
    ├──► Service Class (business logic)
    │       ├── PromotionService
    │       ├── FeeCalculationService
    │       └── ReportCardService
    │
    ├──► Eloquent Model (data access)
    │
    └──► View / PDF / Excel Response
```

### Package Dependencies (new)

Two Composer packages are added:

| Package | Version | Purpose |
|---|---|---|
| `barryvdh/laravel-dompdf` | `^3.0` | PDF generation for report cards and reports |
| `maatwebsite/excel` | `^3.1` | Excel export for all report types |

Install command:
```bash
composer require barryvdh/laravel-dompdf:^3.0 maatwebsite/excel:^3.1
```

---

## Components and Interfaces

### Service Classes

#### `app/Services/PromotionService.php`

```php
/**
 * Promotes a student to the next program in sequence.
 *
 * @param  Student $student  The student to promote.
 * @return void
 * @throws \InvalidArgumentException  If the student's status is not 'active'.
 */
public function promote(Student $student): void
```

Logic:
1. Guard: throw `InvalidArgumentException` if `$student->status !== 'active'`.
2. Find the next program: `Program::where('sequence', '>', $student->program->sequence)->orderBy('sequence')->first()`.
3. If next program exists: update `$student->program_id` to next program's id, set `status = 'active'`, record `PromotionHistory`.
4. If no next program: set `$student->status = 'graduated'`, record `PromotionHistory` with `to_program_id = null`.
5. Wrap steps 3–4 in `DB::transaction`.

#### `app/Services/FeeCalculationService.php`

```php
/**
 * Calculates the fee summary for a student.
 *
 * @param  Student $student
 * @return array{total: float, paid: float, balance: float}
 */
public function calculate(Student $student): array
```

Logic:
1. `$total` = sum of `ProgramFee::where('program_id', $programId)->value('amount') ?? 0` for each distinct program the student has been enrolled in (via `enrollments` or `promotion_history`).
2. `$paid` = `Payment::where('student_id', $student->id)->sum('amount_paid')`.
3. `$balance` = `$total - $paid`.
4. Return `['total' => $total, 'paid' => $paid, 'balance' => $balance]`.

#### `app/Services/ReportCardService.php`

```php
/**
 * Generates report card data for a student in a given program.
 *
 * @param  Student $student
 * @param  Program $program
 * @return array{
 *   student: Student,
 *   program: Program,
 *   courses: Collection,
 *   overall_aggregate: float,
 *   overall_average: float,
 *   overall_grade: string,
 *   remarks: string,
 *   has_scores: bool
 * }
 */
public function generate(Student $student, Program $program): array
```

Logic:
1. Load all courses for the program.
2. For each course, load the student's `ExamScore` (if any).
3. Compute `course_aggregate = quiz_score + exam_score`.
4. Compute `course_position`: rank among all students in same course+program by aggregate (ties share rank, using `DENSE_RANK` equivalent in PHP).
5. Compute `percentage_average = course_aggregate` (max is 100).
6. Assign `grade` via `GradingScale::assign($percentage_average)`.
7. Compute `overall_aggregate = sum(course_aggregates)`.
8. Compute `overall_average = overall_aggregate / count(courses)`.
9. Assign `overall_grade` via `GradingScale::assign($overall_average)`.
10. Set `remarks = overall_average >= 40 ? 'PASSED' : 'FAILED'`.
11. Return the full array.

#### `app/Services/GradingScale.php` (helper, static)

```php
public static function assign(float $score): string
// Returns: A1 (80-100), A2 (70-79), A3 (60-69), B1 (50-59), B2 (40-49), F (0-39)
```

### Controllers

All controllers are in `app/Http/Controllers/Admin/` and extend `App\Http\Controllers\Controller`.

#### `Admin\ExamController`

| Method | Route | Description |
|---|---|---|
| `index` | GET `/admin/exams` | List programs for score entry selection |
| `create` | GET `/admin/exams/create` | Score entry form (program + student selection) |
| `store` | POST `/admin/exams` | Save exam scores via `ExamScore::updateOrCreate` |
| `show` | GET `/admin/exams/{student}/{program}` | Report card preview |

#### `Admin\FeesController`

| Method | Route | Description |
|---|---|---|
| `index` | GET `/admin/fees` | Program fee config + student payment history |
| `store` | POST `/admin/fees/payments` | Record a new payment |
| `destroy` | DELETE `/admin/fees/payments/{payment}` | Delete a payment record |

#### `Admin\PromotionController`

| Method | Route | Description |
|---|---|---|
| `promote` | POST `/admin/students/{student}/promote` | Trigger promotion via PromotionService |

#### `Admin\ReportController` (extended)

| Method | Route | Description |
|---|---|---|
| `index` | GET `/admin/reports` | Reports hub |
| `students` | GET `/admin/reports/students` | Student report with filters |
| `attendance` | GET `/admin/reports/attendance` | Attendance report with filters |
| `financial` | GET `/admin/reports/financial` | Financial report |
| `programs` | GET `/admin/reports/programs` | Program performance report |
| `courses` | GET `/admin/reports/courses` | Course enrollment report |

### Form Requests

| Class | Validates |
|---|---|
| `StoreExamScoreRequest` | `student_id`, `program_id`, `scores` array with `quiz_score`/`exam_score` per course |
| `StorePaymentRequest` | `student_id`, `amount_paid` (> 0), `payment_date`, `notes` (nullable) |
| `StoreProgramFeeRequest` | `program_id`, `amount` (>= 0, decimal) |
| `PromoteStudentRequest` | `student_id` (exists, status = active) |

### Views

| View | Description |
|---|---|
| `layouts/app.blade.php` | Gold theme layout — replaces current indigo theme |
| `admin/dashboard.blade.php` | Stat cards + fees summary |
| `admin/exams/index.blade.php` | Program selection for score entry |
| `admin/exams/create.blade.php` | Score entry table (one row per course) |
| `admin/exams/show.blade.php` | Report card preview with PDF/Excel/Print actions |
| `admin/fees/index.blade.php` | Program fee config + payment recording |
| `admin/reports/financial.blade.php` | Financial report |
| `admin/reports/courses.blade.php` | Course enrollment report |
| `resources/views/pdf/report_card.blade.php` | DomPDF-rendered report card template |

---

## Data Models

### Schema Changes (new migrations)

**Migration 1: `alter_students_table_enterprise_upgrade`**
```sql
ALTER TABLE students DROP COLUMN level;
ALTER TABLE students MODIFY COLUMN status ENUM('active','graduated','suspended','manifestation') DEFAULT 'active';
```

**Migration 2: `add_sequence_to_programs_table`**
```sql
ALTER TABLE programs ADD COLUMN sequence INT NOT NULL DEFAULT 0;
```

**Migration 3: `create_program_fees_table`**
```sql
CREATE TABLE program_fees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    program_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
);
```

**Migration 4: `create_payments_table`**
```sql
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);
```

**Migration 5: `create_exam_scores_table`**
```sql
CREATE TABLE exam_scores (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NOT NULL,
    program_id BIGINT UNSIGNED NOT NULL,
    quiz_score DECIMAL(5,2) NULL,
    exam_score DECIMAL(5,2) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
    UNIQUE KEY uq_exam_scores (student_id, course_id, program_id)
);
```

**Migration 6: `create_promotion_history_table`**
```sql
CREATE TABLE promotion_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    from_program_id BIGINT UNSIGNED NOT NULL,
    to_program_id BIGINT UNSIGNED NULL,
    promoted_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (from_program_id) REFERENCES programs(id) ON DELETE CASCADE,
    FOREIGN KEY (to_program_id) REFERENCES programs(id) ON DELETE SET NULL
);
```

### Eloquent Models

#### `Student` (updated)

```php
protected $fillable = [
    'user_id', 'student_id', 'admission_date',
    'program_id', 'status', 'church_branch_id',
    // 'level' removed
];

// Relationships
public function user(): BelongsTo
public function program(): BelongsTo
public function churchBranch(): BelongsTo
public function enrollments(): HasMany
public function attendances(): HasMany
public function examScores(): HasMany        // new
public function payments(): HasMany          // new
public function promotionHistory(): HasMany  // new
```

#### `Program` (updated)

```php
protected $fillable = ['name', 'duration', 'requirements', 'sequence'];

public function students(): HasMany
public function courses(): HasMany
public function enrollments(): HasMany
public function programFee(): HasOne         // new
public function examScores(): HasMany        // new
```

#### `ProgramFee` (new)

```php
protected $fillable = ['program_id', 'amount'];

public function program(): BelongsTo
```

#### `Payment` (new)

```php
protected $fillable = ['student_id', 'amount_paid', 'payment_date', 'notes'];

protected $casts = ['payment_date' => 'date'];

public function student(): BelongsTo
```

#### `ExamScore` (new)

```php
protected $fillable = [
    'student_id', 'course_id', 'program_id',
    'quiz_score', 'exam_score',
];

public function student(): BelongsTo
public function course(): BelongsTo
public function program(): BelongsTo
```

#### `PromotionHistory` (new)

```php
protected $fillable = [
    'student_id', 'from_program_id', 'to_program_id', 'promoted_at',
];

protected $casts = ['promoted_at' => 'datetime'];

public function student(): BelongsTo
public function fromProgram(): BelongsTo
public function toProgram(): BelongsTo
```

### Grading Scale

The grading scale is a pure static mapping used by `GradingScale::assign()`:

| Score Range | Grade |
|---|---|
| 80 – 100 | A1 |
| 70 – 79 | A2 |
| 60 – 69 | A3 |
| 50 – 59 | B1 |
| 40 – 49 | B2 |
| 0 – 39 | F |

---

## UI/UX Design

### Theme Configuration

The existing indigo/dark theme is replaced with a gold/yellow premium theme. The Tailwind config in `layouts/app.blade.php` is updated:

```javascript
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: {
                    50:  '#fefce8',
                    100: '#fef9c3',
                    200: '#fef08a',
                    300: '#fde047',
                    400: '#facc15',
                    500: '#D4A017',  // KMC gold — primary action color
                    600: '#b8860b',
                    700: '#92680a',
                    800: '#78520a',
                    900: '#5c3d08',
                },
                surface: '#F5F0E8',  // warm off-white background
                ink:     '#1A1A1A',  // near-black text
            },
            fontFamily: { sans: ['Inter', 'sans-serif'] },
        }
    }
}
```

### Layout Structure

```
┌─────────────────────────────────────────────────────────┐
│  TOP NAV (fixed, z-30)                                  │
│  [☰ hamburger mobile] [Page Title] [User] [Logout]      │
├──────────────┬──────────────────────────────────────────┤
│              │                                          │
│  SIDEBAR     │  MAIN CONTENT                            │
│  (fixed,     │  (scrollable)                            │
│   w-64,      │                                          │
│   gold       │  @yield('content')                       │
│   gradient)  │                                          │
│              │                                          │
└──────────────┴──────────────────────────────────────────┘
│  BOTTOM NAV (mobile only, fixed bottom)                 │
└─────────────────────────────────────────────────────────┘
```

Sidebar background: `linear-gradient(180deg, #78520a 0%, #92680a 50%, #D4A017 100%)`.

### Report Card Design

The report card uses a print-optimised layout:

```
┌─────────────────────────────────────────────────────────┐
│  [LOGO]  KINGDOM MINISTERIAL UNIVERSITY COLLEGE         │  ← #D4A017 bg, black text
│          KINGDOM FELLOWSHIP MINISTRY (...)              │
├─────────────────────────────────────────────────────────┤
│  Student: ___________   Program: ___________            │
├──────────┬───────┬────────┬──────────┬────────┬────────┤
│  Course  │ Quiz  │ Scores │ Agg.     │ Pos.   │ %Avg   │ Grade │
├──────────┼───────┼────────┼──────────┼────────┼────────┤
│  ...     │  ...  │  ...   │  ...     │  ...   │  ...   │  ...  │
├──────────┴───────┴────────┴──────────┴────────┴────────┤
│  GRADING SCALE (bottom-left)  │  FOOTER TOTALS (right) │
│  A1=80-100, A2=70-79, ...     │  Total / Agg / Avg     │
│                               │  Grade / Remarks       │
└─────────────────────────────────────────────────────────┘
```

PDF filename: `report_card_{student_id}_{program_slug}.pdf`
Excel filename: `report_card_{student_id}_{program_slug}.xlsx`

### Toast Notification System

Toasts are rendered in the layout using Alpine.js and session flash data:

```html
<!-- Success toast -->
<div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,4000)"
     class="fixed top-4 right-4 z-50 bg-amber-500 text-white px-5 py-3 rounded-xl shadow-lg">
    {{ session('success') }}
</div>
```

---

## Routes

All new routes are added to the existing `admin` middleware group in `routes/web.php`:

```php
// Exams & Report Cards
Route::get('/exams',                          [ExamController::class, 'index'])->name('exams.index');
Route::get('/exams/create',                   [ExamController::class, 'create'])->name('exams.create');
Route::post('/exams',                         [ExamController::class, 'store'])->name('exams.store');
Route::get('/exams/{student}/{program}',      [ExamController::class, 'show'])->name('exams.show');
Route::get('/exams/{student}/{program}/pdf',  [ExamController::class, 'pdf'])->name('exams.pdf');
Route::get('/exams/{student}/{program}/excel',[ExamController::class, 'excel'])->name('exams.excel');

// Fees
Route::get('/fees',                           [FeesController::class, 'index'])->name('fees.index');
Route::post('/fees/payments',                 [FeesController::class, 'store'])->name('fees.store');
Route::delete('/fees/payments/{payment}',     [FeesController::class, 'destroy'])->name('fees.destroy');

// Promotion
Route::post('/students/{student}/promote',    [PromotionController::class, 'promote'])->name('students.promote');

// Reports (extended)
Route::get('/reports/financial',              [ReportController::class, 'financial'])->name('reports.financial');
Route::get('/reports/courses',                [ReportController::class, 'courses'])->name('reports.courses');
Route::get('/reports/financial/pdf',          [ReportController::class, 'financialPdf'])->name('reports.financial.pdf');
Route::get('/reports/financial/excel',        [ReportController::class, 'financialExcel'])->name('reports.financial.excel');
Route::get('/reports/students/pdf',           [ReportController::class, 'studentsPdf'])->name('reports.students.pdf');
Route::get('/reports/students/excel',         [ReportController::class, 'studentsExcel'])->name('reports.students.excel');
Route::get('/reports/attendance/pdf',         [ReportController::class, 'attendancePdf'])->name('reports.attendance.pdf');
Route::get('/reports/attendance/excel',       [ReportController::class, 'attendanceExcel'])->name('reports.attendance.excel');
```

---

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Grading Scale Completeness

*For any* score value between 0 and 100 (inclusive), `GradingScale::assign()` SHALL return exactly one of {A1, A2, A3, B1, B2, F}, and the returned grade SHALL correspond to the correct band for that score.

**Validates: Requirements 4.5, 4.10**

### Property 2: Course Aggregate Computation

*For any* valid `quiz_score` and `exam_score` pair (each between 0 and 100), the computed `course_aggregate` SHALL equal `quiz_score + exam_score`.

**Validates: Requirements 4.2**

### Property 3: Overall Aggregate and Average

*For any* student with N courses in a program (N ≥ 1), the `overall_aggregate` SHALL equal the sum of all `course_aggregate` values, and `overall_average` SHALL equal `overall_aggregate / N`.

**Validates: Requirements 4.8, 4.9**

### Property 4: Remarks Determination

*For any* `overall_average` value, `remarks` SHALL be "PASSED" if and only if `overall_average >= 40`, and "FAILED" otherwise.

**Validates: Requirements 4.11**

### Property 5: Fee Balance Invariant

*For any* student, the `balance` returned by `FeeCalculationService::calculate()` SHALL always equal `total_fees - total_paid`, regardless of how many payments have been recorded or deleted.

**Validates: Requirements 5.5, 6.4, 6.6**

### Property 6: Missing Program Fee Defaults to Zero

*For any* program that has no corresponding `program_fees` record, `FeeCalculationService::calculate()` SHALL treat that program's fee contribution as zero (not null, not an error).

**Validates: Requirements 6.2**

### Property 7: Payment Amount Validation

*For any* `amount_paid` value that is less than or equal to zero, the system SHALL reject the payment and return a validation error. *For any* `amount_paid` value greater than zero, the payment SHALL be accepted.

**Validates: Requirements 6.7**

### Property 8: Promotion Advances to Next Sequence

*For any* active student enrolled in a program with sequence N, after `PromotionService::promote()` is called, the student SHALL be enrolled in the program with the minimum sequence value greater than N (i.e., the immediate next program in sequence order).

**Validates: Requirements 3.3, 3.4**

### Property 9: Final Program Promotion Graduates Student

*For any* active student enrolled in the program with the highest `sequence` value, after `PromotionService::promote()` is called, the student's `status` SHALL be set to `graduated` and `program_id` SHALL remain unchanged.

**Validates: Requirements 3.5**

### Property 10: Promotion Records History

*For any* student promotion (whether advancing to next program or graduating), a `PromotionHistory` record SHALL be created with the correct `student_id`, `from_program_id`, `to_program_id` (null if graduating), and `promoted_at` timestamp.

**Validates: Requirements 3.6**

### Property 11: Inactive Student Promotion Rejection

*For any* student whose `status` is not `active` (i.e., `graduated`, `suspended`, or `manifestation`), calling `PromotionService::promote()` SHALL throw an `InvalidArgumentException` and SHALL NOT modify the student record or create a promotion history entry.

**Validates: Requirements 3.7**

### Property 12: Exam Score Uniqueness

*For any* combination of `(student_id, course_id, program_id)`, there SHALL be at most one `exam_scores` record. Subsequent saves for the same combination SHALL update the existing record (upsert), not create a duplicate.

**Validates: Requirements 2.7**

### Property 13: Report Filter Correctness

*For any* filter combination applied to the Student Report (program, status, church_branch), every student in the returned result set SHALL satisfy all applied filter criteria, and no student satisfying all criteria SHALL be absent from the result set.

**Validates: Requirements 7.2**

### Property 14: Admin Route Authorization

*For any* route under the `/admin` prefix, a request made by a user whose `role` is not `admin` SHALL receive a redirect or 403 response and SHALL NOT receive the protected resource.

**Validates: Requirements 9.3**

### Property 15: Program Deletion Guard

*For any* program that has at least one student enrolled (via the `students` table `program_id` foreign key or `enrollments` table), a DELETE request for that program SHALL be rejected with an error message and the program SHALL remain in the database.

**Validates: Requirements 9.6**

### Property 16: Course Deletion Guard

*For any* course that has at least one `exam_scores` record, a DELETE request for that course SHALL be rejected with an error message and the course SHALL remain in the database.

**Validates: Requirements 9.7**

---

## Error Handling

### Validation Errors

All validation is handled by Form Request classes. On failure, Laravel automatically returns a 422 response with `$errors` bag. Modal forms use Alpine.js to display inline errors without closing the dialog.

### Service Layer Errors

`PromotionService::promote()` throws `\InvalidArgumentException` for invalid student state. Controllers catch this and return a flash error:

```php
try {
    $this->promotionService->promote($student);
    return back()->with('success', 'Student promoted successfully.');
} catch (\InvalidArgumentException $e) {
    return back()->with('error', $e->getMessage());
}
```

### Deletion Guards

`ProgramController::destroy()` and `CourseController::destroy()` check for dependent records before deletion:

```php
if ($program->students()->exists() || $program->enrollments()->exists()) {
    return back()->with('error', 'Cannot delete a program with enrolled students.');
}
```

### Migration Failures

Laravel's migration system wraps each migration in a transaction (on supported databases). If a migration fails, it rolls back automatically. Descriptive error messages are logged via Laravel's default exception handler.

### Missing Exam Scores

`ReportCardService::generate()` returns `['has_scores' => false]` when no `ExamScore` records exist for the student/program combination. The view renders an empty template with the notice "No scores recorded yet."

---

## Testing Strategy

### Unit Tests (PHPUnit)

Unit tests cover service class logic with in-memory/mocked dependencies:

- `PromotionServiceTest` — tests all promotion scenarios (advance, graduate, reject inactive)
- `FeeCalculationServiceTest` — tests balance computation, missing fee defaults, payment deletion
- `ReportCardServiceTest` — tests aggregate computation, grade assignment, remarks, empty state
- `GradingScaleTest` — tests all grade band boundaries

### Property-Based Tests

The project uses **PestPHP** with the **`pestphp/pest-plugin-faker`** or a dedicated PBT library. Given the PHP ecosystem, **`giorgiosironi/eris`** (a QuickCheck-style library for PHP) is the recommended choice for property-based testing.

Install:
```bash
composer require --dev giorgiosironi/eris:^0.12
```

Each property test runs a minimum of **100 iterations**.

Tag format: `Feature: kmc-enterprise-upgrade, Property {N}: {property_text}`

**Property tests to implement:**

| Property | Test Class | Generators |
|---|---|---|
| P1: Grading Scale Completeness | `GradingScalePropertyTest` | `float` in [0, 100] |
| P2: Course Aggregate Computation | `ExamScorePropertyTest` | two `float` in [0, 100] |
| P3: Overall Aggregate and Average | `ReportCardPropertyTest` | collection of N score pairs |
| P4: Remarks Determination | `ReportCardPropertyTest` | `float` in [0, 100] |
| P5: Fee Balance Invariant | `FeeCalculationPropertyTest` | list of payment amounts |
| P6: Missing Fee Defaults to Zero | `FeeCalculationPropertyTest` | program without fee record |
| P7: Payment Amount Validation | `PaymentValidationPropertyTest` | `float` (positive and non-positive) |
| P8: Promotion Advances Sequence | `PromotionServicePropertyTest` | student + ordered program list |
| P9: Final Program Graduates | `PromotionServicePropertyTest` | student in last program |
| P10: Promotion Records History | `PromotionServicePropertyTest` | any active student |
| P11: Inactive Rejection | `PromotionServicePropertyTest` | student with non-active status |
| P12: Exam Score Uniqueness | `ExamScorePropertyTest` | repeated (student, course, program) triple |
| P13: Report Filter Correctness | `ReportFilterPropertyTest` | random student set + filter combo |
| P14: Admin Route Authorization | `AdminAuthorizationPropertyTest` | non-admin user + admin route |
| P15: Program Deletion Guard | `ProgramDeletionPropertyTest` | program with enrolled students |
| P16: Course Deletion Guard | `CourseDeletionPropertyTest` | course with exam scores |

### Integration Tests

- PDF export returns `application/pdf` content-type with correct filename header
- Excel export returns `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet` content-type
- Report export includes all records matching current filter (not just current page)

### Example-Based Tests

- Dashboard controller returns view with all required stat keys
- Student profile view contains all required fields
- ReportCardService returns `has_scores: false` for student with no scores
- Named routes exist for all new endpoints
