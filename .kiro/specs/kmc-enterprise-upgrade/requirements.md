# Requirements Document

## Introduction

This document defines the requirements for upgrading the existing Laravel 11 Kingdom Ministerial University College (KMC) management system into a premium enterprise-level platform. The existing system has foundational modules for Students, Programs, Courses, Attendance, Lecturers, and Church Branches with a basic admin UI. The upgrade introduces a polished gold/yellow premium theme, structured program progression, a full exam and report card engine, fees management, comprehensive reporting, and a clean service-oriented architecture — all within the existing Laravel 11 / PHP 8.2 / MySQL (XAMPP) stack.

---

## Glossary

- **System**: The KMC College Management System (the Laravel 11 application).
- **Admin**: An authenticated user with `role = 'admin'` who manages all platform data.
- **Student**: A registered learner with `role = 'student'` in the `users` table, linked to a record in the `students` table.
- **Lecturer**: An authenticated user with `role = 'lecturer'` who marks attendance.
- **Program**: An ordered academic stage (e.g., Pre-College Course Program, First Semester). Stored in the `programs` table with a `sequence` field.
- **Course**: A subject belonging to a Program. Stored in the `courses` table.
- **Enrollment**: A record linking a Student to a Course within a Program for a given year. Stored in the `enrollments` table.
- **ExamScore**: A record of a student's quiz mark and exam score for a specific course. Stored in the `exam_scores` table.
- **ReportCard**: A generated view/PDF showing a student's scores, grades, position, and aggregate for all courses in a program.
- **PromotionService**: A Laravel service class responsible for advancing a student to the next Program in sequence.
- **FeeCalculationService**: A Laravel service class responsible for computing a student's total fees, total paid, and outstanding balance.
- **ProgramFee**: A record defining the fee amount for a specific Program. Stored in the `program_fees` table.
- **Payment**: A record of a fee payment made by or on behalf of a student. Stored in the `payments` table.
- **GradingScale**: The fixed letter-grade bands: A1 = 80–100, A2 = 70–79, A3 = 60–69, B1 = 50–59, B2 = 40–49, F = 0–39.
- **Toast Notification**: A transient, auto-dismissing UI message confirming success or reporting an error.
- **Modal Form**: An overlay dialog used for create/edit operations without navigating away from the current page.
- **Sidebar**: The fixed left-hand navigation panel present on all authenticated pages.
- **Top Nav**: The fixed top navigation bar showing the current page title, user name, and logout action.
- **Ministry Branch**: The label used in all student-facing forms for the field stored as `church_branch_id` in the database. The underlying column name is unchanged.
- **Student Search Component**: A reusable, server-backed search input with a filtered dropdown used wherever a student must be selected in the system.
- **Student Profile**: A dedicated page aggregating all information about a single student: personal details, fees summary, exam results, attendance records, and promotion history.
- **Preview**: A browser-rendered, full-page view of a report intended for on-screen reading, distinct from a PDF download.

---

## Requirements

---

### Requirement 1: Premium UI/UX Theme

**User Story:** As an Admin, I want a polished gold/yellow and neutral premium interface, so that the platform reflects the prestige of Kingdom Ministerial University College.

#### Acceptance Criteria

1. THE System SHALL apply a gold/yellow (`#D4A017` primary, `#F5F0E8` background, `#1A1A1A` text) Tailwind CSS design system across all authenticated views.
2. THE System SHALL load Tailwind CSS and Alpine.js via CDN so that no local build step is required.
3. THE System SHALL render a fixed sidebar navigation on all authenticated pages that remains visible during vertical scrolling.
4. THE System SHALL render a fixed top navigation bar on all authenticated pages displaying the current section title, the authenticated user's full name, and a logout button.
5. WHEN the viewport width is below 768px, THE System SHALL collapse the sidebar into a slide-in drawer toggled by a hamburger button in the top nav.
6. THE System SHALL display a dashboard with summary stat cards showing: total active students, total lecturers, total programs, total courses, and overall attendance rate.
7. WHEN data is loading or an action is processing, THE System SHALL display a visual loading indicator.
8. THE System SHALL render all data listing pages as tables with per-column search/filter inputs, sortable headers, and server-side pagination showing 15 records per page by default.
9. THE System SHALL present all create and edit forms inside Modal dialogs so that the user remains on the listing page after submission.
10. WHEN an action succeeds (create, update, delete, promote, record payment), THE System SHALL display a gold-themed Toast Notification that auto-dismisses after 4 seconds.
11. IF a server-side validation error occurs, THEN THE System SHALL display inline field-level error messages within the Modal form without closing the dialog.
12. THE System SHALL use Font Awesome icons (CDN) consistently for all navigation items and action buttons.

