<?php

namespace App\Providers;

use App\Models\Setting;
use App\Services\FeeCalculationService;
use App\Services\PromotionService;
use App\Services\ReportCardService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PromotionService::class);
        $this->app->singleton(FeeCalculationService::class);
        $this->app->singleton(ReportCardService::class);

        // ReportService needs ReportCardService injected — register it explicitly
        $this->app->singleton(\App\Services\ReportService::class, function ($app) {
            return new \App\Services\ReportService(
                $app->make(ReportCardService::class)
            );
        });

        // DocumentVerificationService — simple singleton
        $this->app->singleton(\App\Services\DocumentVerificationService::class);
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        $schoolName     = config('app.name', 'School');
        $schoolSubtitle = '';
        $logoUrl        = asset('images/logo.png');
        $sidebarLabels = [
            'students'   => 'Students',
            'lecturers'  => 'Lecturers',
            'teachers'   => 'Teachers',
            'programs'   => 'Programs',
            'courses'    => 'Courses',
            'attendance' => 'Attendance',
            'exams'      => 'Exams & Results',
            'fees'       => 'Fees & Payments',
            'reports'    => 'Reports',
            'portals'    => 'Portals',
            'settings'   => 'Settings',
            'guide'      => 'Admin Guide',
        ];

        try {
            if (Schema::hasTable('settings')) {
                $schoolName = Setting::get('school_name', $schoolName);
                $schoolSubtitle = Setting::get('school_subtitle', $schoolSubtitle);

                $storedLogo = Setting::get('site_logo_url');
                if ($storedLogo) {
                    $logoUrl = filter_var($storedLogo, FILTER_VALIDATE_URL)
                        ? $storedLogo
                        : Storage::disk('public')->url($storedLogo);
                }

            $sidebarLabels = [
                'students'   => Setting::get('sidebar_students_label', $sidebarLabels['students']),
                'lecturers'  => Setting::get('sidebar_lecturers_label', $sidebarLabels['lecturers']),
                'teachers'   => Setting::get('sidebar_teachers_label', $sidebarLabels['teachers']),
                'programs'   => Setting::get('sidebar_programs_label', $sidebarLabels['programs']),
                'courses'    => Setting::get('sidebar_courses_label', $sidebarLabels['courses']),
                'attendance' => Setting::get('sidebar_attendance_label', $sidebarLabels['attendance']),
                'exams'      => Setting::get('sidebar_exams_label', $sidebarLabels['exams']),
                'fees'       => Setting::get('sidebar_fees_label', $sidebarLabels['fees']),
                'reports'    => Setting::get('sidebar_reports_label', $sidebarLabels['reports']),
                'portals'    => Setting::get('sidebar_portals_label', $sidebarLabels['portals']),
                'settings'   => Setting::get('sidebar_settings_label', $sidebarLabels['settings']),
                'guide'      => Setting::get('sidebar_guide_label', $sidebarLabels['guide']),
            ];
            }
        } catch (\Throwable $e) {
            // Ignore boot-time settings errors and continue with defaults.
        }

        $programLabel = $sidebarLabels['programs'] ?? 'Programs';
        $programLabelSingular = \Illuminate\Support\Str::singular($programLabel);

        View::share('schoolName', $schoolName);
        View::share('schoolSubtitle', $schoolSubtitle);
        View::share('siteLogoUrl', $logoUrl);
        View::share('sidebarLabels', $sidebarLabels);
        View::share('programLabel', $programLabel);
        View::share('programLabelSingular', $programLabelSingular);
    }
}
