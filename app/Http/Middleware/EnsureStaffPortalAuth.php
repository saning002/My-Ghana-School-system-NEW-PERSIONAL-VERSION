<?php

namespace App\Http\Middleware;

use App\Models\StaffPortalUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\View;

class EnsureStaffPortalAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $id = session('staff_portal_user_id');

        if (!$id) {
            return redirect()->route('staff-portal.login')
                ->with('error', 'Please log in to access the staff portal.');
        }

        $staffUser = StaffPortalUser::with('permissions')->find($id);

        if (!$staffUser || !$staffUser->is_active) {
            session()->forget(['staff_portal_user_id','staff_portal_user_name','staff_portal_user_role']);
            return redirect()->route('staff-portal.login')
                ->with('error', 'Your account is inactive or no longer exists.');
        }

        // Share staff user data with all views in this middleware group
        View::share('staffPortalUser', $staffUser);
        View::share('staffBranchId',  $staffUser->church_branch_id);

        return $next($request);
    }
}