---

### Requirement 2: Database Schema Upgrades

**User Story:** As an Admin, I want the database to accurately reflect the college's data model, so that student records, program ordering, and new features are stored correctly.

#### Acceptance Criteria

1. THE System SHALL remove the `level` column from the `students` table via a new migration, preserving all other existing student data.
2. THE System SHALL update the `status` enum on the `students` table to accept exactly four values: `active`, `graduated`, `suspended`, `manifestation`.
3. THE System SHALL retain the `password` column on the `users` table so that admin and student login credentials are preserved.
4. THE System SHALL add a `sequence` integer column (not null, default 0) to the `programs` table via a new migration to define program ordering.
5. THE System SHALL create a `program_fees` table with columns: `id`, `program_id` (foreign key → `programs.id`), `amount` (decimal 10,2), `timestamps`.
6. THE System SHALL create a `payments` table with columns: `id`, `student_id` (foreign key → `students.id`), `amount_paid` (decimal 10,2), `payment_date` (date), `notes` (text, nullable), `timestamps`.
7. THE System SHALL create an `exam_scores` table with columns: `id`, `student_id` (foreign key → `students.id`), `course_id` (foreign key → `courses.id`), `program_id` (foreign key → `programs.id`), `quiz_score` (decimal 5,2, nullable), `exam_score` (decimal 5,2, nullable), `timestamps`, and a unique constraint on (`student_id`, `course_id`, `program_id`).
8. THE System SHALL update all Eloquent models (`Student`, `Program`, `Payment`, `ProgramFee`, `ExamScore`) to reflect the schema changes with correct `$fillable` arrays and relationships.
9. IF a migration fails due to a constraint violation, THEN THE System SHALL roll back the migration and log a descriptive error message.

---

### Requirement 3: Program Progression

**User Story:** As an Admin, I want students to automatically advance to the next program when they pass, so that progression is consistent and requires no manual re-assignment.

#### Acceptance Criteria

1. THE System SHALL seed exactly five default Programs in the following sequence order: (1) Pre-College Course Program, (2) First Semester, (3) Second Semester, (4) Third Semester, (5) Third Semester Practical.
2. THE System SHALL provide an Admin UI to view and reorder Programs by dragging or by editing the `sequence` value directly.
3. WHEN an Admin marks a student's result as "passed" for their current Program, THE PromotionService SHALL identify the Program with the next highest `sequence` value.
4. WHEN the next Program exists, THE PromotionService SHALL update the student's `program_id` to the next Program and set `status` to `active`.
5. WHEN no next Program exists (student has completed the final program), THE PromotionService SHALL set the student's `status` to `graduated` and leave `program_id` unchanged.
6. THE PromotionService SHALL record a promotion event (timestamp, from_program_id, to_program_id) in a `promotion_history` table for audit purposes.
7. IF the student's current status is not `active`, THEN THE PromotionService SHALL reject the promotion and return a descriptive error.
8. THE System SHALL display the student's current program and promotion history on the Student Profile page.

---

### Requirement 4: Exams and Report Card

**User Story:** As an Admin, I want to input exam scores per course per student and generate a branded report card, so that academic results are formally documented and exportable.

#### Acceptance Criteria

