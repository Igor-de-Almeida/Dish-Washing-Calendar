<?php

namespace App\Livewire;

use App\Http\Requests\GenerateScheduleRequest;
use Livewire\Component;
use App\Models\Usuario;
use App\Models\dishSchedules;
use App\Models\SwapRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScheduleManager extends Component
{

    public $usuarios = [];
    public $selectedUsers = [];
    public $isAdmin = false;
    public $scaleType = 'weekly_rotation';
    public $startMonth;

    public function mount() { 
        $this->usuarios = Usuario::where('house_id', auth()->user()->house_id)->get();
        $this->isAdmin = Auth::check() ? Auth::user()->isAdmin() : false;
        $this->startMonth = now()->format('Y-m');
        $this->selectedUsers = collect($this->usuarios)->pluck('id')->toArray();
    }

    public function generateSchedule() 
    {
        //$this->validate((new \App\Http\Requests\GenerateScheduleRequest())->rules());
        
        if (!$this->isAdmin) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => __('app.only_admin_can_generate')
                ]);
                return;
            }

            if (empty($this->selectedUsers)) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => __('app.select_at_least_user')
                ]);
            }

            
        try {

            $startDate = Carbon::parse($this->startMonth . '-01');
            $endDate = $startDate->copy()->endOfMonth();

            $users = Usuario::whereIn('id', $this->selectedUsers)->where('house_id', Auth::user()->house_id)->get()->shuffle();

            if ($users->isEmpty()) {
                
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => __('app.no_user_found')
                ]);
                return;
            }

            $userCount = $users->count();

            DB::transaction(function () use ($startDate, $endDate, $users, $userCount) {

                $currentDate = $startDate->copy();
                $currentUserIndex = 0;

                $userTarde = null;
                $userNoite = null;

                while ($currentDate <= $endDate) {
                    $dayOfWeek = $currentDate->dayOfWeek();

                    // Sabado Tarde
                    if ($dayOfWeek === 6) {
                        $userTarde = $users[$currentUserIndex % $userCount];

                        dishSchedules::create([
                            'user_id' => $userTarde->id,
                            'scheduled_date' => $currentDate->format('Y-m-d'),
                            'shift' => 'tarde',
                            'status' => 'pending',
                            'notes' => __('app.saturday_afternoon')
                        ]);

                        $currentUserIndex++;
                        $userNoite = $users[$currentUserIndex % $userCount];

                        // Sabado Noite
                        dishSchedules::create([
                            'user_id' => $userNoite->id,
                            'scheduled_date' => $currentDate->format('Y-m-d'),
                            'shift' => 'noite',
                            'status' => 'pending',
                            'notes' => __('app.saturday_night')
                        ]);
                    } elseif ($dayOfWeek === 0) {

                        if (!$userTarde || !$userNoite) {
                            $userTarde = $users[$currentUserIndex % $userCount];

                            dishSchedules::create([
                                'user_id' => $userTarde->id,
                                'scheduled_date' => $currentDate->format('Y-m-d'),
                                'shift' => 'tarde',
                                'status' => 'pending',
                                'notes' => __('app.sunday_afternoon')
                            ]);

                            $currentUserIndex++;

                            $userNoite = $users[$currentUserIndex % $userCount];

                            dishSchedules::create([
                                'user_id' => $userNoite->id,
                                'scheduled_date' => $currentDate->format('Y-m-d'),
                                'shift' => 'tarde',
                                'status' => 'pending',
                                'notes' => __('app.sunday_afternoon')
                            ]);
                        } 
                        else {
                            // Domingo a tarde
                            dishSchedules::create([
                                'user_id' => $userNoite->id,
                                'scheduled_date' => $currentDate->format('Y-m-d'),
                                'shift' => 'tarde',
                                'status' => 'pending',
                                'notes' => __('app.sunday_afternoon')
                            ]);

                            // Domingo a noite
                            dishSchedules::create([
                                'user_id' => $userTarde->id,
                                'scheduled_date' => $currentDate->format('Y-m-d'),
                                'shift' => 'noite',
                                'status' => 'pending',
                                'notes' => __('app.sunday_night')
                            ]);
                        }
                    } 
                    else {

                        dishSchedules::create([
                            'user_id' => $users[$currentUserIndex % $userCount]->id,
                            'scheduled_date' => $currentDate->format('Y-m-d'),
                            'shift' => 'full',
                            'status' => 'pending',
                            'notes' => __('app.auto_generated')
                        ]);
                        
                        $currentUserIndex++;
                    }
                    $currentDate->addDay();
                }
            });

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => __('app.auto_generated_success') .' '. $startDate->translatedFormat('F/Y'). '!'
            ]);

            return redirect()->route('calendar');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.error_auto_generate')
            ]);
        }
    }

    public function clearGeneratedSchedule()
    {

        try {
            if (!$this->isAdmin) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => __('app.only_admin_clear')
                ]);

                return;
            }

            $startDate = Carbon::parse($this->startMonth . '-01');
            $endDate = $startDate->copy()->endOfMonth();

            DB::transaction(function () use ($startDate, $endDate) {
                SwapRequest::whereHas('fromDishDay', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('scheduled_date', [$startDate, $endDate]);
                })->orWhereHas('toDishDay', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('scheduled_date', [$startDate, $endDate]);
                })->delete();

                dishSchedules::whereBetween('scheduled_date', [$startDate, $endDate])->delete();

                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => '🗑️ '. __('app.scale_of_month') .' '. $startDate->translatedFormat('F/Y') .' '.__('app.success_clear')
                ]);
            });

            return redirect()->route('calendar');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.error_clear_scale')
            ]);
        }
        

    }

    private function getUserColor($userId)
    {
        $colors = ['#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#3b82f6'];
        return $colors[$userId % count($colors)] ?? '#6b7280';
    }

    public function exportToPdf() 
    {
        if (!$this->isAdmin) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.only_admin_export')
            ]);
            return;
        }

        $startDate = Carbon::parse($this->startMonth . '-01');
        $endDate = $startDate->copy()->endOfMonth();

        $schedules = dishSchedules::with('usuario')->whereBetween('scheduled_date', [$startDate, $endDate])->orderBy('scheduled_date')->get();

        $pdf = \PDF::loadView('pdf.schedule', ['schedules' => $schedules, 'month' => $startDate->translatedFormat('F Y')]);

        return response()->streamDownload(function () use ($pdf) {
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => __('app.exporting_scale') .'...'
            ]);
            echo $pdf->output();
        }, 'escala-' . $startDate->format('Y-m') . '.pdf');   
    }

    public function render()
    {
        return view('livewire.schedule-manager', ['usuarios' => $this->usuarios]);
    }
}
