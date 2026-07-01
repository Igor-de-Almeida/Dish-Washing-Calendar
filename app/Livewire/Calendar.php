<?php

namespace App\Livewire;

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
        $this->usuarios = Usuario::where('house_id', Auth::user()->house_id)->get();
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

       //$this->validate((new \App\Http\Requests\MarkAsDoneRequest())->rules());
        
        try {
            $dish = dishSchedules::findOrFail($this->photoDayId);

            if ($dish && $this->photo) {

                $path = $this->photo->store('dish-photos', 'public');

                $dish->update([
                    'status' => 'completed',
                    'photo_path' => $path,
                    'notes' => __('app.marked_as_done_with_photo') . ' '. now()->toDateTimeString()
                ]);

                $this->reset('photo');
                $this->loadEvents();
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => __('app.marked_as_done')
                ]);
                $this->dispatch('calendar-refresh');
            } else if ($dish) {

                $dish->update([
                    'status' => 'completed',
                    'notes' => __('app.marked_as_done_in') .'. '. now()->toDateTimeString()
                ]);

                $this->loadEvents();
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => __('app.marked_as_done')
                ]);
                $this->dispatch('calendar-refresh');
            }
            $this->closePhotoModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.error_to_mark_as_done')
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
        //$this->validate((new \App\Http\Requests\SwapRequestForm())->rules());

        if (!$this->selectedSwapDayId || !$this->swapTargetUserId) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.fill_every_field')
            ]);
            return;
        }
        
        try {

            $day = dishSchedules::findOrFail($this->selectedSwapDayId);
            $targetDay = dishSchedules::findOrFail($this->targetDayId);

            if (!$day || $day->user_id !== FacadesAuth::id()) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => __('app.only_swap_ur_day')
                ]);
                $this->closeSwapModal();
                $this->closeActionModal();
                return;
            }

            $swapRequest = SwapRequest::create([
                'house_id' => auth()->user()->house_id,
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
                'message' => __('app.swap_request_success')
            ]);
            $this->closeSwapModal();
            $this->closeActionModal();
            event(new \App\Events\SwapRequestCreated($swapRequest));
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.error_swap_request')
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
                'message' => __('app.no_permission_to_edit_day')
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

        if (!$this->isAdmin) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.only_admin_can_mark')
            ]);
            $this->closeActionModal();
            $this->dispatch('calendar-refresh');
            return;
        }

        try {
            if ($this->doneEventId) {
                $dishSchedule = dishSchedules::findOrFail($this->doneEventId);

                if ($dishSchedule) {
                    $dishSchedule->update([
                        'status' => 'missed',
                        'notes' => __('app.missed_dish_date').': ' . now()->toDateTimeString()
                    ]);

                    $this->loadEvents();
                    $this->dispatch('notify', [
                        'type' => 'success',
                        'message' => __('app.mark_as_missed')
                    ]);
                    $this->dispatch('calendar-refresh');
                }
            }

            $this->closeActionModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.error_marked_as_missed')
            ]);
        } finally {
            $this->loading = false;
        }
        
    }

    public function resetToScheduled()
    {
        $this->loading = true;

        if (!$this->isAdmin) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.only_admin_can_redo')
            ]);
            $this->closeActionModal();
            $this->dispatch('calendar-refresh');
            return;
        }

        try {
            if ($this->doneEventId) {
                $dishSchedule = dishSchedules::findOrFail($this->doneEventId);
                if ($dishSchedule) {
                    $dishSchedule->update([
                        'status' => 'pending',
                        'notes' => __('app.marked_as_pending') .' '. now()->toDateString()
                    ]);

                    $this->loadEvents();
                    $this->dispatch('notify', [
                        'type' => 'success',
                        'message' => __('app.marked_as_redefined')
                    ]);
                    $this->dispatch('calendar-refresh');
                }
            }

            $this->closeActionModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.error_marked_as_redefined')
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

        if (!$this->isAdmin) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.only_admin_can_act')
            ]);
            $this->closeActionModal();
            $this->closeModal();
            $this->dispatch('calendar-refresh');
            return;
        }

        try {
            $this->validate([
                'selectedUserId' => 'required|exists:usuarios,id',
                'selectedDate' => 'required|date',
            ]);


            if ($this->selectedDate && $this->selectedUserId)
            {
                dishSchedules::updateOrCreate
                (
                    ['scheduled_date' => $this->selectedDate,
                    'house_id' => auth()->user()->house_id],

                    ['user_id' => $this->selectedUserId, 
                    'status' => 'pending', ],
                );

                $user = Usuario::find($this->selectedUserId);

                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => __('app.date_assigned_to_user') . ': ' . $user->nome ?? __('app.unknown')
                ]);
                $this->loadEvents();
                $this->dispatch('calendar-refresh' , events: $this->events);
            }

            $this->closeModal();        
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.error_assign_day')
            ]);
        } finally {
            $this->loading = false;
        }
        
    }

    public function loadEvents()
    {
        //dd(dishSchedules::with('usuario')->where('house_id', auth()->user()->house_id)->get());
        $dishSchedules = dishSchedules::with('usuario')->where('house_id', auth()->user()->house_id)->get();

        
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
                'title' => ($hasPhoto ? $isCompletedWithPhoto : $icon) . ' ' . ($day->usuario->nome ?? '{{__(\'app.unknown\')}}') . $shiftLabel,
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
            'message' => __('app.event_created')
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
