<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

/*Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');*/

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

Route::get('/calendar', function () {
    return view('calendar');
})->name('calendar');

/* Route::get('/debug', function () {
    return [
        'secure' => request()->secure(),
        'scheme' => request()->getScheme(),
        'url' => url('/'),
    ];
}); */

Route::middleware(['auth'])->group(function () {
    Route::get('/calendar', function () {
        return view('calendar');
    })->name('calendar');

    Route::get('/swap-manager', function () {
        return view('swap-request');
    })->name('swap-requests');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    //Apenas Admin
    Route::middleware(['admin'])->group(function () {
        Route::get('/admin/schedule', function () {
            return view('admin.schedule');
        })->name('admin.schedule');
    });
});