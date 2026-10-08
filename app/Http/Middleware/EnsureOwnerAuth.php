<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnerAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('owner')->check()) {
            return redirect()->route('owner.login')
                ->with('error', 'Please log in to access the owner panel.');
        }

        return $next($request);
    }
}
