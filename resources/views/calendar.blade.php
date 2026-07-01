<x-app-layout>
    <x-slot name="header" class="flex items-center">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">🍽 {{ __('app.dish_washing_calendar') }}</h2>
            <div class="flex items-center gap-4"><livewire:language-switcher/>
                <button id="enableNotificationsBtn"
                        onclick="requestNotificationPermission()"
                        class="text-sm text-blue-600">
                    {{ __('app.enable_notifications') }}
                </button>
            </div>
        </div>
    </x-slot>
    

    <livewire:calendar />

</x-app-layout>