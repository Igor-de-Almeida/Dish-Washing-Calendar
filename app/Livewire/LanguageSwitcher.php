<?php

namespace App\Livewire;

use Livewire\Component;

class LanguageSwitcher extends Component
{
    public function setLocale($locale)
    {
        if (in_array($locale, ['pt', 'en'])) {
            session(['locale' => $locale]);
            app()->setLocale($locale);
        }

        $this->redirect(request()->header('Referer'), navigate: true);
        return redirect()->back();
    }

    public function render()
    {
        return view('livewire.language-switcher');
    }
}
