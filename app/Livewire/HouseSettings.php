<?php

namespace App\Livewire;

use App\Models\dishSchedules;
use App\Models\SwapRequest;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Str;

class HouseSettings extends Component
{

    public $house;
    public $name = '';
    public $inviteCode ='';
    public $isOwner = false;

    function mount()
    {
        $this->house = auth()->user()->house;
        $this->name = $this->house?->nome ?? '';
        $this->inviteCode = $this->house?->invite_code ?? '';
        $this->isOwner = $this->house->owner_id === auth()->id();
    }

    public function leaveHouse()
    {
        $user = auth()->user();

        if (!$user->house_id) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.no_belong')
            ]);
            return;
        }

        if ($this->house->owner_id === auth()->id()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.no_leave_4_owner') . '.'
            ]);
            return;
        }

        $houseName = $user->house->name;

        $user->update(['house_id' => null]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' =>  __('app.leave_house').' '. $houseName . ' ' . __('app.enter_other_house')
        ]);

        return redirect()->route('house.manager');
    }

    public function updateHouseName()
    {
        if (!$this->isOwner) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.only_owner_can_edit')
            ]);
            return;
        }

        $this->validate([
            'name' => 'required|string|max:100'
        ]);

        try {
            if ($this->house && $this->house->owner_id === auth()->id()) {
                $this->house->update([
                    'nome' => $this->name, 
                ]);

                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => __('app.house_name_success')
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.error_edit_name')
            ]);
        }
        
    }

    public function generateNewInviteCode()
    {
        if (!$this->isOwner) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.only_owner_generate_code')
            ]);
            return;
        }
        
        try {
            if ($this->house && $this->house->owner_id === auth()->id()) {
                $newCode = Str::upper(Str::random(8));
                $this->house->update(['invite_code' => $newCode]);
                $this->inviteCode = $newCode;

                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => __('app.new_code_generated').': '. $newCode
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.error_new_code')
            ]);
        }
        
    }

    public $newOwnerId = null;
    public $showTransferModal = false;

    public function openTransferModal()
    {
        $this->newOwnerId = null;
        $this->showTransferModal = true;
    }

    public function transferOwnership()
    {
        $this->validate([
            'newOwnerId' => 'required|exists:usuarios,id'
        ]);

        if (!$this->isOwner) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.owner_transfer') . '.'
            ]);
            return;
        }

        $newOwner = Usuario::findOrFail($this->newOwnerId);

        if (!$newOwner || $newOwner->house_id !== $this->house->id) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.selected_user') . '.'
            ]);
            return;
        }

        // Transferir propriedade
        $this->house->update(['owner_id' => $this->newOwnerId]);

        // O antigo user vira user normal
        auth()->user()->update(['tipo' => 'user']);

        // O novo dono vira admin
        $newOwner->update(['tipo' => 'admin']);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' =>  __('app.house_success'). $newOwner->nome .'!'
        ]);

        $this->closeTransferModal();
        $this->mount();
    }

    public function closeTransferModal()
    {
        $this->showTransferModal = false;
        $this->newOwnerId = null;
    }

    public function destroyHouse()
    {
        if (!$this->isOwner) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.owner_destroy'). '.'
            ]);
            return;
        }

        $houseName = $this->house->nome;

        // Apaga tudo relacionado a casa
        DB::transaction(function () {
            // Apaga trocas
            SwapRequest::where('house_id', $this->house->id)->delete();

            // Apaga dias da escala
            dishSchedules::where('house_id', $this->house->id)->delete();

            // Apaga a casa
            $this->house->delete();
        });

        // Remove house_id de todos os usuarios
        Usuario::where('house_id', $this->house->id)->update(['house_id' => null]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => __('app.the_house') .': '. $houseName .' '. __('app.success_destroy') 
        ]);

        return redirect()->route('house.manager');
    }

    public function render()
    {
        return view('livewire.house-settings');
    }
}
