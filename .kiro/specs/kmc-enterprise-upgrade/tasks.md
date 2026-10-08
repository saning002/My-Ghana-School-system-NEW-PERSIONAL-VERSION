# Implementation Plan: KMC Enterprise Upgrade

## Overview

Upgrade the existing Laravel 11 KMC College Management System into a premium enterprise platform. The implementation is additive — new migrations, models, services, controllers, views, and routes are layered on top of the working foundation. Tasks follow the order: packages → migrations → models/services → controllers/routes → views → seeders → tests.

## Tasks

- [x] 1. Install Composer packages
  - Run `composer require barryvdh/laravel-dompdf:^3.0 maatwebsite/excel:^3.1` to add PDF and Excel export support
  - Run `composer require --dev giorgiosironi/eris:^0.12` to add property-based testing support
  - Verify both packages appear in `composer.json` and `composer.lock`
  - _Requirements: 4.12, 4.13, 7.5, 7.6_

- [x] 2. Create database migrations
  - [x] 2.1 Create migration `alter_students_table_enterprise_upgrade`
    - Drop the `level` column from `students`
    - Modify `status` enum to accept exactly: `active`, `graduated`, `suspended`, `manifestation`
    - _Requirements: 2.1, 2.2_

  - [x] 2.2 Create migration `add_sequence_to_programs_table`
    - Add `sequence` integer column (not null, default 0) to `programs`
    - _Requirements: 2.4_

  - [x] 2.3 Create migration `create_program_fees_table`
    - Columns: `id`, `program_id` (FK → programs), `amount` decimal(10,2), `timestamps`
    - _Requirements: 2.5_

  - [x] 2.4 Create migration `create_payments_table`
    - Columns: `id`, `student_id` (FK → students), `amount_paid` decimal(10,2), `payment_date` date, `notes` text nullable, `timestamps`
    - _Requirements: 2.6_

  - [x] 2.5 Create migration `create_exam_scores_table`
    - Columns: `id`, `student_id` (FK → students), `course_id` (FK → courses), `program_id` (FK → programs), `quiz_score` decimal(5,2) nullable, `exam_score` decimal(5,2) nullable, `timestamps`
    - Add unique constraint on (`student_id`, `course_id`, `program_id`)
    - _Requirements: 2.7_

  - [x] 2.6 Create migration `create_promotion_history_table`
    - Columns: `id`, `student_id` (FK → students), `from_program_id` (FK → programs), `to_program_id` (FK → programs, nullable, ON DELETE SET NULL), `promoted_at` timestamp, `timestamps`
    - _Requirements: 3.6_

- [x] 3. Create and update Eloquent models
  - [x] 3.1 Update `app/Models/Student.php`
    - Remove `level` from `$fillable`
    - Add `examScores()`, `payments()`, and `promotionHistory()` HasMany relationships
    - _Requirements: 2.8, 10.5_

  - [x] 3.2 Update `app/Models/Program.php`
    - Add `sequence` to `$fillable`
    - Add `programFee()` HasOne and `examScores()` HasMany relationships
    - _Requirements: 2.8, 10.5_

  - [x] 3.3 Create `app/Models/ProgramFee.php`
    - Set `$fillable = ['program_id', 'amount']`
    - Add `program()` BelongsTo relationship
    - _Requirements: 2.8, 10.5_

  - [x] 3.4 Create `app/Models/Payment.php`
    - Set `$fillable = ['student_id', 'amount_paid', 'payment_date', 'notes']`
    - Cast `payment_date` to `date`
    - Add `student()` BelongsTo relationship
    - _Requirements: 2.8, 10.5_

  - [x] 3.5 Create `app/Models/ExamScore.php`
    - Set `$fillable = ['student_id', 'course_id', 'program_id', 'quiz_score', 'exam_score']`
    - Add `student()`, `course()`, `program()` BelongsTo relationships
    - _Requirements: 2.8, 10.5_

  - [x] 3.6 Create `app/Models/PromotionHistory.php`
    - Set `$fillable = ['student_id', 'from_program_id', 'to_program_id', 'promoted_at']`
    - Cast `promoted_at` to `datetime`
    - Add `student()`, `fromProgram()`, `toProgram()` BelongsTo relationships
    - _Requirements: 2.8, 10.5_

