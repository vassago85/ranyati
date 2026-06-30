<?php

use App\Http\Controllers\ClientAuthController;
use App\Http\Controllers\FirearmApplicationController;
use Illuminate\Support\Facades\Route;

Route::middleware('motivations')->prefix('account')->name('account.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [ClientAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [ClientAuthController::class, 'login'])->name('login.submit');
        Route::get('/register', [ClientAuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [ClientAuthController::class, 'register'])->name('register.submit');
    });

    Route::middleware(['auth', 'client'])->group(function () {
        Route::post('/logout', [ClientAuthController::class, 'logout'])->name('logout');

        Route::get('/verify', [ClientAuthController::class, 'showVerify'])->name('verify');
        Route::post('/verify', [ClientAuthController::class, 'verify'])->name('verify.submit');
        Route::post('/verify/resend', [ClientAuthController::class, 'resendOtp'])->name('verify.resend');

        Route::get('/applications', [FirearmApplicationController::class, 'index'])->name('applications.index');
        Route::delete('/applications/{application}', [FirearmApplicationController::class, 'destroy'])->name('applications.destroy');
        Route::post('/applications/{application}/toggle-monitoring', [FirearmApplicationController::class, 'toggleMonitoring'])->name('applications.toggle-monitoring');

        Route::middleware('client.verified')->group(function () {
            Route::get('/applications/create', [FirearmApplicationController::class, 'create'])->name('applications.create');
            Route::post('/applications', [FirearmApplicationController::class, 'store'])->name('applications.store');
            Route::post('/applications/{application}/refresh', [FirearmApplicationController::class, 'refresh'])->name('applications.refresh');
        });
    });
});

Route::middleware('motivations')->get('/track', function () {
    return auth()->check() && auth()->user()->isClient()
        ? redirect()->route('account.applications.index')
        : redirect()->route('account.register');
})->name('track.landing');
