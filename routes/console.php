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

Schedule::command('app:send-daily-dish-reminders')->dailyAt('8:00')->withoutOverlapping();
