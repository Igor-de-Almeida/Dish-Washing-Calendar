<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('app.manage_schedule') }}
            </h2>
            <div class="flex items-center gap-4">
                <livewire:language-switcher/>
            </div>
        </div>
        
    </x-slot>

    <livewire:schedule-manager />
</x-app-layout>