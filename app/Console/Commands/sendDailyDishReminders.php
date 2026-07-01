<?php

namespace App\Console\Commands;

use App\Models\dishSchedules;
use Illuminate\Console\Command;
use Carbon\Carbon;

class sendDailyDishReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-daily-dish-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        $schedules = dishSchedules::with('usuario')
            ->whereDate('scheduled_date', $today)
            ->where('status', 'pending')
            ->get();

        foreach ($schedules as $schedule) {
            if ($schedule->usuario) {
                $schedule->usuario->notify(new \App\Notifications\TodayIsYourTurn($schedule));
                $this->info('Notificacao enviada para '. $schedule->usuario->nome);
            }
        }
        
        $this->info('Notificacoes diarias enviadas!');
        return self::SUCCESS;
    }
}
