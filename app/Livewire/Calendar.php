<?php

namespace App\Livewire;

use App\Http\Requests\AssignDayRequest;
use App\Http\Requests\MarkAsDoneRequest;
use App\Http\Requests\SwapRequestForm;
use Livewire\Component;
use App\Models\Usuario;
use App\Models\dishSchedules;
use App\Models\SwapRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Livewire\WithFileUploads;

class Calendar extends Component
{
    use WithFileUploads;

    public $events = [];
    public $usuarios = [];

    public $isAdmin = false;

    public function mount()
    {
        $this->usuarios = Usuario::all();
        $this->isAdmin = Auth::check() ? Auth::user()->isAdmin() : false;
        $this->loadEvents();
    }

    // Foto
    public $showPhotoModal = false;
    public $photoDayId = null;
    public $photoPreview = null;
    public $photo;

    public function openPhotoModal ($dayId) 
    {
        $this->photoDayId = $dayId;
        $this->photoPreview = null;
        $this->showPhotoModal = true;
    }

    public $loading = false;

    public function markAsDoneWithPhoto() 
    {
        $this->loading = true;

       // $this->validate(MarkAsDoneRequest::class);
        
        try {
            $dish = dishSchedules::findOrFail($this->photoDayId);

            if ($dish && $this->photo) {

                $path = $this->photo->store('dish-photos', 'public');

                $dish->update([
                    'status' => 'completed',
                    'photo_path' => $path,
                    'notes' => 'Marcado como FEITO com foto em '. now()->toDateTimeString()
                ]);

                $this->reset('photo');
                $this->loadEvents();
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Dia marcado como FEITO '
                ]);
                $this->dispatch('calendar-refresh');
            } else if ($dish) {

                $dish->update([
                    'status' => 'completed',
                    'notes' => 'Marcado como feito em ' . now()->toDateTimeString()
                ]);

                $this->loadEvents();
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Dia marcado como FEITO '
                ]);
                $this->dispatch('calendar-refresh');
            }
            $this->closePhotoModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Erro ao marcar como FEITO com foto.'
            ]);
        } finally {
            $this->loading = false;
        }
    }

    public function closePhotoModal()
    {
        $this->showPhotoModal = false;
        $this->photoDayId = null;
        $this->photoPreview = null;
    }

    // Swapp 
    public $showSwapModal = false;
    public $selectedSwapDayId = null;
    public $swapTargetUserId = null;
    public $targetDayId = null;
    public $swapNotes = '';

    public function openSwapModal($dayId)
    {
        $this->selectedSwapDayId = $dayId;
        $this->swapTargetUserId = null;
        $this->showSwapModal = true;
        $this->targetDayId = null;
        $this->swapNotes = '';


        $this->dispatch('calendar-refresh');
    }

    // Pedido de Troca
    public function sendSwapRequest()
    {
        $this->loading = true;
        //$this->validate(SwapRequestForm::class);

        try {
            if (!$this->selectedSwapDayId || !$this->swapTargetUserId) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Preencha todos os campos!'
                ]);
                return;
            }

            $day = dishSchedules::findOrFail($this->selectedSwapDayId);
            $targetDay = dishSchedules::findOrFail($this->targetDayId);

            if (!$day || $day->user_id !== FacadesAuth::id()) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Só podes pedir troca do teu próprio dia!'
                ]);
                $this->closeSwapModal();
                $this->closeActionModal();
                return;
            }

            $swapRequest = SwapRequest::create([
                'from_user_id' => FacadesAuth::id(),
                'to_user_id' => $this->swapTargetUserId,
                'from_dish_day_id' => $this->selectedSwapDayId,
                'to_dish_day_id' => $targetDay->id,
                'status' => 'pending',
                'notes' => $this->swapNotes
            ]);

            $swapRequest->load(['fromUser', 'toUser', 'fromDishDay']);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Pedido de troca enviado com sucesso!'
            ]);
            $this->closeSwapModal();
            $this->closeActionModal();
            event(new \App\Events\SwapRequestCreated($swapRequest));
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Erro ao Enviar Pedido de troca'
            ]);
        } finally {
            $this->loading = false;    
        }
    }

    public function closeSwapModal()
    {
        $this->showSwapModal = false;
        $this->selectedSwapDayId = null;
        $this->swapTargetUserId = null;
        $this->targetDayId = null;
        $this->swapNotes = '';

        $this->dispatch('calendar-refresh');
    }

    // Actions Modal
    public $showDoneModal = false;
    public $doneEventId = null;
    public $doneEventTitle = null;
    public $showActionModal = false;
    public $currentStatus = null;

    public function openActionModal($id, $title, $currentStatus)
    {
        $event = dishSchedules::with('usuario')->find($id);

        if (!$this->isAdmin && $event && $event->user_id !== Auth::id()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Não tens permissão para alterar este dia!'
            ]);
            $this->dispatch('calendar-refresh');
            return;
        }

        $this->doneEventId = $id;
        $this->doneEventTitle = $title;
        $this->currentStatus = $currentStatus;
        $this->showActionModal = true;

        $this->loadEvents();
        $this->dispatch('calendar-refresh');
    }

    public function markAsMissed() 
    {
        $this->loading = true;

        try {
            if (!$this->isAdmin) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Apenas administradores podem marcar esta opção!'
                ]);
                $this->closeActionModal();
                $this->dispatch('calendar-refresh');
                return;
            }

            if ($this->doneEventId) {
                $dishSchedule = dishSchedules::findOrFail($this->doneEventId);

                if ($dishSchedule) {
                    $dishSchedule->update([
                        'status' => 'missed',
                        'notes' => 'Não Lavou a loiça nesta data: ' . now()->toDateTimeString()
                    ]);

                    $this->loadEvents();
                    $this->dispatch('notify', [
                        'type' => 'success',
                        'message' => ' Dia Marcado como NÂO LAVOU'
                    ]);
                    $this->dispatch('calendar-refresh');
                }
            }

            $this->closeActionModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Erro ao marcar dia como MISSED'
            ]);
        } finally {
            $this->loading = false;
        }
        
    }

    public function resetToScheduled()
    {
        $this->loading = true;

        try {
            if (!$this->isAdmin) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Apenas administradores podem redefinir o estado!'
                ]);
                $this->closeActionModal();
                $this->dispatch('calendar-refresh');
                return;
            }

            if ($this->doneEventId) {
                $dishSchedule = dishSchedules::findOrFail($this->doneEventId);
                if ($dishSchedule) {
                    $dishSchedule->update([
                        'status' => 'pending',
                        'notes' => 'Marcado como pendente em '. now()->toDateString()
                    ]);

                    $this->loadEvents();
                    $this->dispatch('notify', [
                        'type' => 'success',
                        'message' => ' Dia REDEFINIDO com sucesso!'
                    ]);
                    $this->dispatch('calendar-refresh');
                }
            }

            $this->closeActionModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Erro ao marcar dia como REDEFINIDO'
            ]);
        } finally {
            $this->loading = false;
        }
        
    }

    public function closeActionModal() 
    {
        $this->showActionModal = false;
        $this->doneEventId = null;
        $this->doneEventTitle = null;

        $this->dispatch('calendar-refresh');
    }

    // Assignment Modal
    public $showModal = false;
    public $selectedDate = null;
    public $selectedUserId = null;

    // Abre o Modal
    public function openModal($date) {

        $this->selectedDate = $date;
        $this->selectedUserId = null;
        $this->showModal = true;

        $this->dispatch('calendar-refresh');
    }

    // Fecha o Modal
    public function closeModal() {

        $this->showModal = false;
        $this->selectedUserId = null;
        $this->selectedDate = null;

        $this->dispatch('calendar-refresh');
    }

    // Salva Atribuicao
    public function assignUser()
    {
        $this->loading = true;

        try {
            //$this->validate(AssignDayRequest::class);

            if (!$this->isAdmin) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Apenas administradores podem realizar esta acção!'
                ]);
                $this->closeActionModal();
                $this->closeModal();
                $this->dispatch('calendar-refresh');
                return;
            }

            $event = dishSchedules::with('usuario')->find($this->selectedUserId);

            $this->validate([
                'selectedUserId' => 'required|exists:usuarios,id',
                'selectedDate' => 'required|date',
            ]);

            if ($this->selectedDate && $this->selectedUserId)
                {
                    dishSchedules::updateOrCreate
                    (
                        ['scheduled_date' => $this->selectedDate],
                        ['user_id' => $this->selectedUserId, 'status' => 'pending']
                    );

                    $this->loadEvents();

                    if ($event) {
                        //session()->flash('success', '✅ Data Atribuída ao usuario: '. $event->usuario->nome ?? 'Desconhecido');
                        $this->dispatch('notify', [
                            'type' => 'success',
                            'message' => ' Data Atribuída ao usuario: '. $event->usuario->nome ?? 'Desconhecido'
                        ]);
                    }

                    $this->dispatch('calendar-refresh' , events: $this->events);
                }

                $this->closeModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Erro ao atribuir dia'
            ]);
        } finally {
            $this->loading = false;
        }
        
    }

    public function loadEvents()
    {
        $dishSchedules = dishSchedules::with('usuario')->get();

        

        events: $this->events = $dishSchedules->map(function ($day) {
            $pendingSwaps = SwapRequest::where('status', 'pending')->pluck('from_dish_day_id')->unique();

            $statusIcons = [
                'completed' => '✅',
                'missed' => '❌',
                'swapped' => '🔄',
                'pending' => '📅',
            ];

            $isCompletedWithPhoto = '📸';
            $icon = $statusIcons[$day->status] ?? '📅';
            $swapped = $day->status === 'swapped';
            $isInSwap = $pendingSwaps->contains($day->id);
            $isCompleted = $day->status === 'completed';
            $hasPhoto = $day->photo_path !== null;

            $shiftLabel = '';
            if ($day->shift === 'tarde') $shiftLabel = ' ☀ Tarde';
            if ($day->shift === 'noite') $shiftLabel = ' 🎑 Noite';

            return [
                'id' => $day->id,
                'title' => ($hasPhoto ? $isCompletedWithPhoto : $icon) . ' ' . ($day->usuario->nome ?? 'Desconhecido') . $shiftLabel,
                'start' => $day->scheduled_date,
                'allDay' => true,

                'color' => match ($day->status) {
                    'completed' => "#10b981",
                    'missed' => '#ef4444',
                    'swapped' => $this->getUserColor($day->user_id),
                    default => $this->getUserColor($day->user_id)
                },

                'classNames' => $isCompleted ? 'line-through opacity-75' : ($swapped ? 'italic border-2 border-violet-500 animate-pulse' : ($isInSwap ? 'border-2 border-violet-500 animate-pulse' : '')),

                'extendedProps' => [
                    'status' => $day->status,
                    'notes' => $day->notes,
                    'user_id' => $day->user_id,
                    'has_photo' => $hasPhoto,
                    'in_swap' => $isInSwap
                ]
            ];
        })->toArray();
    }

    private function getUserColor($userId)
    {
        $colors = ['#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#3b82f6'];
        return $colors[$userId % count($colors)] ?? '#6b7280';
    }

    public function createEvent($data)
    {
        $date = $data['date'];
        $userId = $data['user_id'];

        dishSchedules::updateOrCreate(
            ['scheduled_date' => $date],

            ['user_id' => $userId,
            'status' => 'pending',
            'notes' => ''
            ]
        );

        $this->loadEvents();
        $this->dispatch('calendar-refresh');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => ' Evento Criado Com Sucesso!'
        ]);
    }

    public function getExistingAssignmentProperty() 
    {
        if (!$this->selectedDate) return null;

        return dishSchedules::with('usuario')->where('scheduled_date', $this->selectedDate)->first();
    }

    public function render()
    {
        return view('livewire.calendar');
    }
}