1. THE System SHALL provide an exam score entry form where an Admin selects a Program, then a Student enrolled in that Program, and enters `quiz_score` and `exam_score` for each Course in that Program.
2. WHEN scores are saved, THE System SHALL compute and store `course_aggregate` as `quiz_score + exam_score` for each course row.
3. THE System SHALL compute `course_position` as the rank of the student's `course_aggregate` among all students enrolled in the same Course and Program, with ties sharing the same rank.
4. THE System SHALL compute `percentage_average` as `(course_aggregate / maximum_possible_aggregate) × 100`, where `maximum_possible_aggregate` is 100.
5. THE System SHALL assign a `grade` letter to each course using the GradingScale: A1 (80–100), A2 (70–79), A3 (60–69), B1 (50–59), B2 (40–49), F (0–39).
6. THE System SHALL generate a Report Card view for a student and program displaying: the school name "KINGDOM MINISTERIAL UNIVERSITY COLLEGE", the subtitle "KINGDOM FELLOWSHIP MINISTRY (TRAINS, EQUIPS & EDUCATES GOSPEL MINISTERS OF KINGDOM AGE)", student name, program name, a course table with columns (Course, Quiz, Scores, Course Aggregate, Course Position, % Average, Grade), a grading scale legend, and a footer row showing total score, overall aggregate, overall average, overall grade, and remarks.
7. THE Report Card SHALL use a gold/yellow background (`#D4A017` header), black borders, and black text to match the KMC branded design.
8. THE System SHALL compute `overall_aggregate` as the sum of all `course_aggregate` values on the report card.
9. THE System SHALL compute `overall_average` as `overall_aggregate / number_of_courses`.
10. THE System SHALL assign `overall_grade` using the GradingScale applied to `overall_average`.
11. THE System SHALL set `remarks` to "PASSED" when `overall_average >= 40`, and "FAILED" when `overall_average < 40`.
12. WHEN an Admin clicks "Download PDF", THE System SHALL generate a PDF of the Report Card using DomPDF and return it as a downloadable file named `report_card_{student_id}_{program_name}.pdf`.
13. WHEN an Admin clicks "Export Excel", THE System SHALL generate an Excel file of the Report Card data using Maatwebsite Excel and return it as a downloadable file named `report_card_{student_id}_{program_name}.xlsx`.
14. WHEN an Admin clicks "Print", THE System SHALL open the browser print dialog with a print-optimised CSS stylesheet applied to the Report Card view.
15. IF a student has no exam scores recorded for a program, THEN THE System SHALL display an empty report card template with a notice "No scores recorded yet."

---

### Requirement 5: Student Profile

**User Story:** As an Admin, I want a comprehensive student profile page, so that I can view all academic and financial information for a student in one place.

#### Acceptance Criteria

1. THE System SHALL display a Student Profile page accessible from the student listing, showing: full name, student ID, email, phone, address, date of birth, church branch, admission date, current program, and current status.
2. THE System SHALL display a tabbed section on the Student Profile page with tabs: "Report Cards", "Attendance", and "Fees".
3. WHEN the "Report Cards" tab is active, THE System SHALL list all programs for which the student has exam scores, with a link to view or download each Report Card.
4. WHEN the "Attendance" tab is active, THE System SHALL display the student's attendance records grouped by course, showing present count, absent count, and attendance percentage per course.
5. WHEN the "Fees" tab is active, THE System SHALL display: total fees (sum of ProgramFee amounts for all programs the student has been enrolled in), total paid (sum of all Payment amounts for the student), and outstanding balance (total fees minus total paid), computed by the FeeCalculationService.
6. THE System SHALL display the student's promotion history on the profile page showing: from program, to program, and promotion date.
7. WHEN the student's status is `suspended` or `manifestation`, THE System SHALL display a prominent status badge in the profile header.

---

### Requirement 6: Fees Management

**User Story:** As an Admin, I want to manage program fees and record student payments, so that the college's financial records are accurate and up to date.

#### Acceptance Criteria

