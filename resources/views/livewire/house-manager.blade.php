<div>
    {{-- If you look to others for fulfillment, you will never truly be fulfilled. --}}

    <!-- Toast Notifications -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2"></div>
    
    @if (session('warning'))
        <div class="mb-8 bg-amber-100 border-amber-400 text-amber-700 px-6 py-4 rounded-2xl">
            {{ session('warning') }}
        </div>
    @endif

    <div class="p-8 max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">{{ __('app.manage_house') }}</h1>

        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow p-8 mb-10">
            <h2 class="text-xl font-semibold mb-6 text-gray-800 dark:text-gray-200">{{ __('app.create_new_house') }}</h2>
            <div class="flex gap-4">
                <input type="text" wire:model="name" class="flex-1 rounded-2xl border p-4" placeholder="{{ __('app.placeholder_name') }}">
                <button wire:click="createHouse" class="px-8 bg-indigo-600 hover:bg-indigo-800 text-white rounded-2xl font-medium">{{ __('app.create_house') }}</button>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow p-8">
            <h2 class="text-xl font-semibold mb-6 text-gray-800 dark:text-gray-200">{{ __('app.enter_house') }}</h2>
            <input type="text" id="invite-code" class="w-full rounded-2xl border p-4 mb-4" placeholder="{{ __('app.placeholder_code') }}">
            <button onclick="joinWithCode()" class="w-full bg-green-600 hover:bg-green-800 text-white py-4 rounded-2xl font-medium">{{ __('app.enter_with_code') }}</button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function joinWithCode() {
            const code = document.getElementById('invite-code').value.trim();
            if (code) {
                @this.joinHouse(code);
            }
        }

        // Toast Nofifications
        function showToast(type, message) {
            console.log("Entrando na funcao Toast");
            const container = document.getElementById('toast-container');

            const toast = document.createElement('div');
            toast.className = `flex items-center gap-3 px-5 py-4 rounded-2xl shadow-xl text-white max-w-xs transition-all duration-300 transform translate-y-4 opacity-0`;

            if (type === 'success') {
                toast.classList.add('bg-green-600');
                toast.innerHTML = `<span class="text-2xl">✅</span><span>${message}</span>`;
            } else if (type === 'error') {
                toast.classList.add('bg-red-600');
                toast.innerHTML = `<span class="text-2xl">❌</span><span>${message}</span>`;
            } else {
                toast.classList.add('bg-blue-600');
                toast.innerHTML = `<span class="text-2xl">ℹ️</span><span>${message}</span>`;
            }

            container.appendChild(toast);

            setTimeout(() => {
            toast.style.transition = 'all 0.3s' ;
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
            }, 10);

            setTimeout(() => { 
                toast.style.transition = 'all 0.4s';
                toast.style.transform = 'translateY(20px)';
                toast.style.opacity = '0';

                setTimeout(() => toast.remove(), 400);
            }, 4000);
        }

        window.addEventListener('livewire:initialized', () => {
            console.log("Entrando no EventListener");
            Livewire.on('notify', (data) => {
                console.log(data[0].message);
                showToast(data[0].type, data[0].message);
            });
        });
    </script>
@endpush

