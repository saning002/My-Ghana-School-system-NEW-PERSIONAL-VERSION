<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class EnsurePortalAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Check if portal is open
        if (!Setting::portalIsOpen()) {
            Session::forget(['portal_student_id', 'portal_student_name']);
            return redirect()->route('portal.login')
                ->with('portal_closed', true);
        }

        // Check if student is logged in
        if (!Session::has('portal_student_id')) {
            return redirect()->route('portal.login')
                ->with('error', 'Please log in to access the student portal.');
        }

        return $next($request);
    }
}
