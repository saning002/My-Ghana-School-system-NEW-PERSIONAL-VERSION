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
            // NOTE: AppServiceProvider runs before TenantMiddleware and before
            // the session is available. We deliberately do NOT read from the
            // settings table here — TenantMiddleware overrides these View::share
            // variables with the correct tenant DB values after switching DBs.
            // Reading here would use the central DB and show wrong school data.
            // Keep this block empty / with defaults only.
            if (false) { // disabled — TenantMiddleware handles this per-request
                $schoolName = Setting::get('school_name', $schoolName);
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
