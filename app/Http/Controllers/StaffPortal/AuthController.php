<?php

namespace App\Http\Controllers\StaffPortal;

use App\Http\Controllers\Controller;
use App\Models\StaffPortalUser;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function loginForm(string $role = 'staff')
    {
        if (session()->has('staff_portal_user_id')) {
            return redirect()->route('staff-portal.dashboard');
        }
        return view('staff-portal.login', compact('role'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Ensure we're on the tenant DB
        $this->ensureTenantDb($request);

        $user = StaffPortalUser::where('email', $request->email)
            ->when($request->filled('role'), fn($q) => $q->where('role', $request->role))
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
        }

        if (! $user->is_active) {
            return back()->withErrors(['email' => 'This account has been deactivated. Contact the administrator.'])->withInput();
        }

        // Store in session
        session([
            'staff_portal_user_id'   => $user->id,
            'staff_portal_user_name' => $user->full_name,
            'staff_portal_user_role' => $user->role,
        ]);

        return redirect()->route('staff-portal.dashboard');
    }

    public function logout()
    {
        session()->forget(['staff_portal_user_id','staff_portal_user_name','staff_portal_user_role']);
        return redirect()->route('staff-portal.login');
    }

    private function ensureTenantDb(Request $request): void
    {
        if (DB::getDefaultConnection() === 'tenant') return;
        $slug = session('tenant_slug') ?? $request->query('tenant');
        if (!$slug) return;
        $tenant = Tenant::on('pgsql')->where('slug', $slug)->first();
        if (!$tenant) return;
        DB::purge('tenant');
        Config::set('database.connections.tenant', $tenant->dbConfig());
        DB::setDefaultConnection('tenant');
        session(['tenant_slug' => $tenant->slug, 'tenant_id' => $tenant->id]);
    }
}
