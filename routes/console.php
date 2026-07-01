<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Carbon\Carbon;
use App\Models\dishSchedules;
use App\Notifications\TodayIsYourTurn;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    $today = Carbon::today();

    $schedules = dishSchedules::where('scheduled_date', $today)-where('status', 'pending')->with('usuario')->get();

    foreach ($schedules as $schedule) {
        if ($schedule->usuario) {
            $schedule->usuario->notify(new TodayIsYourTurn($schedule));
        }
    }
})->dailyAt('08:00');

Schedule::command('app:send-daily-dish-reminders')->dailyAt('8:00');
