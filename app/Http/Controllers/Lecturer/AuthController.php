<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the lecturer login form
     */
    public function loginForm()
    {
        $teachersPortalOpen    = Setting::teachersPortalIsOpen();
        $teachersPortalMessage = Setting::get('teachers_portal_message', 'The teachers portal is currently closed.');

        if (Auth::check() && Auth::user()->role === 'lecturer') {
            return redirect()->route('lecturer.dashboard');
        }

        return view('lecturer.login', compact('teachersPortalOpen', 'teachersPortalMessage'));
    }

    /**
     * Handle lecturer login
     *
     * Uses manual user lookup + Auth::login() instead of Auth::attempt()
     * to ensure the query hits the tenant DB, not the central DB.
     * Same pattern as SchoolAccessController::login which works reliably.
     */
    public function login(Request $request)
    {
        // Check if teacher portal is closed
        if (!Setting::teachersPortalIsOpen()) {
            return back()->with('portal_closed', true)->withErrors([
                'email' => Setting::get('teachers_portal_message', 'The teachers portal is currently closed.'),
            ]);
        }

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // ── Ensure we're on the tenant DB ────────────────────────────────
        // TenantMiddleware should have already switched, but as a fallback,
        // resolve the tenant from session or ?tenant= query param and switch.
        $tenantSlug = session('tenant_slug') ?? $request->query('tenant');
        if ($tenantSlug && DB::getDefaultConnection() !== 'tenant') {
            $tenant = Tenant::on('pgsql')->where('slug', $tenantSlug)->first();
            if ($tenant) {
                DB::purge('tenant');
                Config::set('database.connections.tenant', $tenant->dbConfig());
                DB::setDefaultConnection('tenant');
                session(['tenant_slug' => $tenant->slug, 'tenant_id' => $tenant->id]);
            }
        }

        // ── Manual user lookup on tenant DB ──────────────────────────────
        $user = User::where('email', $request->input('email'))->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        // Ensure user is a lecturer
        if ($user->role !== 'lecturer') {
            throw ValidationException::withMessages([
                'email' => 'Access restricted to lecturers only.',
            ]);
        }

        // ── Log in WITHOUT Auth::login() ────────────────────────────────────
        // Auth::login() internally calls session()->migrate(true) which
        // regenerates the session ID. This causes the session to be saved
        // with a new ID, and the cookie is updated. But something in the
        // process causes the auth state to be lost on the next request.
        // Instead, manually store the user ID in the session using the
        // same key that Auth::user() looks for.
        $authKey = Auth::getName();  // e.g. 'login_web_XXXXX'
        session()->put($authKey, $user->id);

        // Restore tenant slug so TenantMiddleware switches DB on next request
        $tenantId = session('tenant_id');
        session(['tenant_slug' => $tenantSlug, 'tenant_id' => $tenantId]);

        return redirect('/lecturer/dashboard' . ($tenantSlug ? '?tenant=' . $tenantSlug : ''));
    }

    /**
     * Handle lecturer logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('lecturer.login');
    }
}
