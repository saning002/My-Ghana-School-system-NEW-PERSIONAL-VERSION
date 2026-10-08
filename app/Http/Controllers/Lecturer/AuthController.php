<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $user = Auth::user();

        // Ensure user is a lecturer
        if ($user->role !== 'lecturer') {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Access restricted to lecturers only.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('lecturer.dashboard');
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
