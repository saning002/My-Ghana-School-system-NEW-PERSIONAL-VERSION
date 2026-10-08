# Bugfix Requirements Document

## Introduction

The student portal's "Download PDF" button for report cards is broken for all students. When a student clicks the button (from either the dashboard results section or the report card view), a new browser tab opens and displays the plain-text error message "PDF generation failed. Please contact support." instead of downloading a PDF file. This affects every student on every program, every time the button is clicked.

The root cause is in `app/Http/Controllers/Student/PortalController.php` in the `reportCardPdf()` method. The `setOptions()` call passes option keys using the old/incorrect naming convention (`isHtml5ParserEnabled`, `isRemoteEnabled`) that are not recognised by `barryvdh/laravel-dompdf` v3, which expects the underlying dompdf option names (`enable_html5_parser`, `enable_remote`). The unrecognised options cause DomPDF to throw an exception, which is caught by the surrounding `try/catch` block and returned as the generic error response.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN a student clicks the "Download PDF" button for any report card THEN the system opens a new browser tab displaying the plain-text message "PDF generation failed. Please contact support." instead of downloading a PDF

1.2 WHEN the `reportCardPdf()` controller method calls `Pdf::loadView(...)->setOptions([...])` with the option keys `isHtml5ParserEnabled` and `isRemoteEnabled` THEN the system throws an exception because those key names are not valid for `barryvdh/laravel-dompdf` v3

1.3 WHEN the PDF generation exception is thrown THEN the system returns an HTTP 500 plain-text response instead of a downloadable PDF file

### Expected Behavior (Correct)

2.1 WHEN a student clicks the "Download PDF" button for a report card that has scores THEN the system SHALL generate and download a valid PDF file named `report_card_{student_id}_{program-slug}.pdf`

2.2 WHEN the `reportCardPdf()` controller method configures DomPDF options THEN the system SHALL use the correct option key names recognised by `barryvdh/laravel-dompdf` v3: `enable_html5_parser` instead of `isHtml5ParserEnabled`, and `enable_remote` instead of `isRemoteEnabled`

2.3 WHEN the PDF is generated successfully THEN the system SHALL return a file download response with the correct `Content-Type: application/pdf` header

### Unchanged Behavior (Regression Prevention)

3.1 WHEN a student views the report card page (HTML view, not PDF) THEN the system SHALL CONTINUE TO display the report card correctly without errors

3.2 WHEN a student logs into the portal and views the dashboard THEN the system SHALL CONTINUE TO load the dashboard with the report card preview without errors

3.3 WHEN a student's report card has no scores (`has_scores` is false) THEN the system SHALL CONTINUE TO hide the "Download PDF" button and not attempt PDF generation

3.4 WHEN an admin generates a PDF report card via the admin exam routes (`/admin/exams/{student}/{program}/pdf`) THEN the system SHALL CONTINUE TO work correctly and be unaffected by this fix

3.5 WHEN any other PDF export in the admin panel (financial, students, attendance, programs, courses) is triggered THEN the system SHALL CONTINUE TO generate and download correctly without errors

3.6 WHEN the portal middleware checks authentication and portal-open status THEN the system SHALL CONTINUE TO redirect unauthenticated or portal-closed requests to the login page
