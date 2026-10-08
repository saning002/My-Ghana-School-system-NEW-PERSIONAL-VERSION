<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffPortalUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffPortalController extends Controller
{
    public function index()
    {
        $staffUsers = StaffPortalUser::with('permissions')->latest()->get();
        return view('admin.staff-portal.index', compact('staffUsers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name'   => 'required|string|max:150',
            'email'       => 'required|email|unique:staff_portal_users,email',
            'password'    => 'required|string|min:6',
            'role'        => 'required|in:accountant,headmaster,headteacher,deputy,secretary',
            'phone'       => 'nullable|string|max:30',
            'permissions' => 'nullable|array',
        ]);

        $user = StaffPortalUser::create([
            'full_name' => $request->full_name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'phone'     => $request->phone,
            'is_active' => true,
        ]);

        // If admin explicitly chose permissions, use those.
        // If they submitted nothing, grant the full default set (all ON until turned off).
        if ($request->has('permissions') && is_array($request->permissions) && count($request->permissions) > 0) {
            $user->syncPermissions($request->permissions);
        } else {
            $user->grantDefaultPermissions();
        }

        return back()->with('success', "{$user->role_label} account created for {$user->full_name}.");
    }

    public function update(Request $request, StaffPortalUser $staffPortalUser)
    {
        $request->validate([
            'full_name'   => 'required|string|max:150',
            'role'        => 'required|in:accountant,headmaster,headteacher,deputy,secretary',
            'phone'       => 'nullable|string|max:30',
            'permissions' => 'nullable|array',
            'is_active'   => 'nullable|boolean',
            'password'    => 'nullable|string|min:6',
        ]);

        $data = [
            'full_name' => $request->full_name,
            'role'      => $request->role,
            'phone'     => $request->phone,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $staffPortalUser->update($data);
        $staffPortalUser->syncPermissions($request->permissions ?? []);

        return back()->with('success', "{$staffPortalUser->full_name}'s account updated.");
    }

    public function destroy(StaffPortalUser $staffPortalUser)
    {
        $name = $staffPortalUser->full_name;
        $staffPortalUser->delete();
        return back()->with('success', "{$name}'s account deleted.");
    }
}
