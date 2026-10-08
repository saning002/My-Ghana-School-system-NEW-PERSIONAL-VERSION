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

        return view('owner.school-login', compact('tenant'));
    }

    public function login(Request $request, string $slug)
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

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Switch to this tenant's database
        $this->switchToTenant($tenant);

        // Attempt authentication against tenant's DB
        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            // Switch back to central DB
            $this->resetDatabase();

            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $user = Auth::user();

        // Only admins and lecturers can log in
        if (!in_array($user->role, ['admin', 'lecturer']) && !$user->isSuperAdmin()) {
            Auth::logout();
            $this->resetDatabase();

            throw ValidationException::withMessages([
                'email' => 'Access restricted to administrators and lecturers.',
            ]);
        }

        $request->session()->regenerate();

        // Store tenant slug in session — TenantMiddleware reads this on every request
        session(['tenant_slug' => $tenant->slug, 'tenant_id' => $tenant->id]);

        // Reset to central DB after auth — TenantMiddleware will re-switch per request
        $this->resetDatabase();

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
        DB::setDefaultConnection('mysql');
        DB::purge('tenant');
    }
}
