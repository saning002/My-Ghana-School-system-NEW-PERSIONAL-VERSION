<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Models\MessagingCreditPayment;
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

        // Revenue this month — subscriptions + messaging credits
        $revenueThisMonth =
            OwnerPayment::whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount')
            +
            MessagingCreditPayment::where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount');

        // Revenue all time — subscriptions + messaging credits
        $revenueTotal =
            OwnerPayment::sum('amount')
            +
            MessagingCreditPayment::where('status', 'completed')->sum('amount');

        // Revenue split for info cards
        $subscriptionRevenueTotal = OwnerPayment::sum('amount');
        $messagingRevenueTotal    = MessagingCreditPayment::where('status', 'completed')->sum('amount');

        // Recent payments — merge subscription + messaging, show last 8
        $recentPayments = OwnerPayment::with('tenant')
            ->orderByDesc('payment_date')
            ->limit(8)
            ->get();

        $recentMessagingPayments = MessagingCreditPayment::with('tenant', 'package')
            ->where('status', 'completed')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(function ($p) {
                // Normalise to same shape as OwnerPayment for the view
                $p->payment_date  = $p->created_at;
                $p->payment_type  = 'messaging_credits';
                $p->description   = optional($p->package)->name
                    ? "Messaging Credits: {$p->package->name}"
                    : 'Messaging Credits';
                return $p;
            });

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

        // Monthly revenue for chart (last 12 months) — combined
        $monthlyRevenue = collect(range(11, 0))->map(function ($monthsAgo) {
            $date = now()->subMonths($monthsAgo);

            $subscriptions = OwnerPayment::whereMonth('payment_date', $date->month)
                ->whereYear('payment_date', $date->year)
                ->sum('amount');

            $messaging = MessagingCreditPayment::where('status', 'completed')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('amount');

            return [
                'month'         => $date->format('M Y'),
                'amount'        => $subscriptions + $messaging,
                'subscriptions' => $subscriptions,
                'messaging'     => $messaging,
            ];
        });

        return view('owner.dashboard', compact(
            'stats',
            'revenueThisMonth',
            'revenueTotal',
            'subscriptionRevenueTotal',
            'messagingRevenueTotal',
            'recentPayments',
            'recentMessagingPayments',
            'expiringSoon',
            'trialsExpiringSoon',
            'recentSchools',
            'recentBackups',
            'monthlyRevenue'
        ));
    }
}
