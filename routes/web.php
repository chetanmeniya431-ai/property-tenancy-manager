<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SignalController;
use App\Http\Controllers\TenancyController;
use App\Support\Roles;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendLink'])->name('password.email')->middleware('throttle:5,10');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:'.implode('|', Roles::STAFF))->group(function () {
        Route::resource('properties', PropertyController::class);
    });

    // Tenancies hold financial data (rent, deposit) and lease documents —
    // the Maintenance Coordinator role is explicitly barred from both.
    Route::middleware('role:'.implode('|', Roles::BACK_OFFICE))->group(function () {
        Route::resource('tenancies', TenancyController::class);
    });

    Route::resource('maintenance', MaintenanceRequestController::class)->parameters(['maintenance' => 'maintenanceRequest']);
    Route::post('maintenance/{maintenanceRequest}/assign', [MaintenanceRequestController::class, 'assign'])->name('maintenance.assign');
    Route::post('maintenance/{maintenanceRequest}/start', [MaintenanceRequestController::class, 'start'])->name('maintenance.start');
    Route::post('maintenance/{maintenanceRequest}/resolve', [MaintenanceRequestController::class, 'resolve'])->name('maintenance.resolve');
    Route::post('maintenance/{maintenanceRequest}/close', [MaintenanceRequestController::class, 'close'])->name('maintenance.close');
    Route::post('maintenance/{maintenanceRequest}/reopen', [MaintenanceRequestController::class, 'reopen'])->name('maintenance.reopen');

    Route::get('/signals', [SignalController::class, 'index'])->name('signals.index');
    Route::post('/signals/events/{signalEvent}/resolve', [SignalController::class, 'resolve'])->name('signals.resolve');

    Route::middleware('role:'.implode('|', Roles::BACK_OFFICE))->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/tenancies/{tenancy}/dispute-export', [ReportController::class, 'disputeExport'])->name('reports.dispute-export');

        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/signals/{signal}/toggle', [SettingsController::class, 'toggleSignal'])->name('settings.signals.toggle');
        Route::post('/settings/users', [SettingsController::class, 'storeUser'])->name('settings.users.store');
    });
});
