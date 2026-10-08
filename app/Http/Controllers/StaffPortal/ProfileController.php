<?php

namespace App\Http\Controllers\StaffPortal;

use App\Http\Controllers\Controller;
use App\Models\StaffPortalUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\CloudinaryService;

class ProfileController extends Controller
{
    public function edit()
    {
        $userId   = session('staff_portal_user_id');
        $staffUser = StaffPortalUser::findOrFail($userId);
        return view('staff-portal.profile', compact('staffUser'));
    }

    public function update(Request $request)
    {
        $userId   = session('staff_portal_user_id');
        $staffUser = StaffPortalUser::findOrFail($userId);

        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone'     => 'nullable|string|max:30',
            'password'  => 'nullable|string|min:6|confirmed',
        ]);

        $updates = $request->only(['full_name', 'phone']);

        if ($request->filled('password')) {
            $updates['password'] = Hash::make($request->password);
        }

        $staffUser->update($updates);

        // Update session name
        session(['staff_portal_user_name' => $staffUser->full_name]);

        return back()->with('success', 'Profile updated successfully.');
    }
}