1. THE System SHALL provide an Admin interface to set or update the fee `amount` for each Program in the `program_fees` table.
2. WHEN a ProgramFee record does not exist for a Program, THE System SHALL treat the fee for that Program as zero.
3. THE System SHALL provide an Admin interface to record a new Payment for a student, capturing: `student_id`, `amount_paid`, `payment_date`, and optional `notes`.
4. WHEN a Payment is recorded, THE FeeCalculationService SHALL recompute and cache the student's `total_fees`, `total_paid`, and `balance`.
5. THE System SHALL display a payment history table for each student showing all Payment records ordered by `payment_date` descending.
6. THE System SHALL allow an Admin to delete a Payment record, after which THE FeeCalculationService SHALL recompute the student's balance.
7. IF `amount_paid` is less than or equal to zero, THEN THE System SHALL reject the payment and display a validation error "Amount paid must be greater than zero."
8. THE System SHALL display a fees summary on the Admin dashboard showing: total fees billed across all students, total collected, and total outstanding.

---

### Requirement 7: Student and Attendance Reports

**User Story:** As an Admin, I want filterable student and attendance reports, so that I can monitor enrolment trends and class participation.

#### Acceptance Criteria

1. THE System SHALL provide a central Reports module accessible from the sidebar with sub-sections: Students, Attendance, Financial, Program Performance, and Course Enrollment.
2. THE System SHALL generate a Student Report listing all students with columns: student ID, full name, program, church branch, admission date, and status; filterable by program, status, and church branch.
3. THE System SHALL generate an Attendance Report listing attendance records filterable by date range, program, and course, showing present/absent counts and attendance percentage per student.
4. WHEN an Admin applies filters, THE System SHALL update the report table without a full page reload using Alpine.js reactive state or a form submission.
5. WHEN an Admin clicks "Export PDF" on any report, THE System SHALL generate a PDF of the filtered report using DomPDF.
6. WHEN an Admin clicks "Export Excel" on any report, THE System SHALL generate an Excel file of the filtered report using Maatwebsite Excel.

---

### Requirement 8: Financial and Performance Reports

**User Story:** As an Admin, I want financial and academic performance reports, so that I can assess the college's revenue and academic outcomes.

#### Acceptance Criteria

1. THE System SHALL generate a Financial Report showing per-student fee summary (total fees, total paid, balance) filterable by program and payment date range.
2. THE System SHALL generate a Program Performance Report showing, per program: number of enrolled students, number passed, number failed, and average overall score; filterable by program.
3. THE System SHALL generate a Course Enrollment Report showing, per course: the program it belongs to, number of enrolled students, and average course aggregate; filterable by program.
4. THE System SHALL display all report data in paginated tables with 15 rows per page.
5. WHEN an Admin clicks "Export PDF" or "Export Excel" on a financial or performance report, THE System SHALL export the complete unfiltered dataset for the current filter selection (not just the current page).

---

### Requirement 9: Admin Controls

**User Story:** As an Admin, I want full control over program ordering, fee configuration, and all platform data, so that I can manage the college without developer intervention.

#### Acceptance Criteria

1. THE System SHALL provide an Admin-only Program Management page where the Admin can create, edit, delete, and reorder Programs by setting the `sequence` value.
2. THE System SHALL provide an Admin-only Fee Configuration page where the Admin can set or update the fee amount for each Program.
3. THE System SHALL restrict all Admin routes to users with `role = 'admin'` using the existing `EnsureUserHasRole` middleware.
4. THE System SHALL provide an Admin interface to update a student's status to any of: `active`, `graduated`, `suspended`, `manifestation`.
5. THE System SHALL provide an Admin interface to manually trigger a student promotion (bypassing the automatic pass-based trigger) with a confirmation dialog.
6. WHEN an Admin deletes a Program that has enrolled students, THE System SHALL prevent deletion and display an error "Cannot delete a program with enrolled students."
7. WHEN an Admin deletes a Course that has exam scores recorded, THE System SHALL prevent deletion and display an error "Cannot delete a course with recorded exam scores."

---

### Requirement 10: Clean Architecture

**User Story:** As a developer, I want the codebase to follow a service-oriented, slim-controller architecture, so that the system is maintainable and testable.

#### Acceptance Criteria

