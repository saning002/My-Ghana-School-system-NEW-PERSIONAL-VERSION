<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

use Illuminate\Support\Facades\Schedule;

// ── Tenant scheduled backups ──────────────────────────────────────────────
Schedule::command('tenants:backup --frequency=daily')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('tenants:backup --frequency=weekly')
    ->weeklyOn(0, '03:00') // Sunday 3am
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('tenants:backup --frequency=monthly')
    ->monthlyOn(1, '04:00') // 1st of month 4am
    ->withoutOverlapping()
    ->runInBackground();