- [x] 4. Create service classes and register them
  - [x] 4.1 Create `app/Services/GradingScale.php`
    - Implement static `assign(float $score): string` returning A1/A2/A3/B1/B2/F per the defined bands
    - Add PHPDoc block documenting the method
    - _Requirements: 4.5, 10.10_

  - [ ]* 4.2 Write property test for GradingScale completeness
    - **Property 1: Grading Scale Completeness** — for any float in [0, 100], `assign()` returns exactly one valid grade in the correct band
    - **Validates: Requirements 4.5, 4.10**

  - [x] 4.3 Create `app/Services/PromotionService.php`
    - Implement `promote(Student $student): void` with guard, next-program lookup, DB::transaction, and PromotionHistory recording
    - Add PHPDoc block documenting parameters, return type, and thrown exceptions
    - _Requirements: 3.3, 3.4, 3.5, 3.6, 3.7, 10.1, 10.10_

  - [ ]* 4.4 Write property tests for PromotionService
    - **Property 8: Promotion Advances to Next Sequence** — active student in program N ends up in program with min sequence > N
    - **Property 9: Final Program Promotion Graduates Student** — active student in last program gets status `graduated`
    - **Property 10: Promotion Records History** — every promotion creates a PromotionHistory record with correct fields
    - **Property 11: Inactive Student Promotion Rejection** — non-active student throws InvalidArgumentException, no record created
    - **Validates: Requirements 3.3, 3.4, 3.5, 3.6, 3.7**

  - [x] 4.5 Create `app/Services/FeeCalculationService.php`
    - Implement `calculate(Student $student): array` returning `['total', 'paid', 'balance']`
    - Treat missing ProgramFee records as zero contribution
    - Add PHPDoc block documenting parameters and return type
    - _Requirements: 5.5, 6.2, 6.4, 6.6, 10.2, 10.10_

  - [ ]* 4.6 Write property tests for FeeCalculationService
    - **Property 5: Fee Balance Invariant** — balance always equals total_fees minus total_paid for any payment list
    - **Property 6: Missing Program Fee Defaults to Zero** — program with no fee record contributes 0, not null or error
    - **Validates: Requirements 5.5, 6.2, 6.4, 6.6**

  - [x] 4.7 Create `app/Services/ReportCardService.php`
    - Implement `generate(Student $student, Program $program): array` with aggregate, position ranking (DENSE_RANK in PHP), average, grade, and remarks computation
    - Return `has_scores: false` when no ExamScore records exist
    - Add PHPDoc block documenting parameters and return shape
    - _Requirements: 4.2, 4.3, 4.4, 4.5, 4.8, 4.9, 4.10, 4.11, 4.15, 10.3, 10.10_

  - [ ]* 4.8 Write property tests for ReportCardService
    - **Property 2: Course Aggregate Computation** — for any quiz_score and exam_score in [0,100], aggregate equals their sum
    - **Property 3: Overall Aggregate and Average** — overall_aggregate equals sum of course aggregates; overall_average equals overall_aggregate / N
    - **Property 4: Remarks Determination** — remarks is "PASSED" iff overall_average >= 40
    - **Validates: Requirements 4.2, 4.8, 4.9, 4.11**

  - [x] 4.9 Register services in `app/Providers/AppServiceProvider.php`
    - Bind `PromotionService`, `FeeCalculationService`, and `ReportCardService` in the service container
    - _Requirements: 10.6_

- [x] 5. Create Form Request classes
  - [x] 5.1 Create `app/Http/Requests/StoreExamScoreRequest.php`
    - Validate `student_id`, `program_id`, and `scores` array with `quiz_score`/`exam_score` per course
    - _Requirements: 4.1, 10.4_

  - [x] 5.2 Create `app/Http/Requests/StorePaymentRequest.php`
    - Validate `student_id`, `amount_paid` (> 0), `payment_date`, `notes` (nullable)
    - _Requirements: 6.3, 6.7, 10.4_

  - [ ]* 5.3 Write property test for payment amount validation
    - **Property 7: Payment Amount Validation** — any amount_paid ≤ 0 is rejected; any amount_paid > 0 is accepted
    - **Validates: Requirements 6.7**

  - [x] 5.4 Create `app/Http/Requests/StoreProgramFeeRequest.php`
    - Validate `program_id` and `amount` (>= 0, decimal)
    - _Requirements: 6.1, 10.4_

  - [x] 5.5 Create `app/Http/Requests/PromoteStudentRequest.php`
    - Validate `student_id` exists and has status `active`
    - _Requirements: 3.7, 10.4_