1. THE System SHALL implement a `PromotionService` class in `app/Services/PromotionService.php` encapsulating all program promotion logic, with no promotion logic residing in controllers.
2. THE System SHALL implement a `FeeCalculationService` class in `app/Services/FeeCalculationService.php` encapsulating all fee computation logic, with no fee calculation logic residing in controllers.
3. THE System SHALL implement a `ReportCardService` class in `app/Services/ReportCardService.php` encapsulating grade computation, position ranking, and aggregate calculation logic.
4. THE System SHALL use Laravel Form Request classes (in `app/Http/Requests/`) for all create and update validation, removing inline `$request->validate()` calls from controllers.
5. THE System SHALL define all Eloquent relationships explicitly on each model so that related data is always accessed via relationship methods rather than raw joins in controllers.
6. THE System SHALL register `PromotionService`, `FeeCalculationService`, and `ReportCardService` in the `AppServiceProvider` so they are resolved via Laravel's service container.
7. THE System SHALL keep all controller methods to a maximum of 20 lines by delegating business logic to service classes.
8. THE System SHALL define named routes for all new and updated endpoints following the convention `admin.{resource}.{action}`.
9. THE System SHALL use database transactions (via `DB::transaction`) in all controller methods that perform multiple write operations.
10. THE System SHALL include PHPDoc blocks on all public service class methods documenting parameters, return types, and thrown exceptions.

---

### Requirement 11: Action Button Visibility

**User Story:** As an Admin, I want primary action buttons to be immediately visible when I open a section, so that I can perform key actions without having to search for them.

#### Acceptance Criteria

1. WHEN the Admin navigates to the Students listing page, THE System SHALL display the "Add Student" button in a prominent, fixed position at the top of the page — visible without scrolling — using the gold primary colour (`#D4A017` / `bg-primary-600`) with a Font Awesome `fa-plus` icon.
2. WHEN the Admin navigates to the Attendance listing page, THE System SHALL display the "Mark Attendance" button in a prominent, fixed position at the top of the page — visible without scrolling — using the gold primary colour with a Font Awesome `fa-plus` icon.
3. THE System SHALL ensure that primary action buttons on listing pages are rendered above the data table or card list so that they are never obscured by content.
4. WHEN the student list is empty, THE System SHALL display an additional inline "Add First Student" call-to-action button within the empty-state area in addition to the top-of-page button.
5. WHEN the attendance list is empty, THE System SHALL display an additional inline "Mark First Attendance" call-to-action button within the empty-state area in addition to the top-of-page button.

---

### Requirement 12: Student Registration and Edit Form Field Updates

**User Story:** As an Admin, I want the student registration and edit forms to use accurate labels and only show relevant fields, so that data entry is clear and consistent with the college's terminology.

#### Acceptance Criteria

1. THE System SHALL display the label "Ministry Branch" (instead of "Church Branch") on both the Student Registration form and the Student Edit form for the field that maps to the `church_branch_id` column.
2. THE System SHALL remove the "Level" field from the Student Registration form so that no `level` input is presented to the Admin during student creation.
3. THE System SHALL remove the "Level" field from the Student Edit form so that no `level` input is presented to the Admin during student editing.
4. THE System SHALL include `manifestation` as a selectable option in the Status dropdown on the Student Edit form, in addition to the existing options: `active`, `graduated`, `suspended`.
5. THE System SHALL display the Status dropdown options in the following order: Active, Graduated, Suspended, Manifestation.
6. WHEN the student's current status is `manifestation`, THE System SHALL pre-select "Manifestation" in the Status dropdown when the edit form is loaded.
7. THE System SHALL apply the label change and field removal consistently across all form views (create and edit) without altering the underlying database column name (`church_branch_id`).

---

### Requirement 13: Clickable Student Rows and Comprehensive Student Profile

**User Story:** As an Admin, I want to click on any student row in the listing to open a full profile page, so that I can quickly access all information about a student in one place.

#### Acceptance Criteria

