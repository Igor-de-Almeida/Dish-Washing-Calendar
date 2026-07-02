<?php

use App\Http\Controllers\PushSubscriptionController;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\Usuario;
use App\Notifications\NotificationTest;
use App\Notifications\TodayIsYourTurn;

Route::view('/', 'welcome');

/*Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');*/

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

Route::get('/', function () {
    return redirect()->route('house.manager');
});

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
    Route::get('/house', function() {
        return view('house-manager');
    })->name('house.manager');

    Route::get('/house/settings', function () {
        if (!Auth::user()->house_id) {
            return redirect()->route('house.manager')->with('warning', __('app.create_enter'));
        }
        return view('house-settings');
    })->name('house.settings');

    Route::get('/calendar', function () {
        if (!Auth::user()->house_id) {
            return redirect()->route('house.manager')->with('warning', __('app.create_enter'));
        }
        return view('calendar');
    })->name('calendar');

    Route::get('/swap-manager', function () {
        if (!Auth::user()->house_id) {
            return redirect()->route('house.manager')->with('warning', __('app.create_enter'));
        }
        return view('swap-request');
    })->name('swap-requests');

    Route::get('/dashboard', function () {
        if (!Auth::user()->house_id) {
            return redirect()->route('house.manager')->with('warning', __('app.create_enter'));
        }
        return view('dashboard');
    })->name('dashboard');

    Route::get('/admin/schedule', function () {
        if (Auth::user()->tipo !== 'admin' && Auth::user()->tipo !== 'super_admin') {
            abort(403,  __('app.access_denied'));
        }
        if (!Auth::user()->house_id) {
            return redirect()->route('house.manager')->with('warning', __('app.create_enter'));
        }
        return view('admin.schedule');
    })->name('admin.schedule');

    Route::get('/health-check', function () {
    \Log::info('Health check executado');

    return response()->json([
        'status' => 'ok',
        'time' => now(),
    ]);
});
});