- [x] 6. Create controllers and register routes
  - [x] 6.1 Create `app/Http/Controllers/Admin/ExamController.php`
    - Implement `index`, `create`, `store` (using `ExamScore::updateOrCreate`), `show`, `pdf`, and `excel` methods
    - Inject `ReportCardService` via constructor; keep each method ≤ 20 lines
    - _Requirements: 4.1, 4.12, 4.13, 4.14, 10.7_

  - [ ]* 6.2 Write property test for exam score uniqueness
    - **Property 12: Exam Score Uniqueness** — repeated saves for the same (student_id, course_id, program_id) upsert, not duplicate
    - **Validates: Requirements 2.7**

  - [x] 6.3 Create `app/Http/Controllers/Admin/FeesController.php`
    - Implement `index`, `store` (record payment), and `destroy` (delete payment) methods
    - Inject `FeeCalculationService` via constructor; keep each method ≤ 20 lines
    - _Requirements: 6.1, 6.3, 6.5, 6.6, 10.7_

  - [x] 6.4 Create `app/Http/Controllers/Admin/PromotionController.php`
    - Implement `promote` method delegating to `PromotionService`, catching `InvalidArgumentException` and flashing error
    - Keep method ≤ 20 lines
    - _Requirements: 3.3, 3.4, 3.5, 9.5, 10.1, 10.7_

  - [x] 6.5 Update `app/Http/Controllers/Admin/ReportController.php`
    - Add `financial`, `programs`, `courses`, `financialPdf`, `financialExcel`, `studentsPdf`, `studentsExcel`, `attendancePdf`, `attendanceExcel` methods
    - Inject `FeeCalculationService` via constructor; keep each method ≤ 20 lines
    - _Requirements: 7.1, 7.2, 7.3, 8.1, 8.2, 8.3, 8.5, 10.7_

  - [x] 6.6 Update `app/Http/Controllers/Admin/DashboardController.php`
    - Add fees summary stats (total billed, total collected, total outstanding) to the view data
    - Inject `FeeCalculationService` via constructor
    - _Requirements: 1.6, 6.8_

  - [x] 6.7 Update `app/Http/Controllers/Admin/StudentController.php`
    - Add `show` method (or update existing) to load student profile with exam scores, payments, promotion history, and attendance
    - Inject `FeeCalculationService` via constructor
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7_

  - [x] 6.8 Update `app/Http/Controllers/Admin/ProgramController.php`
    - Add deletion guard: reject delete if students or enrollments exist, flash error message
    - _Requirements: 9.6, 10.9_

  - [x] 6.9 Update `app/Http/Controllers/Admin/CourseController.php`
    - Add deletion guard: reject delete if exam_scores exist for the course, flash error message
    - _Requirements: 9.7_

  - [ ]* 6.10 Write property tests for deletion guards
    - **Property 15: Program Deletion Guard** — any program with enrolled students rejects DELETE and remains in DB
    - **Property 16: Course Deletion Guard** — any course with exam scores rejects DELETE and remains in DB
    - **Validates: Requirements 9.6, 9.7**

  - [x] 6.11 Register all new routes in `routes/web.php`
    - Add exam, fees, promotion, and extended report routes inside the existing `admin` middleware group
    - Follow `admin.{resource}.{action}` naming convention
    - _Requirements: 10.8_

  - [ ]* 6.12 Write property test for admin route authorization
    - **Property 14: Admin Route Authorization** — any request to `/admin/*` by a non-admin user receives a redirect or 403, never the protected resource
    - **Validates: Requirements 9.3**

