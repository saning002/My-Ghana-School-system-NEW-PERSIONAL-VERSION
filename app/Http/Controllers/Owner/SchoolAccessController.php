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

        // Attempt authentication against tenant's Neon DB
        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $this->resetDatabase();
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $user      = Auth::user();
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

        // All good — regenerate session and store tenant context
        $request->session()->regenerate();
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
