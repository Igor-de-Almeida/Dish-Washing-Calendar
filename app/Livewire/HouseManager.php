<?php

namespace App\Livewire;

use App\Models\House;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Support\Str;

class HouseManager extends Component
{

    public $name = '';
    public $houses = [];

    public function mount()
    {
        $this->loadHouses();
    }

    public function loadHouses() 
    {
        $this->houses = House::where('owner_id', Auth::id())->get();
    }

    public function createHouse()
    {
        $this->validate((new \App\Http\Requests\CreateHouseRequest())->rules());

        try {
            $house = House::create([
                'nome' => $this->name,
                'invite_code' => Str::upper(Str::random(8)),
                'owner_id' => Auth::id()
            ]);

            Auth::user()->update([
                'house_id' => $house->id,
                'tipo' => 'admin'
            ]);

            $this->name = '';
            $this->loadHouses();
                
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => __('app.house_created') .'! '. __('app.invite_code') . ': '.  $house->invite_code
            ]);            
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.error_creating_house')
            ]);
        } finally {
            return redirect()->route('calendar');
        }
        
        
    }

    public function joinHouse($inviteCode)
    {
        $house = House::where('invite_code', $inviteCode)->first();

        if (!$house) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('app.invite_code') .' '. __('app.invalid') . '.'
            ]);
            return;
        }

        Auth::user()->update([
            'house_id' => $house->id,
            'tipo' => 'user'
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => __('app.entered_house') . ' ' . $house->nome .' ' .  __('app.with') . ' ' . __('app.success')
        ]);

        return redirect()->route('calendar');
    }

    public function render()
    {
        return view('livewire.house-manager');
    }
}
