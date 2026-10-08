<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Models\OwnerPayment;
use App\Models\Tenant;

class DashboardController extends Controller
{
    public function index()
    {
        // School status counts
        $stats = [
            'total'     => Tenant::count(),
            'active'    => Tenant::where('status', 'active')->count(),
            'trial'     => Tenant::where('status', 'trial')->count(),
            'suspended' => Tenant::where('status', 'suspended')->count(),
            'expired'   => Tenant::where('status', 'expired')->count(),
        ];

        // Revenue this month
        $revenueThisMonth = OwnerPayment::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        // Revenue all time
        $revenueTotal = OwnerPayment::sum('amount');

        // Recent payments (last 8)
        $recentPayments = OwnerPayment::with('tenant')
            ->orderByDesc('payment_date')
            ->limit(8)
            ->get();

        // Schools expiring in next 30 days
        $expiringSoon = Tenant::where('status', 'active')
            ->whereNotNull('subscription_ends_at')
            ->whereBetween('subscription_ends_at', [now(), now()->addDays(30)])
            ->orderBy('subscription_ends_at')
            ->get();

        // Schools on trial expiring in next 7 days
        $trialsExpiringSoon = Tenant::where('status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->whereBetween('trial_ends_at', [now(), now()->addDays(7)])
            ->orderBy('trial_ends_at')
            ->get();

        // Recent schools (last 5)
        $recentSchools = Tenant::orderByDesc('created_at')->limit(5)->get();

        // Recent backups (last 5)
        $recentBackups = Backup::with('tenant')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Monthly revenue for chart (last 12 months)
        $monthlyRevenue = collect(range(11, 0))->map(function ($monthsAgo) {
            $date = now()->subMonths($monthsAgo);
            return [
                'month'  => $date->format('M Y'),
                'amount' => OwnerPayment::whereMonth('payment_date', $date->month)
                    ->whereYear('payment_date', $date->year)
                    ->sum('amount'),
            ];
        });

        return view('owner.dashboard', compact(
            'stats', 'revenueThisMonth', 'revenueTotal',
            'recentPayments', 'expiringSoon', 'trialsExpiringSoon',
            'recentSchools', 'recentBackups', 'monthlyRevenue'
        ));
    }
}
