<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChurchBranch;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminManagementController extends Controller
{
    private function ensureSuperAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized Access. Super admin only.');
        }
    }

    /**
     * Ensure we are connected to the tenant DB before any admin operation.
     * When a school admin is logged in via /school/{slug}/login, the session
     * has tenant_slug set and TenantMiddleware switches the DB automatically.
     * This is a safety guard for cases where the connection may have reset.
     */
    private function ensureTenantDb(): void
    {
        $slug = session('tenant_slug');
        if (!$slug) return; // Not in tenant context — skip

        $currentDb = \Illuminate\Support\Facades\DB::getDefaultConnection();
        if ($currentDb !== 'tenant') {
            // Re-switch to tenant DB
            $tenant = \App\Models\Tenant::where('slug', $slug)->first();
            if ($tenant) {
                \Illuminate\Support\Facades\DB::purge('tenant');
                \Illuminate\Support\Facades\Config::set('database.connections.tenant', $tenant->dbConfig());
                \Illuminate\Support\Facades\DB::setDefaultConnection('tenant');
            }
        }
    }

    public function index()
    {
        $this->ensureSuperAdmin();
        $this->ensureTenantDb();

        $admins = User::where('role', 'admin')
            ->with('branch')
            ->orderBy('is_super_admin', 'desc')
            ->orderBy('id')
            ->paginate(15);

        return view('admin.admins.index', compact('admins'));
    }

    public function create()
    {
        $this->ensureSuperAdmin();
        $this->ensureTenantDb();
        $branches = ChurchBranch::all();
        return view('admin.admins.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $this->ensureSuperAdmin();
        $this->ensureTenantDb();

        $validated = $request->validate([
            'full_name'        => 'required|string|max:255',
            'email'            => 'required|email',
            'password'         => 'required|string|min:6|confirmed',
            'phone'            => 'nullable|string|max:20',
            'church_branch_id' => 'nullable',
            'is_super_admin'   => 'nullable|boolean',
        ]);

        // Check email uniqueness on the correct connection
        $emailExists = User::where('email', $validated['email'])->exists();
        if ($emailExists) {
            return back()->withErrors(['email' => 'An admin with this email already exists.'])->withInput();
        }

        User::create([
            'full_name'        => $validated['full_name'],
            'email'            => $validated['email'],
            'password'         => Hash::make($validated['password']),
            'phone'            => $validated['phone'] ?? null,
            'role'             => 'admin',
            'is_super_admin'   => $request->boolean('is_super_admin', false),
            'church_branch_id' => $request->filled('church_branch_id') ? $validated['church_branch_id'] : null,
        ]);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Administrator created successfully.');
    }

    public function edit(User $admin)
    {
        $this->ensureSuperAdmin();
        $this->ensureTenantDb();

        if ($admin->role !== 'admin') {
            abort(404);
        }

        $branches = ChurchBranch::all();
        return view('admin.admins.edit', compact('admin', 'branches'));
    }

    public function update(Request $request, User $admin)
    {
        $this->ensureSuperAdmin();
        $this->ensureTenantDb();

        if ($admin->role !== 'admin') {
            abort(404);
        }

        $validated = $request->validate([
            'full_name'        => 'required|string|max:255',
            'email'            => 'required|email',
            'phone'            => 'nullable|string|max:20',
            'church_branch_id' => 'nullable',
            'is_super_admin'   => 'nullable|boolean',
            'password'         => 'nullable|string|min:6|confirmed',
        ]);

        // Prevent removing own super admin status
        if ($admin->id === auth()->id() && !$request->boolean('is_super_admin')) {
            return back()->with('error', 'You cannot remove your own Super Admin privileges.');
        }

        $updates = [
            'full_name'        => $validated['full_name'],
            'email'            => $validated['email'],
            'phone'            => $validated['phone'] ?? null,
            'is_super_admin'   => $request->boolean('is_super_admin', false),
            'church_branch_id' => $request->filled('church_branch_id') ? $validated['church_branch_id'] : null,
        ];

        if ($request->filled('password')) {
            $updates['password'] = Hash::make($validated['password']);
        }

        $admin->update($updates);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Administrator updated successfully.');
    }

    public function destroy(User $admin)
    {
        $this->ensureSuperAdmin();
        $this->ensureTenantDb();

        if ($admin->role !== 'admin') {
            abort(404);
        }

        // Prevent self-deletion
        if ($admin->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Allow deleting super admins as long as at least one remains after deletion
        if ($admin->isSuperAdmin()) {
            $superAdminCount = User::where('role', 'admin')
                ->where('is_super_admin', true)
                ->count();

            if ($superAdminCount <= 1) {
                return back()->with('error', 'Cannot delete the last Super Admin. Create another Super Admin first.');
            }
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', 'Administrator deleted successfully.');
    }
}
