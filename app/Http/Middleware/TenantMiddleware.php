<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Never run tenant switching on owner panel routes
        if ($request->is('owner/*') || $request->is('owner')) {
            return $next($request);
        }

        $tenant = $this->resolveTenant($request);

        // No tenant found — if this is an admin/lecturer route, allow through
        // (they may be using the legacy direct login without a school slug)
        // If this is a school/website route expecting a tenant, show not-found
        if (!$tenant) {
            $isSchoolRoute = $request->is('school/*')
                || $request->is('website/*')
                || $request->is('portal/*');

            if ($isSchoolRoute) {
                return response()->view('tenant.not-found', [], 404);
            }

            // Admin/lecturer routes without tenant context — pass through
            // (direct login case — no DB switching needed)
            return $next($request);
        }

        // Store tenant on the request so controllers/views can access it
        $request->merge(['_tenant' => $tenant]);
        app()->instance('tenant', $tenant);

        // Always persist tenant in session so subsequent requests
        // (like login form POST) have the tenant context.
        // This is critical: resolveTenant may find the tenant via ?tenant= query
        // param or subdomain, but without setting the session here, the next
        // request won't have the tenant context and will hit the central DB.
        session(['tenant_slug' => $tenant->slug, 'tenant_id' => $tenant->id]);

        // Check status before switching DB
        if ($tenant->isSuspended()) {
            return response()->view('tenant.suspended', compact('tenant'), 403);
        }

        if ($tenant->isExpired()) {
            $tenant->update(['status' => 'expired']);
            return response()->view('tenant.expired', compact('tenant'), 403);
        }

        // Switch database connection to this tenant's DB
        $this->switchDatabase($tenant);

        // Share tenant with all Blade views
        view()->share('currentTenant', $tenant);

        // ── Check if the requested route's module is enabled ─────────────────
        $enabledModules = $tenant->modules_json ?? null;
        if ($enabledModules !== null) {
            $routeModule = $this->routeToModule($request);
            if ($routeModule && !in_array($routeModule, $enabledModules, true)) {
                return response()->view('tenant.module-disabled', [
                    'tenant' => $tenant,
                    'module' => $routeModule,
                ], 403);
            }
        }

        // Share tenant with all Blade views
        view()->share('currentTenant', $tenant);

        // ── Share enabled modules so sidebar/views can hide disabled items ──
        $enabledModules = $tenant->modules_json ?? [];
        // If cache is empty (new tenant or pre-cache), treat all as enabled
        // so nothing is accidentally hidden before owner has configured modules
        if (empty($enabledModules)) {
            $enabledModules = null; // null = all enabled (no restriction)
        }
        view()->share('enabledModules', $enabledModules);

        // Helper: moduleEnabled($key) — true if null (all) or key in array
        view()->composer('*', function ($view) use ($enabledModules) {
            $view->with('_moduleEnabled', function (string $key) use ($enabledModules): bool {
                if ($enabledModules === null) return true;
                return in_array($key, $enabledModules, true);
            });
        });

        // ── Apply owner-set admin theme to the tenant's site_settings ────────
        // If owner set a theme_json on the tenant, it takes precedence
        if (!empty($tenant->theme_json)) {
            app()->instance('owner_theme_override', $tenant->theme_json);
        }

        // ── Read school settings from the NOW-CORRECT tenant DB ──────────
        // The AppServiceProvider::boot() ran before the session was available
        // and read from the central DB. Override those shared variables here
        // so the admin panel shows the correct school's name, logo, etc.
        try {
            $schoolName     = Setting::get('school_name', config('app.name'));
            $schoolSubtitle = Setting::get('school_subtitle', '');
            $storedLogo     = Setting::get('site_logo_url');

            $logoUrl = null;
            if ($storedLogo) {
                $logoUrl = filter_var($storedLogo, FILTER_VALIDATE_URL)
                    ? $storedLogo
                    : \Illuminate\Support\Facades\Storage::disk('public')->url($storedLogo);
            }

            $sidebarLabels = [
                'students'   => Setting::get('sidebar_students_label', 'Students'),
                'lecturers'  => Setting::get('sidebar_lecturers_label', 'Teachers'),
                'teachers'   => Setting::get('sidebar_teachers_label', 'Teachers'),
                'programs'   => Setting::get('sidebar_programs_label', 'Programs'),
                'courses'    => Setting::get('sidebar_courses_label', 'Courses'),
                'attendance' => Setting::get('sidebar_attendance_label', 'Attendance'),
                'exams'      => Setting::get('sidebar_exams_label', 'Exams & Results'),
                'fees'       => Setting::get('sidebar_fees_label', 'Fees & Payments'),
                'reports'    => Setting::get('sidebar_reports_label', 'Reports'),
                'portals'    => Setting::get('sidebar_portals_label', 'Student Portal'),
                'settings'   => Setting::get('sidebar_settings_label', 'Settings'),
                'guide'      => Setting::get('sidebar_guide_label', 'Admin Guide'),
            ];

            $programLabel = $sidebarLabels['programs'] ?? 'Programs';
            $programLabelSingular = \Illuminate\Support\Str::singular($programLabel);

            view()->share('schoolName', $schoolName);
            view()->share('schoolSubtitle', $schoolSubtitle);
            view()->share('siteLogoUrl', $logoUrl);
            view()->share('sidebarLabels', $sidebarLabels);
            view()->share('programLabel', $programLabel);
            view()->share('programLabelSingular', $programLabelSingular);
        } catch (\Throwable $e) {
            // Keep the defaults from AppServiceProvider if tenant DB fails
        }

        $response = $next($request);

        // Reset to central DB after request
        $this->resetDatabase();

        return $response;
    }

    private function resolveTenant(Request $request): ?Tenant
    {
        // IMPORTANT: Tenant model must ALWAYS query the central DB (pgsql)
        // Never query it on the tenant connection — tenants table doesn't exist there

        // 1. Session-based (Option A — slug login at /school/{slug}/login)
        $sessionSlug = session('tenant_slug');
        if ($sessionSlug) {
            // Force central DB for this query
            $found = Tenant::on('pgsql')->where('slug', $sessionSlug)->first();
            if ($found) return $found;
            // If session slug is stale/invalid, fall through to other methods
        }

        // 1b. URL path-based — extract slug from /school/{slug}/...
        // This runs BEFORE the route handler, so session hasn't been set yet
        $path = $request->path();
        if (preg_match('#^school/([^/]+)#', $path, $m)) {
            $urlSlug = $m[1];
            $tenant = Tenant::on('pgsql')->where('slug', $urlSlug)
                ->orWhere('subdomain', $urlSlug)
                ->first();
            if ($tenant) {
                // Store in session so all subsequent requests (portal logins, etc.)
                // have the tenant context without needing the slug in the URL
                session(['tenant_slug' => $tenant->slug, 'tenant_id' => $tenant->id]);
                return $tenant;
            }
        }

        // 2. Try subdomain — e.g. "accra-academy" from "accra-academy.yourdomain.com"
        $host      = $request->getHost();
        $appDomain = config('app.domain', parse_url(config('app.url'), PHP_URL_HOST));

        if ($appDomain && str_ends_with($host, '.' . $appDomain)) {
            $subdomain = str_replace('.' . $appDomain, '', $host);
            if ($subdomain && $subdomain !== $appDomain) {
                return Tenant::on('pgsql')->where('subdomain', $subdomain)->first();
            }
        }

        // 3. Try X-Tenant-Slug header
        $slug = $request->header('X-Tenant-Slug');
        if ($slug) {
            return Tenant::on('pgsql')->where('slug', $slug)->first();
        }

        // 4. Try ?tenant= query param
        $tenantParam = $request->query('tenant');
        if ($tenantParam) {
            return Tenant::on('pgsql')->where('slug', $tenantParam)->orWhere('subdomain', $tenantParam)->first();
        }

        return null;
    }

    /**
     * Dynamically configure and switch to the tenant's database.
     */
    private function switchDatabase(Tenant $tenant): void
    {
        // Purge any existing tenant connection so it reconnects fresh
        DB::purge('tenant');

        Config::set('database.connections.tenant', $tenant->dbConfig());

        // Set tenant as the default connection for this request
        DB::setDefaultConnection('tenant');

        // Configure school's own Cloudinary if credentials are set
        if ($tenant->cloudinary_cloud_name && $tenant->cloudinary_api_key && $tenant->cloudinary_api_secret) {
            Config::set('cloudinary.cloud_url',
                "cloudinary://{$tenant->cloudinary_api_key}:{$tenant->cloudinary_api_secret}@{$tenant->cloudinary_cloud_name}"
            );
            Config::set('cloudinary.cloud_name', $tenant->cloudinary_cloud_name);
            Config::set('cloudinary.api_key',    $tenant->cloudinary_api_key);
            Config::set('cloudinary.api_secret', $tenant->cloudinary_api_secret);
        }

        // Configure school's own Backblaze B2 if credentials are set
        if ($tenant->b2_key_id && $tenant->b2_application_key && $tenant->b2_bucket_name) {
            Config::set('filesystems.disks.b2', [
                'driver'   => 's3',
                'key'      => $tenant->b2_key_id,
                'secret'   => $tenant->b2_application_key,
                'region'   => 'us-west-004',
                'bucket'   => $tenant->b2_bucket_name,
                'url'      => $tenant->b2_endpoint ?? '',
                'endpoint' => $tenant->b2_endpoint ?? '',
            ]);
            Config::set('filesystems.default', 'b2');
        }
    }

    /**
     * Reset back to the central (default) connection.
     */
    private function resetDatabase(): void
    {
        // Always reset to pgsql (the central DB) — never mysql
        DB::setDefaultConnection('pgsql');
        DB::purge('tenant');
    }

    /**
     * Map an incoming route name to its module key.
     * Returns null if the route doesn't belong to a gated module.
     */
    private function routeToModule(\Illuminate\Http\Request $request): ?string
    {
        $route = $request->route()?->getName() ?? '';

        $map = [
            'admin.students'         => 'students',
            'admin.lecturers'        => 'teachers',
            'admin.programs'         => 'programs',
            'admin.courses'          => 'courses',
            'admin.attendance'       => 'attendance',
            'admin.exams'            => 'exams',
            'admin.academic-sessions'=> 'academic_sessions',
            'admin.timetable'        => 'timetable',
            'admin.scheme-of-learning'=> 'scheme_of_learning',
            'admin.calendar'         => 'calendar',
            'admin.student-reports'  => 'student_reports',
            'admin.fees'             => 'fees',
            'admin.fees.term'        => 'fees',
            'admin.fees.reminders'   => 'fee_reminders',
            'admin.daily-fees'       => 'daily_fees',
            'admin.reports'          => 'reports',
            'admin.notifications'    => 'notifications',
            'admin.portal-settings'  => 'portals',
            'admin.staff-portal'     => 'staff_portal',
            'admin.website'          => 'website',
            'admin.work-monitoring'  => 'teachers',
            'admin.class-teachers'   => 'teachers',
            'admin.exam-questions'   => 'exams',
            'student.'               => 'portals',
            'website.'               => 'website',
        ];

        foreach ($map as $prefix => $module) {
            if (str_starts_with($route, $prefix)) {
                return $module;
            }
        }

        return null; // dashboard, settings, profile — always accessible
    }
}
