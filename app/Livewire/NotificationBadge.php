<?php

namespace App\Livewire;

use App\Models\SwapRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBadge extends Component
{
    public $count = 0;

    protected $listeners = [
        'refresh-badge' => 'updateCount'
    ];

    public function mount() 
    {
        SwapRequest::where('to_user_id', auth()->id())->where('status', 'pending')->get();;
    }

    public function loadNotifications()
    {
        $this->count = SwapRequest::with(['toUserId'])->where('to_user_id', Auth::id())->where('status', 'pending')->count();
    }

    public function render()
    {
        $count = SwapRequest::with(['toUser'])->where('to_user_id', Auth::id())->where('status', 'pending')->count();

        if ($this->count === 0) {
            $this->loadNotifications();
        }

        return view('livewire.notification-badge', ['count' => $count]);
    }
}
