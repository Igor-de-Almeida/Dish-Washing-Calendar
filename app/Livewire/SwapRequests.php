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
        $this->pendingRequests = SwapRequest::with(['fromUser', 'toUser', 'fromDishDay'])->where('to_user_id', Auth::id())->where('status', 'pending')->orderBy('created_at', 'desc')->get();
    }

    public function acceptRequest($requestId)
    {
        $request = SwapRequest::with(['fromDishDay', 'toDishDay'])->find($requestId);
        
        if (!$request || $request->to_user_id !== Auth::id()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Pedido Inválido!'
            ]);
            return;
        }

        // Realizar a troca
        $fromDay = $request->fromDishDay;
        $toDay = $request->toDishDay;

        if (!$fromDay || !$toDay) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Dias não encontrados.'
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
            'message' => 'Troca realizada com sucesso!'
        ]);
        $this->loadPendingRequests();    
        $this->dispatch('calendar-refresh');
        
    }

    public function rejectRequest($requestId)
    {
        $request = SwapRequest::findOrFail($requestId);

        if ($request && $request->to_user_id === Auth::id()) {
            $request->update(['status' => 'rejected']);
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Pedido recusado!'
            ]);
            $this->loadPendingRequests();
        }
    }

    public function render()
    {
        $received = SwapRequest::with(['fromUser', 'toUser', 'fromDishDay', 'toDishDay'])->where('status', 'pending')->where('to_user_id', Auth::id())->orderBy('created_at', 'desc')->get();

        $sent = SwapRequest::with(['fromUser', 'toUser', 'fromDishDay', 'toDishDay'])->where('from_user_id', Auth::id())->orderBy('created_at', 'desc')->get();

        $count = SwapRequest::with(['toUser'])->where('to_user_id', Auth::id())->where('status', 'pending')->count();

        $history = SwapRequest::with(['fromUser', 'toUser', 'fromDishDay', 'toDishDay'])->where(function ($q) {
            $q->where('from_user_id', Auth::id())->orWhere('to_user_id', Auth::id());})->whereIn('status', ['accepted', 'rejected'])->orderBy('created_at', 'desc')->get();

        return view('livewire.swap-requests', ['received' => $received, 'sent' => $sent, 'count' => $count, 'history' => $history]);
    }
}
