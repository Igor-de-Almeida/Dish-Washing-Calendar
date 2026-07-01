<?php

namespace App\Livewire;

use App\Models\dishSchedules;
use App\Models\SwapRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SwapRequests extends Component
{

    public $pendingRequests = [];
    public $tab = 'received';

    public function mount()
    {
        $this->loadPendingRequests();
    }

    public function loadPendingRequests() 
    {
        $this->pendingRequests = SwapRequest::with(['fromUser', 'toUser', 'fromDishDay'])->where('house_id', Auth::user()->house_id)->where('to_user_id', Auth::id())->where('status', 'pending')->orderBy('created_at', 'desc')->get();
    }

    public function acceptRequest($requestId)
    {

        //$this->validate((new \App\Http\Requests\SwapRequestForm())->rules());

        try {
            $request = SwapRequest::with(['fromDishDay', 'toDishDay'])->find($requestId);
        
            if (!$request || $request->to_user_id !== Auth::id()) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => __('app.invalid_request')
                ]);
                return;
            }

            // Realizar a troca
            $fromDay = $request->fromDishDay;
            $toDay = $request->toDishDay;

            if (!$fromDay || !$toDay) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => __('app.days_not_found')
                ]);
                return;
            }

            $oldFromUser = $fromDay->user_id;
            $oldToUser = $toDay->user_id;
            
            $fromDay->update(['user_id' => $oldToUser, 'status' => 'swapped']);
            $toDay->update(['user_id' => $oldFromUser, 'status' => 'swapped']);

            $request->update(['status' => 'accepted']);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => __('app.swap_completed')
            ]);
            $this->loadPendingRequests();    
            $this->dispatch('calendar-refresh');
        } catch(\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.error_swap')
            ]);
        }
        
        
    }

    public function rejectRequest($requestId)
    {

        //$this->validate((new \App\Http\Requests\SwapRequestForm())->rules());

        try {
            $request = SwapRequest::findOrFail($requestId);

            if ($request && $request->to_user_id === Auth::id()) {
                $request->update(['status' => 'rejected']);
                
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => __('app.deny_request')
                ]);
                $this->loadPendingRequests();
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.error_deny_request')
            ]);
        }

        
    }

    public function render()
    {
        $user = auth()->user();
        $userId = $user->id;
        $houseId = $user->house_id;

        $received = SwapRequest::with([
                'fromUser',
                'toUser',
                'fromDishDay',
                'toDishDay'
            ])
            ->where('house_id', $houseId)
            ->where('status', 'pending')
            ->where('to_user_id', $userId)
            ->latest()
            ->get();

        $sent = SwapRequest::with([
                'fromUser',
                'toUser',
                'fromDishDay',
                'toDishDay'
            ])
            ->where('house_id', $houseId)
            ->where('from_user_id', $userId)
            ->latest()
            ->get();

        $count = SwapRequest::where('house_id', $houseId)
            ->where('to_user_id', $userId)
            ->where('status', 'pending')
            ->count();

        $history = SwapRequest::with([
                'fromUser',
                'toUser',
                'fromDishDay',
                'toDishDay'
            ])
            ->where('house_id', $houseId)
            ->where(function ($query) use ($userId) {
                $query->where('from_user_id', $userId)
                    ->orWhere('to_user_id', $userId);
            })
            ->whereIn('status', ['accepted', 'rejected'])
            ->latest()
            ->get();

        return view('livewire.swap-requests', ['received' => $received, 'sent' => $sent, 'count' => $count, 'history' => $history]);
    }
}
