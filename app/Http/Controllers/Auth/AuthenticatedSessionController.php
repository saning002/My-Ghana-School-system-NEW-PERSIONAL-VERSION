<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ChurchBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        // Redirect generic /login visits to the website portal page.
        // Admin login is accessed via /admin/login directly.
        return redirect()->route('website.portal');
    }

    public function adminLogin()
    {
        $branches = ChurchBranch::orderBy('name')->get();
        return view('auth.branch-select', compact('branches'));
    }

    public function branchLoginForm(ChurchBranch $branch)
    {
        // Guard: if branch is null, redirect to branch selection
        if (! $branch || ! $branch->id) {
            return redirect()->route('login');
        }

        // Get the login background from any admin assigned to this branch (prioritize first admin with background)
        $adminWithBackground = \App\Models\User::where('church_branch_id', $branch->id)
            ->whereNotNull('login_background_url')
            ->first();
        
        $loginBackground = $adminWithBackground?->login_background_url;
        
        return view('auth.login-branch', compact('branch', 'loginBackground'));
    }

    public function superAdminLoginForm()
    {
        // Get the login background from any super admin with a background set
        $superAdminWithBackground = \App\Models\User::where('is_super_admin', true)
            ->whereNotNull('login_background_url')
            ->first();
        
        $loginBackground = $superAdminWithBackground?->login_background_url;
        
        return view('auth.login-branch', ['branch' => null, 'super' => true, 'loginBackground' => $loginBackground]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $user = Auth::user();
        if (! in_array($user->role, ['admin', 'lecturer'], true) && ! $user->isSuperAdmin()) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Access restricted to administrators and lecturers only.',
            ]);
        }

        $request->session()->regenerate();

        if ($user->role === 'lecturer') {
            return redirect()->route('lecturer.dashboard');
        }

        return redirect()->route('admin.dashboard');
    }

    public function storeBranch(Request $request, ChurchBranch $branch)
    {
        // Guard: if branch is null, redirect to branch selection
        if (! $branch || ! $branch->id) {
            return redirect()->route('login');
        }

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $user = Auth::user();

        if ($user->role !== 'admin' && ! $user->isSuperAdmin()) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Access restricted to administrators only.',
            ]);
        }

        if (! $user->isSuperAdmin() && ($user->church_branch_id !== $branch->id)) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'These credentials are not authorized for this branch.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function storeSuperAdmin(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $user = Auth::user();
        if (! $user->isSuperAdmin()) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Super admin access required.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('website.home');
    }
}
