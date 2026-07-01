<div>
    {{-- In work, do what you enjoy. --}}

    <!-- Toast Notifications -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2"></div>

    <div class="p-8 max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">{{ __('app.house_settings') }}</h1>

        @if ($house)
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow p-8">
                
                <div class="mb-10">
                    <label class="block text-sm font-medium mb-3 text-gray-800 dark:text-gray-200">{{ __('app.house_name') }}</label>
                    <div class="flex gap-4">
                        <input type="text" class="flex-1  rounded-2xl border p-4" wire:model="name" @readonly(!$isOwner)>
                        <button wire:click="updateHouseName" class="px-8 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-medium" @disabled(!$isOwner)>{{ __('app.update') }}</button>
                    </div>
                    @if (!$isOwner)
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">{{ __('app.only_owner_can_edit') }}.</p>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium mb-3 text-gray-800 dark:text-gray-200">{{ __('app.invite_code') }}</label>
                    <div class="flex gap-4">
                        <input type="text" id="invite-code-input" value="{{ $inviteCode }}" class="flex-1 text-gray-800 dark:text-gray-200 rounded-2xl border p-4 bg-gray-100 dark:bg-gray-700" readonly>
                        <button onclick="copyInviteCode()" class="px-8 bg-gray-700 dark:bg-indigo-600 dark:hover:bg-indigo-800 hover:bg-gray-800 text-white rounded-2xl font-medium">{{ __('app.copy') }}</button>
                    </div>
                    @if ($isOwner)
                        <button wire:click="generateNewInviteCode" class="mt-4 text-sm text-red-600 hover:text-red-800 font-medium rounded-2xl">{{ __('app.generate_code') }}</button>
                    @endif
                </div>
            </div>
        @else
            <p class="text-red-600">{{ __('app.no_house') }}.</p>
        @endif

        @if(!$isOwner)
            <div class="mt-10 mb-10 pt-6 border-t dark:border-gray-700">
                <button wire:click="leaveHouse" 
                        wire:confirm="{{ __('app.are_u_sure') }}"
                        class="w-full py-4 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-2xl font-medium flex items-center justify-center gap-2">
                    🚪 {{ __('app.leave') }}
                </button>
                <p class="text-xs text-gray-500 text-center mt-3">
                    {{ __('app.if_u_leave') }}.
                </p>
            </div>
        @endif

        @if ($isOwner)
            <button wire:click="openTransferModal" class="mt-6 w-full py-4 border border-red-300 text-red-600 hover:bg-red-50 rounded-2xl font-medium">👑 {{ __('app.transfer_property') }}</button>
        @endif
        
        @if($isOwner)
            <div class="mt-12 pt-8 border-t border-red-200 dark:border-red-900">
                <button wire:click="destroyHouse" 
                        wire:confirm="⚠ {{ __('app.are_u_sure') }}"
                        class="w-full py-4 bg-red-600 hover:bg-red-700 text-white rounded-2xl font-medium flex items-center justify-center gap-2">
                    🗑️ {{ __('app.destroy') }}
                </button>
                <p class="text-xs text-red-600 text-center mt-3">
                    {{ __('app.if_destroy') }}.
                </p>
            </div>
        @endif

    </div>

    @if ($showTransferModal)
        <div class="fixed inset-0 bg-black/70 flex items-center justify-center z-[100] p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-md mx-auto">
                <div class="p-6">
                    <h2 class="text-2xl font-bold mb-2 text-gray-800 dark:text-gray-200">{{__('app.transfer_property')}}</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">{{ __('app.pick_owner') }}.</p>

                    <select wire:model.live="newOwnerId" class="w-full rounded-2xl p-4 border">
                        <option value="">{{__('app.select_owner')}}</option>
                        @foreach ($house->users()->where('id', '!=', auth()->id())->get() as $user)
                            <option value="{{ $user->id }}">{{ $user->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-5 flex gap=3">
                    <button wire:click="closeTransferModal" class="flex-1 py-4 text-gray-600 dark:text-gray-400 hover:text-gray-200 hover:bg-gray-500 rounded-2xl">{{ __('app.cancel') }}</button>

                    <button wire:click="transferOwnership" class="flex-1 py-4 bg-red-600 hover:bg-red-700 text-white rounded-2xl" @disabled(empty($newOwnerId))>{{ __('app.transfer_property') }}</button>
                </div>
            </div>
        </div>
    @endif
    
</div>

@push('scripts')
    <script>
        window.copyInviteCode = function ()
        {
            console.log(@json(__('app.copied')));
            const codeInput = document.getElementById('invite-code-input');
            const code = codeInput.value;
             
            if (code) {
                navigator.clipboard.writeText(code).then(() => {
                    console.log('Codigo copiado');
                    @this.dispatch('notify', {
                        type: 'success',
                        message:  @js(__('app.copied'))
                    });
                    codeInput.style.background = '#10b981';
                    setTimeout(() => {
                        codeInput.style.background = '';
                    }, 800);
                    
                }).catch(() => {
                    console.log("Error");
                    $wire.dispatch('notify', {
                        type: 'error',
                        message: @js(__('app.copy_failed'))
                    });

                });
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
                console.log(data);
                showToast(data.type, data.message);
            });
        });
    </script>
@endpush