- [x] 7. Checkpoint — Ensure all tests pass
  - Run `php artisan test` and confirm all existing tests still pass before proceeding to views.
  - Ensure all tests pass, ask the user if questions arise.

- [x] 8. Create and update views
  - [x] 8.1 Update `resources/views/layouts/app.blade.php`
    - Replace indigo/dark theme with gold/yellow Tailwind config (`#D4A017` primary, `#F5F0E8` surface, `#1A1A1A` ink)
    - Load Tailwind CSS, Alpine.js, and Font Awesome via CDN
    - Implement fixed sidebar with gold gradient (`#78520a` → `#92680a` → `#D4A017`)
    - Implement fixed top nav with page title, user name, and logout button
    - Add mobile hamburger toggle and slide-in drawer for viewports < 768px
    - Add Alpine.js toast notification component reading `session('success')` and `session('error')`, auto-dismissing after 4 seconds
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.10, 1.12_

  - [x] 8.2 Update `resources/views/admin/dashboard.blade.php`
    - Add stat cards: total active students, total lecturers, total programs, total courses, overall attendance rate
    - Add fees summary cards: total billed, total collected, total outstanding
    - _Requirements: 1.6, 6.8_

  - [x] 8.3 Update `resources/views/partials/sidebar.blade.php`
    - Add navigation links for Exams, Fees, and Reports sub-sections using Font Awesome icons
    - Apply gold gradient background and active-state highlighting
    - _Requirements: 1.3, 1.12_

  - [x] 8.4 Create `resources/views/admin/exams/index.blade.php`
    - Program selection list for score entry; links to `admin.exams.create` with program pre-selected
    - _Requirements: 4.1_

  - [x] 8.5 Create `resources/views/admin/exams/create.blade.php`
    - Score entry table with one row per course (quiz_score + exam_score inputs)
    - Student selector filtered by selected program
    - _Requirements: 4.1_

  - [x] 8.6 Create `resources/views/admin/exams/show.blade.php`
    - Report card preview with all required columns and footer totals
    - "Download PDF", "Export Excel", and "Print" action buttons
    - Display "No scores recorded yet" notice when `has_scores` is false
    - _Requirements: 4.6, 4.7, 4.12, 4.13, 4.14, 4.15_

  - [x] 8.7 Create `resources/views/pdf/report_card.blade.php`
    - DomPDF-rendered template with gold header (`#D4A017`), black borders, black text
    - School name, subtitle, student/program info, course table, grading scale legend, footer totals
    - _Requirements: 4.6, 4.7, 4.12_

  go- [ ] 8.8 Create `resources/views/admin/fees/index.blade.php`
    - Program fee configuration section (set/update amount per program)
    - Payment recording form (student selector, amount, date, notes)
    - Payment history table per student ordered by payment_date descending
    - _Requirements: 6.1, 6.3, 6.5_

  - [x] 8.9 Update `resources/views/admin/students/show.blade.php`
    - Display full student profile fields (name, ID, email, phone, address, DOB, church branch, admission date, program, status)
    - Add tabbed section: "Report Cards", "Attendance", "Fees"
    - Show promotion history (from program, to program, date)
    - Show status badge for `suspended` or `manifestation` students
    - Add "Promote" button with confirmation dialog linking to `admin.students.promote`
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 9.4, 9.5_

  - [x] 8.10 Create `resources/views/admin/reports/financial.blade.php`
    - Per-student fee summary table (total fees, total paid, balance) with program and date range filters
    - Export PDF and Export Excel buttons
    - _Requirements: 8.1, 8.4, 8.5_

  - [x] 8.11 Create `resources/views/admin/reports/courses.blade.php`
    - Per-course enrollment table (program, enrolled count, average aggregate) with program filter
    - Export PDF and Export Excel buttons
    - _Requirements: 8.3, 8.4, 8.5_

  - [x] 8.12 Update `resources/views/admin/reports/index.blade.php`
    - Add navigation cards/links for all five report sub-sections: Students, Attendance, Financial, Program Performance, Course Enrollment
    - _Requirements: 7.1_

  - [x] 8.13 Update existing admin listing views for gold theme consistency
    - Update `admin/students/index.blade.php`, `admin/programs/index.blade.php`, `admin/courses/index.blade.php`, `admin/lecturers/index.blade.php` to use gold-themed table headers, buttons, and pagination
    - Ensure modal forms display inline validation errors without closing the dialog
    - _Requirements: 1.8, 1.9, 1.11_

