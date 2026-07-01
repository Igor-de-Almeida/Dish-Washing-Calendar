<div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto"> 
    <!-- Toast Notifications -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2"></div>
    <h1 class="text-2xl sm:text-3xl font-bold mb-6">{{ __('app.dish_washing_calendar') }}</h1>

    <div class="mb-6 hidden sm:flex gap-2 flex-wrap sm:gap-3">
        @foreach ($usuarios as $user)
            <div class="flex items-center gap-2.5 bg-white dark:bg-gray-800 px-4 py-2.5 rounded-lg shadow text-sm border border-gray-100 mb-4 flex-wrap">

                <div class="w-5 h-5 rounded-full flex-shrink-0 ring-2 ring-offset-2 ring-offset-white dark:ring-offset-gray-900" style="background-color: {{ $this->getUserColor($user->id) }}"></div>

                <span class="font-semibold text-gray-800 dark:text-gray-200 text-sm">{{ $user->nome }}</span>
            </div>
        @endforeach
    </div>

    <div id="calendar" data-events='@json($events)' class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl text-gray-800 dark:text-gray-200 p-3 sm:p-6 min-h-[520px] md:min-h-[650px]"></div>

    <!-- Modal -->
    @if ($showModal)
        <div class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4 sm:p-6">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl max-w-md w-full mx-4 overflow-hidden">
                
                <!-- Header -->
                <div class="px-6 py-5 border-b dark:border-gray-700">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ __('app.assign_day') }}</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Data: <strong>{{ $selectedDate }}</strong>
                    </p>
                </div>

                <!-- Body -->
                <div class="p-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        {{ __('app.who_will_wash') }}?
                    </label>
                    
                    <select wire:model.live="selectedUserId" 
                            class="w-full rounded-2xl border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500 py-4 px-5 text-base">
                        <option value="">{{ __('app.select_user') }}...</option>
                        @foreach ($usuarios as $user)
                            <option value="{{ $user->id }}" style="color: {{ $this->getUserColor($user->id) }}">
                                • {{ $user->nome ?? $user->name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Info se ja existir atribuicao -->
                    @if ($existingAssignment = \App\Models\dishSchedules::where('scheduled_date', $selectedDate)->first())
                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-3">⚠ {{ __('app.already_assign_to') }}: <strong>{{ $existingAssignment->usuario->nome ?? 
                         __('app.unknown') }}</strong></p>
                    @endif
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-5 flex flex-col sm:flex-row justify-end gap-3 border-t dark:border-gray-600">
                    <button 
                        wire:click="closeModal"
                        class="px-6 flex-1 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-2xl font-medium text-base transition">
                        {{ __('app.cancel') }}
                    </button>
                    
                    <button 
                        wire:click="assignUser"
                        class="px-6 flex-1 text-base py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-medium transition disabled:opacity-50"
                        @disabled(empty($selectedUserId))>
                        @if ($loading)
                            <span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2"></span>
                            {{ __('app.assigning') }}...
                        @else
                            {{ __('app.confirm_assignment') }}
                        @endif
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Accoes (Done / Missed / Reset) -->
    @if ($showActionModal)
        <div class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl max-w-md w-full mx-auto overflow-hidden">
                
                <div class="p-8 text-center">
                    <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-200 leading-tight">{{ $doneEventTitle }}</h2>
                    
                    <div class="space-y-3">
                        <button wire:click="openPhotoModal({{ $doneEventId }})" class="w-full py-4 bg-green-600 hover:bg-green-700 text-white rounded-2xl font-medium text-base transition">✅ {{ __('app.mark_as_done') }}</button>

                        <button wire:click="markAsMissed" class="w-full py-4 bg-red-600 hover:bg-red-700 text-white rounded-2xl font-medium text-base transition">❌{{__('app.mark_as_missed')}}</button>

                        <button wire:click="resetToScheduled" wire:confirm="⚠ {{ __(' app.really?') }}"  class="w-full py-4 bg-gray-500 hover:bg-gray-600 text-white rounded-2xl font-medium text-base transition">🔄 {{ __('app.back_to_schedule') }}</button>

                        <button wire:click="openSwapModal({{ $doneEventId }})" class="w-full py-4 bg-violet-600 hover:bg-violet-700 text-white rounded-2xl font-medium transition">🔄 {{ __('app.swap_with_user') }}</button>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex justify-center border-t">
                     <button 
                        wire:click="closeActionModal"
                        class="px-8 py-3 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-2xl text-base">
                        {{ __('app.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Pedido de Troca -->
    @if ($showSwapModal)
        <div class="fixed inset-0 bg-black/70 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl max-w-md w-full mx-4">
                <div class="p-6">
                    <h2 class="text-2xl font-bold mb-4 text-gray-800 dark:text-gray-200">{{ __('app.ask_swap') }}</h2>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-800 dark:text-gray-200">{{ __('app.my_day') }}</label>
                            <input type="text" value="{{ \App\Models\dishSchedules::find($selectedSwapDayId)->scheduled_date ?? '' }}" class="w-full rounded-2xl p-4 bg-gray-100 dark:bg-gray-700 mb-4 text-gray-800 dark:text-gray-200" disabled>
                            <label class="block text-sm font-medium mb-2 text-gray-800 dark:text-gray-200">{{ __('app.swap_with') }}</label>
                            <select wire:model.live="swapTargetUserId" class="w-full rounded-2xl p-4 border text-gray-800 dark:text-gray-200 dark:bg-gray-700">
                                <option value="">{{ __('app.select_user') }}...</option>
                                @foreach ($usuarios as $user)
                                    @if($user->id !== auth()->id())
                                        <option value="{{ $user->id }}">
                                            {{ $user->nome }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        @if ($swapTargetUserId)
                            <div>
                                <label class="block text-sm font-medium mb-2 text-gray-800 dark:text-gray-200">{{ __('app.target_day') }}</label>
                                <select wire:model="targetDayId" class="w-full rounded-2xl p-4 border text-gray-800 dark:text-gray-200 dark:bg-gray-700" required>
                                    <option value="">{{ __('app.select_day') }}...</option>
                                    @foreach (\App\Models\dishSchedules::where('user_id', $swapTargetUserId)->where('house_id', auth()->user()->house_id)->where('scheduled_date', '>=', now()->format('Y-m-d'))->orderBy('scheduled_date')->get() as $day)
                                        <option value="{{ $day->id }}">{{ $day->scheduled_date }} - {{ $day->shift === 'tarde' ? 'Tarde' : ($day->shift === 'noite' ? 'Noite' : 'Dia Inteiro') }}
                                            @if ($day->status !== 'pending') ({{ ucfirst($day->status) }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-800 dark:text-gray-200">{{ __('app.justification') }}</label>
                            <textarea wire:model="swapNotes" class="w-full rounded-2xl p-4 border dark:bg-gray-700 text-gray-800 dark:text-gray-200" rows="3" placeholder="Ex: {{__('app.im_sick') }}?"></textarea>
                        </div>
                    </div>
                </div>  

                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex justify-end gap-3">
                    <button wire:click="closeSwapModal" class="px-6 py-3 text-gray-600 hover:bg-gray-100 rounded-2xl">{{ __('app.cancel') }}</button>
                    <button wire:click="sendSwapRequest" class="px-6 py-3 bg-violet-600 text-white rounded-2xl disabled:opacity-50" @disabled(empty($swapTargetUserId))>
                        @if ($loading)
                            <span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2"></span>
                            {{ __('app.sending') }}...
                        @else
                            {{ __('app.send_swap_request') }}
                        @endif
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Prova Fotografica -->
    @if ($showPhotoModal)
        <div class="fixed inset-0 bg-black/80 flex items-center justify-center z-[100] p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">

                <div class="p-6">
                    <h2 class="text-2xl font-bold text-center mb-2 text-gray-800 dark:text-gray-200">{{ __('app.mark_as_done') }}</h2>
                    <p class="text-center text-gray-500 dark:text-gray-400  mb-6">{{ __('app.take_or_select_photo') }}</p>

                    <div id="photo-preview" class="w-full aspect-video bg-gray-100 dark:bg-gray-700 rounded-2xl flex items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-600 mb-6 overflow-hidden">
                        <div class="text-center p-8">
                            <span class="text-5xl mb-3 blcok">📸</span>
                            <p class="text-gray-500">{{ __('app.no_photo_selected') }}</p>
                        </div>
                    </div>
                    

                    <form id="photo-form" enctype="multipart/form-data">
                        <input type="file" name="photo" id="photo-input" accept="image/*" capture="environment" class="hidden">
                    </form>

                    <div class="grid grid-cols-2 gap-4">
                        <label for="photo-input" class="cursor-pointer">
                            <input type="file" id="photo-input" accept="image/*" capture="environment" class="hidden">
                            <div class="bg-blue-600 hover:bg-blue-700 text-gray-800 dark:text-gray-200 py-5 rounded-2xl text-center font-medium flex items-center justify-center gap-2">📸 {{ __('app.take_photo') }}

                            </div>
                        </label>

                        <label for="photo-input" class="cursor-pinte">
                            <input type="file" id="gallery-input" accept="image/*" class="hidden">
                            <div class="bg-gray-700 hover:bg-gray-800 text-gray-800 dark:text-gray-200 py-5 rounded-2xl text-center font-medium">📂 {{ __('app.galery') }}

                            </div>
                        </label>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-5 flex gap-3">
                    <button wire:click="closePhotoModal" class="flex-1 py-4 text-gray-600 dark:text-gray-400 hover:text-gray-700 hover:bg-gray-500 rounded-2xl font-medium">{{ __('app.cancel') }}</button>

                    <button onclick="submitPhoto()" id="confirm-btn" class="flex-1 py-4 bg-green-600 hover:bg-green-700 text-gray-800 dark:text-gray-200 rounded-2xl font-medium disabled:opacity-50" @disabled($loading || !$photo)>
                        @if ($loading)
                            <span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2"></span>
                            {{ __('app.saving') }}...
                        @else
                            {{ __('app.confirm') }}
                        @endif
                    </button>
                </div>
            </div>
        </div>
    @endif

@push('scripts')
<script>
    console.log('✅ Calendar script loaded');
    let calendarInstance = null;

    function initCalendar() {
        console.log('🔄 Trying to initialize FullCalendar...');
        const locale = '{{ app()->getLocale() }}';
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) {
            console.error('❌ Calendar element not found!');
            return;
        }

        if (calendarInstance)
        {
            calendarInstance.destroy();
        }

        const isMobile = window.innerWidth < 768;
        const isDark = document.documentElement.classList.contains('dark');
        const events = JSON.parse(calendarEl.dataset.events || '[]');

        calendarInstance = new window.FullCalendar.Calendar(calendarEl, {
            plugins: [
                    window.FullCalendar.dayGridPlugin, 
                    window.FullCalendar.interactionPlugin
            ],
            initialView: isMobile ? 'dayGridWeek' : 'dayGridMonth',
            height: 'auto',
            contentHeight: isMobile ? '500' : 'auto',
            expandRows: true,
            // === TEMAS ===
            themeSystem: 'standard',
            dayHeaderClassNames: isDark ? 'text-gray-300' : 'text-gray-700',
            dayCellClassNames: function(arg) {
                const isWeekend = arg.date.getDay() === 0 || arg.date.getDay() === 6;
                return isWeekend ? (isDark ? 'bg-gray-800 dark:bg-gray-200' : ' bg-white dark:bg-gray-800')  : (isDark ? 'dark:bg-gray-900' : '');
            },
            eventClassNames: function (info) {
                if (info.event.extendedProps?.in_swap) {
                    return ['sm:text-base py-1 px-2 rounded-lg shadow-sm text-sm font-medium animate-pulse']
                } else {
                    return ['sm:text-base py-1 px-2 rounded-lg shadow-sm text-sm font-medium']
                }
            },
            eventTextColor:  isDark ? '#e0e7ff' : '#ffffff',
            eventBackgroundColor: isDark ? '#6366f1' : '#4f46e5',
            eventBorderColor: isDark ? '#6366f1' : '#4f46e5',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: isMobile ? 'dayGridWeek' : 'dayGridMonth,dayGridWeek'
            },
            // Linguagem
            locale: locale,
            dayHeaderFormat: { weekday: 'short' },
            weekText: locale === 'pt' ? 'Sem' : 'Wk',
            allDayText: locale === 'pt' ? 'Dia inteiro' : 'All day',
            buttonText: {
                today: locale === 'pt' ? 'Hoje' : 'Today',
                month: locale === 'pt' ? 'Mês' : 'Month',
                week: locale === 'pt' ? 'Semana' : 'Week'
            },
            //Reposividade
            views: {
                dayGridMonth: {
                    dayMaxEvents: 4,
                },

                dayGridWeek: {
                    dayMaxEvents: 6,
                }
            },
            events: events,
            editable: true,
            // Mobile
            dayMaxEvents: isMobile ? 2 : 4, 
            eventDisplay: 'block',
            eventTimeFormat: { hour: '2-digit', minute: '2-digit', merediem: false},
            eventClick: function(info) {
                const status = info.event.extendedProps?.status || 'pending';
                const isMyDay = true;
                if (status === 'done') {
                    @this.openDoneModal()
                }
                @this.openActionModal(info.event.id, info.event.title, info.event.status);
            },
            dateClick: function(info) {
                console.log('📅 Date clicked:', info.dateStr);
                @this.openModal(info.dateStr);

            }
        });

        calendarEl._calendarInstance = calendar;
        calendarInstance.render();

        console.log('✅ FullCalendar rendered successfully!');
    }

    // Multiple triggers
    window.addEventListener('load', () => {
        initCalendar();
    });
    document.addEventListener('DOMContentLoaded', initCalendar);
    document.addEventListener('livewire:navigated', initCalendar);
    document.addEventListener('livewire:initialized', () => setTimeout(initCalendar, 100));

    window.addEventListener('calendar-refresh', () => {
        console.log("Actualizando Calendario!");
        initCalendar();
    });

    //Important: Refresh calendar when Livewire updates data
    //document.addEventListener('calendar-updated', initCalendar);

    // Último recurso: executar várias vezes
    setTimeout(initCalendar, 300);
    setTimeout(initCalendar, 800);

    if (document.documentElement.classList.contains('dark')) {
        calendarInstance.setOption('eventTextColor', '#f3f4f6');
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

    // Preview de foto
    let currentPhotoFile = null;

    document.addEventListener('change', function (e) {
        console.log("Entrando no evento de foto");
        if (e.target.id === 'photo-input' && e.target.files[0]) {
            currentPhotoFile = e.target.files[0];
            const reader = new FileReader();
            reader.onload = function(ev) {
                document.getElementById('photo-preview').innerHTML = `<img src="${ev.target.result}" class="max-h-64 object-contain w-full">`;
            };
            reader.readAsDataURL(currentPhotoFile);

            document.getElementById('confirm-btn').disabled = false;
        }
    });

    function triggerGallery()
    {
        const input = document.getElementById('photo-input');
        input.capture = '';
    }

    function submitPhoto() 
    {
        console.log("Entrando na funcao para submeter foto");

        if (!currentPhotoFile) {
            alert("{{ __('app.take_or_select_photo') }}.");
            return;
        }

        const formData = new FormData();
        formData.append('photo', currentPhotoFile);

        
        @this.upload('photo', currentPhotoFile, () => {
            @this.markAsDoneWithPhoto();
        });
    }

    // Fechar modals ao clicar fora
        document.addEventListener('click', function(e) {
            const modals = document.querySelectorAll('.fixed.inset-0');
            
            modals.forEach(modal => {
                
                if (modal.contains(e.target) && !e.target.closest('.rounded-3xl')) {
                    
                    if (typeof @this.closeModal === 'function') @this.closeModal();
                    if (typeof @this.closeActionModal === 'function') @this.closeActionModal();
                    if (typeof @this.closeSwapModal === 'function') @this.closeSwapModal();
                    if (typeof @this.closePhotoModal === 'function') @this.closePhotoModal();
                }
            });
        });

        
</script>
@endpush