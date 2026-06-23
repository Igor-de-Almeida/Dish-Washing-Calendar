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
        $this->usuarios = Usuario::all();
        $this->isAdmin = Auth::check() ? Auth::user()->isAdmin() : false;
        $this->startMonth = now()->format('Y-m');
        $this->selectedUsers = collect($this->usuarios)->pluck('id')->toArray();
    }

    public function generateSchedule() 
    {
        //$this->validate(GenerateScheduleRequest::class);

        if (!$this->isAdmin) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Apenas administradores podem gerar escalas!'
            ]);
            return;
        }

        if (empty($this->selectedUsers)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Selecione pelo menos um usuário para a escala.'
            ]);
        }

        $startDate = Carbon::parse($this->startMonth . '-01');
        $endDate = $startDate->copy()->endOfMonth();

        $users = Usuario::whereIn('id', $this->selectedUsers)->get()->shuffle();

        if ($users->isEmpty()) {
            //session()->flash('error', 'Nenhum usuário encontrado.');
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Nenhum Usuário Encontrado'
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
                        'notes' => 'Sábado á tarde'
                    ]);

                    $currentUserIndex++;
                    $userNoite = $users[$currentUserIndex % $userCount];

                    // Sabado Noite
                    dishSchedules::create([
                        'user_id' => $userNoite->id,
                        'scheduled_date' => $currentDate->format('Y-m-d'),
                        'shift' => 'noite',
                        'status' => 'pending',
                        'notes' => 'Sábado á noite'
                    ]);
                } elseif ($dayOfWeek === 0) {

                    if (!$userTarde || !$userNoite) {
                        $userTarde = $users[$currentUserIndex % $userCount];

                        dishSchedules::create([
                            'user_id' => $userTarde->id,
                            'scheduled_date' => $currentDate->format('Y-m-d'),
                            'shift' => 'tarde',
                            'status' => 'pending',
                            'notes' => 'Domingo á tarde'
                        ]);

                        $currentUserIndex++;

                        $userNoite = $users[$currentUserIndex % $userCount];

                        dishSchedules::create([
                            'user_id' => $userNoite->id,
                            'scheduled_date' => $currentDate->format('Y-m-d'),
                            'shift' => 'tarde',
                            'status' => 'pending',
                            'notes' => 'Domingo á tarde'
                        ]);
                    } 
                    else {
                        // Domingo a tarde
                        dishSchedules::create([
                            'user_id' => $userNoite->id,
                            'scheduled_date' => $currentDate->format('Y-m-d'),
                            'shift' => 'tarde',
                            'status' => 'pending',
                            'notes' => 'Domingo á tarde'
                        ]);

                        // Domingo a noite
                        dishSchedules::create([
                            'user_id' => $userTarde->id,
                            'scheduled_date' => $currentDate->format('Y-m-d'),
                            'shift' => 'noite',
                            'status' => 'pending',
                            'notes' => 'Domingo á noite'
                        ]);
                    }
                } 
                else {

                    dishSchedules::create([
                        'user_id' => $users[$currentUserIndex % $userCount]->id,
                        'scheduled_date' => $currentDate->format('Y-m-d'),
                        'shift' => 'full',
                        'status' => 'pending',
                        'notes' => 'Gerado Automaticamente'
                    ]);
                    
                    $currentUserIndex++;
                }
                $currentDate->addDay();
            }
        });

        //Carbon::setLocale('pt');
        //session()->flash('success', '✅ Escala automática gerada com sucesso para '. $startDate->translatedFormat('F/Y'). '!');

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Escala automática gerada com sucesso para '. $startDate->translatedFormat('F/Y'). '!'
        ]);

        return redirect()->route('calendar');
    }

    public function clearGeneratedSchedule()
    {
        if (!$this->isAdmin) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Apenas administradores podem limpar a escala.'
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
                'message' => '🗑️ Escala do mês '. $startDate->translatedFormat('F/Y') . ' limpa com sucesso!'
            ]);
        });

        return redirect()->route('calendar');

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
                'message' => 'Apenas administradores podem exportar a escala.'
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
                'message' => 'Exportando Escala...'
            ]);
            echo $pdf->output();
        }, 'escala-' . $startDate->format('Y-m') . '.pdf');   
    }

    public function render()
    {
        return view('livewire.schedule-manager', ['usuarios' => $this->usuarios]);
    }
}
