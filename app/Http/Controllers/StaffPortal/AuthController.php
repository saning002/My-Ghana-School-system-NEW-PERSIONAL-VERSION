<?php

namespace App\Http\Controllers\StaffPortal;

use App\Http\Controllers\Controller;
use App\Models\StaffPortalUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
}
