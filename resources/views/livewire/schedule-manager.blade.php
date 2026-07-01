<div>
    {{-- Success is as dangerous as failure. --}}
    <div class="p-8 max-w-5xl mx-auto">
        @if (session('warning'))
            <div class="mb-8 bg-amber-100 border border-amber-400 text-amber-700 px-6 py-4 rounded-2xl flex items-start gap-3">
                <span class="text-2xl">⚠️</span>
                <span>{{ session('warning') }}</span>
            </div>
        @endif
        <div id="toast-container" class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2"></div>
        <h1 class="text-3xl font-bold mb-8">{{ __('app.manage_schedule') }} - {{ __('app.administration') }}</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-3xl shadow p-8">
                <h2 class="text-2xl font-semibold mb-6 text-gray-800 dark:text-gray-200">{{ __('app.generate_scale') }}</h2>
                <div class="space-y-6">
                    <h3 class="font-semibold mb-4 text-gray-800 dark:text-gray-200">{{ __('app.select_user_for_scale') }} </h3>
                    @foreach ($usuarios as $user)
                        <label class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-700 rounded-2xl cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                            <input type="checkbox" wire:model="selectedUsers" value="{{ $user->id }}" class="w-5 h-5 text-indigo-600">

                            <div class="flex items-center gap-3 text-gray-800 dark:text-gray-200">
                                <div class="w-6 h-6 rounded-full" style="background-color: {{ $this->getUserColor($user->id) }}"></div>
                                <span>{{ $user->nome }}</span>
                            </div>
                        </label>
                    @endforeach
                    <div>
                        <label for="" class="block text-sm font-medium mb-2 text-gray-800 dark:text-gray-200">{{ __('app.type_of_scale') }}</label>
                        <select wire:model="scaleType" class="w-full rounded-2xl p-4 border">
                            <option value="weekly_rotation">{{ __('app.weekly_rotation') }}</option>
                            <option value="fixed_days">{{ __('app.fixed_days') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-800 dark:text-gray-200">{{ __('app.start_month') }}</label>
                        <input type="month" wire:model="startMonth" class="w-full rounded-2xl p-4 border">
                    </div>

                    <button wire:click="generateSchedule" wire:loading.attr="disabled" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white  py-4 rounded-2xl font-medium text-lg">{{ __('app.generate_auto_scale') }}</button>

                    <button wire:click="clearGeneratedSchedule" wire:confirm="{{ __('app.clear_scale') }}." class="w-full mt-4 bg-red-600 hover:bg-red-700 text-white  py-4 rounded-2xl font-medium text-lg flex items-center justify-center gap-2">🗑️ {{ __('app.clear_generated_scale') }}</button>

                    <button wire:click="exportToPdf" class="w-full mt-4 bg-emerald-600 hover:bg-emerald-700 text-white py-4 rounded-2xl font-medium text-lg flex items-center justify-center gap-2">📄 {{ __('app.export_to_pdf') }}</button>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow p-8">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ __('app.users') }}: {{ count($usuarios ?? []) }}</h3>
                <div class="space-y-3">
                    @foreach ($usuarios ?? [] as $user)
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-2xl">
                            <div class="flex items-center gap-3 text-gray-800 dark:text-gray-200">
                                <div class="w-6 h-6 rounded-full" style="background-color: {{ $this->getUserColor($user->id) }}"></div>
                                <span>{{ $user->nome }}</span>
                            </div>
                            <span class="text-xs px-3 py-1 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-full">{{ $user->tipo }}</span>
                        </div>
                        
                    @endforeach
                    
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
    <script>
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