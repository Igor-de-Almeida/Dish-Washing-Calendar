<?php

use App\Http\Controllers\PushSubscriptionController;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\Usuario;
use App\Notifications\NotificationTest as TestWebPushNotification;

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

    Route::get('/test-push', function () {

        $user = auth()->user();

        $user->notify(new TestWebPushNotification);

        dd([
            'auth_id' => $user->id,
            'subscriptions' => $user->pushSubscriptions()->get()->toArray(),
        ]);
        
        \Log::info('Número de subscriptions', [
            'count' => $user->pushSubscriptions()->count()
        ]);

        try {

            
            return 'Notificação enviada!';
        }  catch (\Throwable $e) {
            \Log::error('Erro ao enviar WebPush', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $e->getMessage();
        } 
        
    });

    Route::get('/test-subscriptions', function () {
        dd(auth()->user()->pushSubscriptions());
    });

    Route::get('/debug-user', function () {

        $user = auth()->user();

        dd(
            get_class($user),
            $user->id,
            $user->pushSubscriptions()->toSql(),
            $user->pushSubscriptions()->getBindings()
        );

    });

    Route::post('/api/push/subscribe', [PushSubscriptionController::class, 'store']);

});