<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * SchoolAccessController
 * ----------------------
 * Handles slug-based school login for Option A (single Render URL, multiple schools).
 * Each school accesses: /school/{slug}/login
 *
 * On login:
 *  1. Resolves the tenant from the slug
 *  2. Switches DB connection to the tenant's database
 *  3. Authenticates the user against that database
 *  4. Stores tenant slug in session for all subsequent requests
 */
class SchoolAccessController extends Controller
{
    public function showAdminLogin(string $slug)
    {
        $tenant = Tenant::where('slug', $slug)->orWhere('subdomain', $slug)->first();
        if (!$tenant) abort(404);
        if ($tenant->isSuspended()) return view('tenant.suspended', compact('tenant'));
        if ($tenant->isExpired())   return view('tenant.expired',    compact('tenant'));
        session(['tenant_slug' => $tenant->slug, 'tenant_id' => $tenant->id]);
        return view('owner.school-login', ['tenant' => $tenant, 'role' => 'admin']);
    }

    public function showLecturerLogin(string $slug)
    {
        $tenant = Tenant::where('slug', $slug)->orWhere('subdomain', $slug)->first();
        if (!$tenant) abort(404);
        if ($tenant->isSuspended()) return view('tenant.suspended', compact('tenant'));
        if ($tenant->isExpired())   return view('tenant.expired',    compact('tenant'));
        session(['tenant_slug' => $tenant->slug, 'tenant_id' => $tenant->id]);
        return view('owner.school-login', ['tenant' => $tenant, 'role' => 'lecturer']);
    }

    /**
     * Redirect to a portal login page after setting the tenant session.
     * This ensures TenantMiddleware switches to the tenant DB for portal logins.
     *
     * Usage:
     *   /school/{slug}/student-portal  → redirect to /student-portal/login?tenant={slug}
     *   /school/{slug}/teachers-portal → redirect to /teachers-portal/login?tenant={slug}
     *   /school/{slug}/staff-portal/{role?} → redirect to /staff-portal/login/{role}?tenant={slug}
     */
    public function portalRedirect(string $slug, string $portal)
    {
        // Use tenant from app container — TenantMiddleware already resolved it
        // Do NOT query Tenant::where() here because the DB may already be
        // switched to the tenant connection where the tenants table doesn't exist
        $tenant = app('tenant');
        if (!$tenant) {
            $tenant = Tenant::on('pgsql')->where('slug', $slug)->orWhere('subdomain', $slug)->first();
        }
        if (!$tenant) abort(404, 'School not found.');

        // Set tenant in session so TenantMiddleware switches DB for all portal pages
        session(['tenant_slug' => $tenant->slug, 'tenant_id' => $tenant->id]);

        // Add ?tenant= as a backup so even if session is lost, the next request
        // can still resolve the tenant from the query param
        $tenantParam = '?tenant=' . $tenant->slug;

        return match($portal) {
            'student'    => redirect('/student-portal/login' . $tenantParam),
            'teacher'    => redirect('/teachers-portal/login' . $tenantParam),
            'staff'      => redirect('/staff-portal/login' . $tenantParam),
            default      => redirect('/student-portal/login' . $tenantParam),
        };
    }

    public function showLogin(string $slug)
    {
        $tenant = Tenant::where('slug', $slug)
            ->orWhere('subdomain', $slug)
            ->first();

        if (!$tenant) {
            abort(404, 'School not found.');
        }

        if ($tenant->isSuspended()) {
            return view('tenant.suspended', compact('tenant'));
        }

        if ($tenant->isExpired()) {
            return view('tenant.expired', compact('tenant'));
        }

        // Store tenant in session before redirecting to website
        // so the website knows which school's content to show
        session(['tenant_slug' => $tenant->slug, 'tenant_id' => $tenant->id]);

        // Redirect to the school's public website portal page
        // which has links to admin, teacher and student login
        return redirect()->route('website.portal');
    }

    public function login(Request $request, string $slug)
    {
        $tenant = Tenant::where('slug', $slug)
            ->orWhere('subdomain', $slug)
            ->first();

        if (!$tenant) abort(404, 'School not found.');
        if ($tenant->isSuspended()) return view('tenant.suspended', compact('tenant'));
        if ($tenant->isExpired())   return view('tenant.expired',   compact('tenant'));

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Switch to tenant DB — must stay switched through the whole login
        $this->switchToTenant($tenant);

        // Auth::attempt() uses its own cached connection internally.
        // Bypass it — find the user manually from the tenant DB, verify password,
        // then log in using Auth::login() which doesn't need a DB call.
        $userRecord = \App\Models\User::where('email', $request->input('email'))->first();

        if (!$userRecord || !\Illuminate\Support\Facades\Hash::check($request->input('password'), $userRecord->password)) {
            $this->resetDatabase();
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        // Log in the user directly — no DB query needed
        Auth::login($userRecord, $request->boolean('remember'));

        $user      = $userRecord;
        $loginRole = $request->input('login_role', 'superadmin');

        // Validate role matches selected tab
        $roleError = null;

        if ($loginRole === 'lecturer' && $user->role !== 'lecturer') {
            $roleError = 'This account is not a Lecturer account. Try the Admin or Super Admin tab.';
        } elseif ($loginRole === 'superadmin' && !$user->isSuperAdmin()) {
            $roleError = 'This account is not a Super Admin. Try the Admin tab instead.';
        } elseif ($loginRole === 'admin' && $user->role !== 'admin') {
            $roleError = 'This account is not an Admin account.';
        } elseif (!in_array($user->role, ['admin', 'lecturer']) && !$user->isSuperAdmin()) {
            $roleError = 'Access restricted to administrators and lecturers only.';
        }

        if ($roleError) {
            Auth::logout();
            $this->resetDatabase();
            throw ValidationException::withMessages(['email' => $roleError]);
        }

        // All good — store tenant BEFORE session regeneration so it survives
        session(['tenant_slug' => $tenant->slug, 'tenant_id' => $tenant->id]);

        // Regenerate session (preserves existing session data including tenant_slug)
        $request->session()->regenerate();

        // Set tenant_slug again after regenerate to be safe
        session(['tenant_slug' => $tenant->slug, 'tenant_id' => $tenant->id]);

        // Keep tenant DB active — TenantMiddleware will handle it on next request
        // Do NOT reset DB here as it would cause the redirect to lose auth context

        if ($user->role === 'lecturer') {
            return redirect()->route('lecturer.dashboard');
        }

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $tenantSlug = session('tenant_slug');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($tenantSlug) {
            return redirect()->route('school.login', $tenantSlug);
        }

        return redirect('/');
    }

    private function switchToTenant(Tenant $tenant): void
    {
        DB::purge('tenant');
        Config::set('database.connections.tenant', $tenant->dbConfig());
        DB::setDefaultConnection('tenant');
    }

    private function resetDatabase(): void
    {
        DB::setDefaultConnection('pgsql');
        DB::purge('tenant');
    }
}