- [x] 9. Update database seeder
  - Update `database/seeders/DatabaseSeeder.php` to seed the five default Programs with correct sequence values: (1) Pre-College Course Program, (2) First Semester, (3) Second Semester, (4) Third Semester, (5) Third Semester Practical
  - Ensure seeder is idempotent (uses `updateOrCreate` or checks before inserting)
  - _Requirements: 3.1_

- [x] 10. Final checkpoint — Ensure all tests pass
  - Run `php artisan test` and confirm the full test suite passes.
  - Ensure all tests pass, ask the user if questions arise.

- [x] 11. Write unit and integration tests
  - [x] 11.1 Write unit tests for `GradingScaleTest`
    - Test all six grade band boundaries (exact boundary values and mid-range values)
    - _Requirements: 4.5_

  - [ ]* 11.2 Write property test for GradingScale (P1)
    - **Property 1: Grading Scale Completeness**
    - **Validates: Requirements 4.5, 4.10**

  - [x] 11.3 Write unit tests for `PromotionServiceTest`
    - Test advance to next program, graduation from final program, rejection of inactive student
    - _Requirements: 3.3, 3.4, 3.5, 3.6, 3.7_

  - [ ]* 11.4 Write property tests for PromotionService (P8, P9, P10, P11)
    - **Property 8: Promotion Advances to Next Sequence**
    - **Property 9: Final Program Promotion Graduates Student**
    - **Property 10: Promotion Records History**
    - **Property 11: Inactive Student Promotion Rejection**
    - **Validates: Requirements 3.3, 3.4, 3.5, 3.6, 3.7**

  - [x] 11.5 Write unit tests for `FeeCalculationServiceTest`
    - Test balance computation, missing fee defaults to zero, payment deletion recomputes balance
    - _Requirements: 5.5, 6.2, 6.4, 6.6_

  - [ ]* 11.6 Write property tests for FeeCalculationService (P5, P6)
    - **Property 5: Fee Balance Invariant**
    - **Property 6: Missing Program Fee Defaults to Zero**
    - **Validates: Requirements 5.5, 6.2, 6.4, 6.6**

  - [x] 11.7 Write unit tests for `ReportCardServiceTest`
    - Test aggregate computation, grade assignment, remarks, empty state (`has_scores: false`)
    - _Requirements: 4.2, 4.8, 4.9, 4.10, 4.11, 4.15_

  - [ ]* 11.8 Write property tests for ReportCardService (P2, P3, P4)
    - **Property 2: Course Aggregate Computation**
    - **Property 3: Overall Aggregate and Average**
    - **Property 4: Remarks Determination**
    - **Validates: Requirements 4.2, 4.8, 4.9, 4.11**

  - [ ]* 11.9 Write property test for payment validation (P7)
    - **Property 7: Payment Amount Validation**
    - **Validates: Requirements 6.7**

  - [ ]* 11.10 Write property test for exam score uniqueness (P12)
    - **Property 12: Exam Score Uniqueness**
    - **Validates: Requirements 2.7**

  - [ ]* 11.11 Write property test for report filter correctness (P13)
    - **Property 13: Report Filter Correctness**
    - **Validates: Requirements 7.2**

  - [ ]* 11.12 Write property test for admin route authorization (P14)
    - **Property 14: Admin Route Authorization**
    - **Validates: Requirements 9.3**

  - [ ]* 11.13 Write property tests for deletion guards (P15, P16)
    - **Property 15: Program Deletion Guard**
    - **Property 16: Course Deletion Guard**
    - **Validates: Requirements 9.6, 9.7**

  - [ ]* 11.14 Write integration tests for PDF and Excel exports
    - Test PDF export returns `application/pdf` with correct filename header
    - Test Excel export returns correct MIME type with correct filename header
    - Test exports include all records matching current filter, not just current page
    - _Requirements: 4.12, 4.13, 7.5, 7.6, 8.5_

