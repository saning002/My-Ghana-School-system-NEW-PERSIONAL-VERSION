<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChurchBranch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class BranchAdminController extends Controller
{
    private function ensureSuperAdmin()
    {
        if (! auth()->check() || ! auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized Access. Super admin only.');
        }
    }

    public function index(ChurchBranch $branch)
    {
        $this->ensureSuperAdmin();

        $admins = $branch->admins()->paginate(10);
        return view('admin.branch-admins.index', compact('branch', 'admins'));
    }

    public function create(ChurchBranch $branch)
    {
        $this->ensureSuperAdmin();

        return view('admin.branch-admins.create', compact('branch'));
    }

    public function store(Request $request, ChurchBranch $branch)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6|confirmed',
            'phone'     => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'full_name' => $validated['full_name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'phone'     => $validated['phone'] ?? null,
            'role'      => 'admin',
            'church_branch_id' => $branch->id,
        ]);

        return redirect()->route('admin.branches.admins.index', $branch)->with('success', 'Branch admin created successfully.');
    }

    public function edit(ChurchBranch $branch, User $admin)
    {
        $this->ensureSuperAdmin();

        if ($admin->church_branch_id !== $branch->id) {
            abort(404);
        }

        return view('admin.branch-admins.edit', compact('branch', 'admin'));
    }

    public function update(Request $request, ChurchBranch $branch, User $admin)
    {
        $this->ensureSuperAdmin();

        if ($admin->church_branch_id !== $branch->id) {
            abort(404);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $admin->id,
            'phone'     => 'nullable|string|max:20',
            'password'  => 'nullable|string|min:6|confirmed',
        ]);

        $updates = [
            'full_name' => $validated['full_name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
        ];

        if ($request->filled('password')) {
            $updates['password'] = Hash::make($validated['password']);
        }

        $admin->update($updates);

        return redirect()->route('admin.branches.admins.index', $branch)->with('success', 'Branch admin updated successfully.');
    }

    public function destroy(ChurchBranch $branch, User $admin)
    {
        $this->ensureSuperAdmin();

        if ($admin->church_branch_id !== $branch->id || $admin->isSuperAdmin()) {
            abort(404);
        }

        $admin->delete();

        return redirect()->route('admin.branches.admins.index', $branch)->with('success', 'Branch admin removed successfully.');
    }
}
