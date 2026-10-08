<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Admin branch selection page (separate from /login so random visitors don't hit it)
    Route::get('admin/login', [AuthenticatedSessionController::class, 'adminLogin'])->name('admin.login');

    // Branch selection and branch-specific login
    Route::get('login/branch/{branch}', [AuthenticatedSessionController::class, 'branchLoginForm'])->name('login.branch');
    Route::post('login/branch/{branch}', [AuthenticatedSessionController::class, 'storeBranch'])->name('login.branch.post');

    // Super admin login
    Route::get('login/super-admin', [AuthenticatedSessionController::class, 'superAdminLoginForm'])->name('login.super-admin');
    Route::post('login/super-admin', [AuthenticatedSessionController::class, 'storeSuperAdmin'])->name('login.super-admin.post');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