- [x] 12. Action button visibility (Requirement 11)
  - [x] 12.1 Update `resources/views/admin/students/index.blade.php` — top-of-page "Add Student" button
    - Ensure the "Add Student" button is rendered above the data table/card list, visible without scrolling
    - Apply gold primary colour (`bg-primary-600`) with `fa-plus` Font Awesome icon
    - _Requirements: 11.1, 11.3_

  - [x] 12.2 Update `resources/views/admin/students/index.blade.php` — empty-state CTA
    - Add "Add First Student" inline CTA inside the empty-state area (both mobile card and desktop table empty rows)
    - _Requirements: 11.4_

  - [x] 12.3 Update `resources/views/admin/attendance/index.blade.php` — top-of-page "Mark Attendance" button
    - Ensure the "Mark Attendance" button is rendered above the data table, visible without scrolling
    - Apply gold primary colour (`bg-primary-600`) with `fa-plus` Font Awesome icon
    - _Requirements: 11.2, 11.3_

  - [x] 12.4 Update `resources/views/admin/attendance/index.blade.php` — empty-state CTA
    - Add "Mark First Attendance" inline CTA inside the empty-state area
    - _Requirements: 11.5_

- [x] 13. Student registration and edit form field updates (Requirement 12)
  - [x] 13.1 Update `resources/views/admin/students/create.blade.php` — label and field changes
    - Rename "Church Branch" label to "Ministry Branch" for the `church_branch_id` field
    - Remove the "Level" input field entirely from the create form
    - _Requirements: 12.1, 12.2, 12.7_

  - [x] 13.2 Update `resources/views/admin/students/edit.blade.php` — label, field, and status dropdown changes
    - Rename "Church Branch" label to "Ministry Branch" for the `church_branch_id` field
    - Remove the "Level" input field entirely from the edit form
    - Add `manifestation` as a selectable option in the Status dropdown
    - Order Status options as: Active, Graduated, Suspended, Manifestation
    - Pre-select the current student status (including `manifestation`) when the form loads
    - _Requirements: 12.1, 12.3, 12.4, 12.5, 12.6, 12.7_

- [x] 14. Clickable student rows and comprehensive student profile (Requirement 13)
  - [x] 14.1 Update `resources/views/admin/students/index.blade.php` — clickable rows
    - Make each student row in the desktop table clickable (add `cursor-pointer` and hover highlight) navigating to `admin.students.show`
    - Make the student name in the mobile card list a clickable link to `admin.students.show`
    - _Requirements: 13.1, 13.2_

  - [x] 14.2 Update `resources/views/admin/students/show.blade.php` — comprehensive profile view
    - Display all personal details: full name, student ID, email, phone, address, date of birth, ministry branch, admission date, current program, current status
    - Display Fees Summary section (total fees billed, total paid, outstanding balance via FeeCalculationService)
    - Display Exam Results / Report Cards section listing programs with exam scores and links to each report card
    - Display Attendance Records section grouped by course (present count, absent count, attendance percentage)
    - Display Promotion History section (from program, to program, promotion date)
    - Show coloured status badge in profile header when status is `suspended` or `manifestation`
    - Add Edit and Promote action buttons in the profile header
    - _Requirements: 13.3, 13.4, 13.5, 13.6, 13.7, 13.8, 13.9_

- [x] 15. Global student search component (Requirement 14)
  - [x] 15.1 Add `search()` method to `app/Http/Controllers/Admin/StudentController.php`
    - Implement `GET /admin/students/search?q=...` returning JSON array of matching students (id, full_name, student_id, program name)
    - Search by partial match on full name, student ID, and registration number (at least 2 characters)
    - Return empty array with "No students found" indicator when no matches
    - Keep method ≤ 20 lines
    - _Requirements: 14.1, 14.2, 14.4, 14.8_

  - [x] 15.2 Register the search route in `routes/web.php`
    - Add `GET /admin/students/search` → `StudentController@search` named `admin.students.search`
    - Place the route before the `students` resource route to avoid parameter conflicts
    - _Requirements: 14.1, 10.8_

  - [x] 15.3 Create `resources/views/components/student-search.blade.php`
    - Text input with Alpine.js reactive state for query and results dropdown
    - Debounced fetch to `admin.students.search?q=...` triggered after 2+ characters typed
    - Dropdown list showing each student's full name, student ID, and program
    - "No students found" message when results are empty
    - Keyboard navigation: arrow keys to move through results, Enter to select
    - On selection: populate hidden `student_id` input and display student name as confirmation
    - On clear: reset selection and clear dependent fields
    - _Requirements: 14.2, 14.3, 14.4, 14.5, 14.6, 14.7_

  - [x] 15.4 Replace student selector inputs with the new component across existing views
    - Update `resources/views/admin/exams/create.blade.php` to use `<x-student-search>`
    - Update `resources/views/admin/fees/index.blade.php` to use `<x-student-search>`
    - Update `resources/views/admin/attendance/create.blade.php` to use `<x-student-search>`
    - _Requirements: 14.3_

