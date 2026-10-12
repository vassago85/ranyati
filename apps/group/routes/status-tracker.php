<?php

use App\Http\Controllers\Account\ClientProfileController;
use App\Http\Controllers\Account\MotivationRequestController;
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

            // Personal particulars shared across every motivation; filled once.
            Route::get('/profile', [ClientProfileController::class, 'edit'])->name('profile.edit');
            Route::post('/profile', [ClientProfileController::class, 'update'])->name('profile.update');

            // Motivation requests: pick a type, answer the questionnaire,
            // upload documents (including a signed Power of Attorney), submit.
            Route::prefix('motivations')->name('motivations.')->group(function () {
                Route::get('/', [MotivationRequestController::class, 'index'])->name('index');
                Route::get('/create', [MotivationRequestController::class, 'create'])->name('create');
                Route::post('/', [MotivationRequestController::class, 'store'])->name('store');
                Route::get('/{motivation}', [MotivationRequestController::class, 'show'])->name('show');
                Route::get('/{motivation}/edit', [MotivationRequestController::class, 'edit'])->name('edit');
                Route::post('/{motivation}', [MotivationRequestController::class, 'update'])->name('update');
                Route::post('/{motivation}/submit', [MotivationRequestController::class, 'submit'])->name('submit');
                Route::delete('/{motivation}', [MotivationRequestController::class, 'destroy'])->name('destroy');

                Route::get('/{motivation}/poa', [MotivationRequestController::class, 'downloadPoa'])->name('poa');
                Route::get('/{motivation}/reference-template', [MotivationRequestController::class, 'downloadReference'])->name('reference-template');

                Route::post('/{motivation}/documents', [MotivationRequestController::class, 'uploadDocument'])->name('documents.upload');
                Route::delete('/{motivation}/documents/{document}', [MotivationRequestController::class, 'deleteDocument'])->name('documents.delete');
                Route::get('/{motivation}/documents/{document}', [MotivationRequestController::class, 'viewDocument'])->name('documents.view');
            });
        });
    });
});

Route::middleware('motivations')->get('/track', function () {
    return auth()->check() && auth()->user()->isClient()
        ? redirect()->route('account.applications.index')
        : redirect()->route('account.register');
})->name('track.landing');
