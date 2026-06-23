<?php

namespace App\Livewire;

use App\Models\dishSchedules;
use App\Models\Usuario;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{

    public $totalDays = 0;
    public $completedDays = 0;
    public $pendingDays = 0;
    public $missedDays = 0;
    public $swappedDays = 0;

    public $userStats = [];
    public $monthlyCompletion = 0;

    public function mount() 
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $allDays = dishSchedules::whereBetween('scheduled_date', [$startOfMonth, $endOfMonth]);

        $this->totalDays = $allDays->distinct('scheduled_date')->count('scheduled_date');
        $this->completedDays = $allDays->clone()->where('status', 'completed')->distinct('scheduled_date')->count('scheduled_date');
        $this->pendingDays = $allDays->clone()->where('status', 'pending')->distinct('scheduled_date')->count('scheduled_date');
        $this->missedDays = $allDays->clone()->where('status', 'missed')->distinct('scheduled_date')->count('scheduled_date');
        $this->swappedDays = $allDays->clone()->where('status', 'swapped')->distinct('scheduled_date')->count('scheduled_date');

        $this->monthlyCompletion = $this->totalDays > 0 ? round(($this->completedDays / $this->totalDays) * 100, 1) : 0;

        $this->userStats = Usuario::withCount([
            'dishSchedules as total_days' => function($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('scheduled_date', [$startOfMonth, $endOfMonth]);
            },
            'dishSchedules as done_days' => function($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('scheduled_date', [$startOfMonth, $endOfMonth])->where('status', 'completed');
            }
        ])->get()->map(function($user) {
            $completion = $user->total_days > 0 ? round(($user->done_days / $user->total_days) * 100, 1) : 0;

           return [
                'id' => $user->id,
                'name' => $user->nome,
                'total_days' => $user->total_days,
                'done_days' => $user->done_days,
                'completion' => $completion,
                'color' => $this->getUserColor($user->id)
            ]; 
        })->sortByDesc('completion')->values();
    }

    private function getuserColor($userId)
    {
        $colors = ['#ef4444', '#22c55e', '#eab308', '#3b82f6', '#a855f7', '#ec4899'];
        return $colors[$userId % count($colors)];
    }

    public function render()
    {
        return view('livewire.dashboard' ,[
            'totalDays' => $this->totalDays
        ]);
    }
}