- [x] 16. Report preview, download, and print (Requirement 15)
  - [x] 16.1 Add preview methods to `app/Http/Controllers/Admin/ReportController.php`
    - Add `studentsPreview`, `attendancePreview`, `financialPreview`, `programsPreview`, `coursesPreview` methods
    - Each method applies the same filters as the corresponding report method and returns a full-page preview view
    - Keep each method ≤ 20 lines
    - _Requirements: 15.1, 15.2, 15.6_

  - [x] 16.2 Add missing PDF/Excel methods to `app/Http/Controllers/Admin/ReportController.php`
    - Add `programsPdf` and `programsExcel` methods for the Program Performance report
    - Add `coursesPdf` and `coursesExcel` methods for the Course Enrollment report
    - Add `studentsExcel`, `attendanceExcel` methods if not already present
    - Apply current filter selection to exports (not just the current pagination page)
    - _Requirements: 15.3, 15.4, 15.6_

  - [x] 16.3 Register all new report routes in `routes/web.php`
    - Add preview routes: `reports.students.preview`, `reports.attendance.preview`, `reports.financial.preview`, `reports.programs.preview`, `reports.courses.preview`
    - Add missing PDF/Excel routes: `reports.programs.pdf`, `reports.programs.excel`, `reports.courses.pdf`, `reports.courses.excel`, `reports.students.excel`, `reports.attendance.excel`
    - Follow `admin.reports.{report}.{action}` naming convention
    - _Requirements: 15.1, 10.8_

  - [x] 16.4 Create preview Blade views under `resources/views/admin/reports/preview/`
    - Create `students.blade.php`, `attendance.blade.php`, `financial.blade.php`, `programs.blade.php`, `courses.blade.php`
    - Each view renders the full filtered dataset formatted for on-screen reading (no file download triggered)
    - Display "No data found" message when the filtered dataset is empty
    - _Requirements: 15.2, 15.8_

  - [x] 16.5 Update all report index views to show four action buttons
    - Update `resources/views/admin/reports/students.blade.php`, `attendance.blade.php`, `financial.blade.php`, `programs.blade.php`, `courses.blade.php`
    - Add Preview, Download PDF, Download Excel, and Print buttons using consistent visual style and placement
    - Disable Download PDF and Download Excel buttons (with tooltip "No data to export") when the filtered dataset is empty
    - _Requirements: 15.7, 15.8_

  - [x] 16.6 Add print-optimised CSS to report views
    - Add `@media print` styles to each report view (or to a shared partial) hiding sidebar, top nav, and action buttons
    - Ensure the Print button triggers `window.print()` via an `onclick` handler
    - _Requirements: 15.5_

- [x] 17. Final checkpoint — Ensure all new tasks pass
  - Run `php artisan test` and confirm the full test suite passes after implementing tasks 12–16.
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for a faster MVP
- Each task references specific requirements for traceability
- Checkpoints at tasks 7, 10, and 17 ensure incremental validation
- Property tests validate universal correctness properties using `giorgiosironi/eris`
- Unit tests validate specific examples and edge cases using PHPUnit/PestPHP
- All service classes must have PHPDoc blocks per Requirement 10.10
- All controller methods must stay ≤ 20 lines per Requirement 10.7
- Use `DB::transaction` in any controller method with multiple writes per Requirement 10.9