1. WHEN the Admin clicks on a student row (or the student's name) in the Students listing table or mobile card list, THE System SHALL navigate to that student's Profile page.
2. THE System SHALL render the student row and name as a visually interactive element (cursor pointer, hover highlight) to indicate it is clickable.
3. THE Student Profile page SHALL display all personal details: full name, student ID, registration number (if applicable), email, phone, address, date of birth, ministry branch, admission date, current program, and current status.
4. THE Student Profile page SHALL display a Fees Summary section showing: total fees billed, total amount paid, and outstanding balance, computed by the FeeCalculationService.
5. THE Student Profile page SHALL display an Exam Results / Report Cards section listing all programs for which the student has exam scores, with a link to view the full report card for each program.
6. THE Student Profile page SHALL display an Attendance Records section showing the student's attendance grouped by course, with present count, absent count, and attendance percentage per course.
7. THE Student Profile page SHALL display a Promotion History section showing each promotion event: from program, to program, and promotion date.
8. WHEN the student's status is `suspended` or `manifestation`, THE System SHALL display a prominent coloured status badge in the student profile header.
9. THE Student Profile page SHALL provide Edit and Promote action buttons accessible from the profile header without requiring the Admin to return to the listing page.

---

### Requirement 14: Global Student Search and Filter Consistency

**User Story:** As an Admin, I want a consistent student search and selection component available throughout the system, so that I can find and select a student by name, student ID, or registration number in any context.

#### Acceptance Criteria

1. THE System SHALL provide a reusable Student Search component that allows searching by: full name (partial match), student ID (exact or partial match), and registration number (exact or partial match).
2. WHEN the Admin types at least 2 characters into the Student Search component, THE System SHALL display a filtered dropdown list of matching students showing each student's full name, student ID, and program.
3. THE System SHALL use the same Student Search component consistently across all contexts where a student must be selected, including: exam score entry, payment recording, attendance marking, and report filtering.
4. WHEN no students match the search query, THE System SHALL display a "No students found" message within the dropdown.
5. WHEN the Admin selects a student from the dropdown, THE System SHALL populate the relevant hidden or visible form field with the selected student's ID and display the student's name as a confirmation.
6. THE Student Search component SHALL be keyboard-navigable: the Admin SHALL be able to use arrow keys to move through results and Enter to select.
7. IF the search query is cleared, THEN THE System SHALL reset the student selection and clear any dependent fields.
8. THE System SHALL perform student search filtering on the server side to support large student populations without degrading browser performance.

---

### Requirement 15: Report Preview, Download, and Print

**User Story:** As an Admin, I want to preview, download, and print every report type directly from the browser, so that I can review data on screen and produce physical or digital copies without leaving the system.

#### Acceptance Criteria

1. THE System SHALL provide Preview (view in browser), Download PDF, Download Excel, and Print actions for every report type in the Reports section: Students Report, Attendance Report, Financial Report, Program Performance Report, Course Enrollment Report, and Report Cards.
2. WHEN the Admin clicks "Preview" on any report, THE System SHALL render the filtered report data in a full-page browser view formatted for on-screen reading, without triggering a file download.
3. WHEN the Admin clicks "Download PDF" on any report, THE System SHALL generate a PDF of the complete filtered dataset using DomPDF and return it as a downloadable file with a descriptive filename (e.g., `students_report_2025.pdf`, `attendance_report_2025.pdf`).
4. WHEN the Admin clicks "Download Excel" on any report, THE System SHALL generate an Excel file of the complete filtered dataset using Maatwebsite Excel and return it as a downloadable `.xlsx` file with a descriptive filename.
5. WHEN the Admin clicks "Print" on any report, THE System SHALL open the browser print dialog with a print-optimised CSS stylesheet applied so that navigation, sidebars, and action buttons are hidden in the printed output.
6. THE System SHALL export the complete dataset matching the current filter selection for PDF and Excel downloads — not just the records visible on the current pagination page.
7. THE System SHALL display the four action buttons (Preview, Download PDF, Download Excel, Print) consistently on every individual report page using the same visual style and placement.
8. WHEN a report has no data matching the current filters, THE System SHALL still allow the Preview and Print actions, displaying an appropriate "No data found" message, and SHALL disable the Download PDF and Download Excel buttons with a tooltip explaining that there is no data to export.
9. THE Report Card PDF SHALL use the existing branded DomPDF template (`resources/views/pdf/report_card.blade.php`) with the gold header, black borders, and KMC school name and subtitle.
10. WHEN the Admin clicks "Download Excel" on the Report Cards section, THE System SHALL export all exam score rows for the selected student and program using the existing `ReportCardExport` class.
