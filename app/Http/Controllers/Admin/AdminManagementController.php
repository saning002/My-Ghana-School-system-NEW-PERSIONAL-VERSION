<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChurchBranch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminManagementController extends Controller
{
    private function ensureSuperAdmin()
    {
        if (! auth()->check() || ! auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized Access. Super admin only.');
        }
    }

    public function index()
    {
        $this->ensureSuperAdmin();

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
        $branches = ChurchBranch::all();
        return view('admin.admins.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'full_name'        => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|string|min:6|confirmed',
            'phone'            => 'nullable|string|max:20',
            'church_branch_id' => 'nullable|exists:church_branches,id',
            'is_super_admin'   => 'nullable|boolean',
        ]);

        User::create([
            'full_name'        => $validated['full_name'],
            'email'            => $validated['email'],
            'password'         => Hash::make($validated['password']),
            'phone'            => $validated['phone'] ?? null,
            'role'             => 'admin',
            'is_super_admin'   => $request->boolean('is_super_admin'),
            'church_branch_id' => $request->filled('church_branch_id') ? $validated['church_branch_id'] : null,
        ]);

        return redirect()->route('admin.admins.index')->with('success', 'Administrator created successfully.');
    }

    public function edit(User $admin)
    {
        $this->ensureSuperAdmin();

        if ($admin->role !== 'admin') {
            abort(404);
        }

        $branches = ChurchBranch::all();
        return view('admin.admins.edit', compact('admin', 'branches'));
    }

    public function update(Request $request, User $admin)
    {
        $this->ensureSuperAdmin();

        if ($admin->role !== 'admin') {
            abort(404);
        }

        $validated = $request->validate([
            'full_name'        => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email,' . $admin->id,
            'phone'            => 'nullable|string|max:20',
            'church_branch_id' => 'nullable|exists:church_branches,id',
            'is_super_admin'   => 'nullable|boolean',
            'password'         => 'nullable|string|min:6|confirmed',
        ]);

        $updates = [
            'full_name'        => $validated['full_name'],
            'email'            => $validated['email'],
            'phone'            => $validated['phone'] ?? null,
            'is_super_admin'   => $request->boolean('is_super_admin'),
            'church_branch_id' => $request->filled('church_branch_id') ? $validated['church_branch_id'] : null,
        ];

        // If the Super Admin changes their own super admin status, reject to prevent lockout
        if ($admin->id === auth()->id() && !$request->boolean('is_super_admin')) {
            return back()->with('error', 'You cannot remove your own Super Admin privileges.');
        }

        if ($request->filled('password')) {
            $updates['password'] = Hash::make($validated['password']);
        }

        $admin->update($updates);

        return redirect()->route('admin.admins.index')->with('success', 'Administrator updated successfully.');
    }

    public function destroy(User $admin)
    {
        $this->ensureSuperAdmin();

        if ($admin->role !== 'admin') {
            abort(404);
        }

        // Prevent self-deletion
        if ($admin->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Prevent deleting the last Super Admin
        if ($admin->isSuperAdmin()) {
            $superAdminCount = User::where('role', 'admin')->where('is_super_admin', true)->count();
            if ($superAdminCount <= 1) {
                return back()->with('error', 'You cannot delete the last remaining Super Admin.');
            }
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')->with('success', 'Administrator deleted successfully.');
    }
}
