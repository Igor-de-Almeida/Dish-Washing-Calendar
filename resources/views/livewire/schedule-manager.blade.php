<div>
    {{-- Success is as dangerous as failure. --}}
    <div class="p-8 max-w-5xl mx-auto">
        <div id="toast-container" class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2"></div>
        <h1 class="text-3xl font-bold mb-8">Gestão de Escala - Administração</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-3xl shadow p-8">
                <h2 class="text-2xl font-semibold mb-6 text-gray-800 dark:text-gray-200">Gerar Nova Escala</h2>
                <div class="space-y-6">
                    <h3 class="font-semibold mb-4 text-gray-800 dark:text-gray-200">Selecionar Utilizadores para a Escala</h3>
                    @foreach ($usuarios as $user)
                        <label class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-700 rounded-2xl cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                            <input type="checkbox" wire:model="selectedUsers" value="{{ $user->id }}" class="w-5 h-5 text-indigo-600">

                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full" style="background-color: {{ $this->getUserColor($user->id) }}"></div>
                                <span>{{ $user->nome }}</span>
                            </div>
                        </label>
                    @endforeach
                    <div>
                        <label for="" class="block text-sm font-medium mb-2 text-gray-800 dark:text-gray-200">Tipo de Escala</label>
                        <select wire:model="scaleType" class="w-full rounded-2xl p-4 border">
                            <option value="weekly_rotation">Rotação Semanal</option>
                            <option value="fixed_days">Dias Fixos da Semana</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-800 dark:text-gray-200">Mês de Início</label>
                        <input type="month" wire:model="startMonth" class="w-full rounded-2xl p-4 border">
                    </div>

                    <button wire:click="generateSchedule" wire:loading.attr="disabled" class="w-full bg-indigo-600 hover:bg-indigo-700 text-gray-800 dark:text-white py-4 rounded-2xl font-medium text-lg">Gerar Escala Automática</button>

                    <button wire:click="clearGeneratedSchedule" onclick="return confirm('Tem certeza que deseja limpar toda a escala deste mês? Esta ação não pode ser desfeita.')" class="w-full mt-4 bg-red-600 hover:bg-red-700 text-white dark:text-gray-800 py-4 rounded-2xl font-medium text-lg flex items-center justify-center gap-2">🗑️ Limpar Escala Gerada</button>

                    <button wire:click="exportToPdf" class="w-full mt-4 bg-emerald-600 hover:bg-emerald-700 text-white dark:text-gray-800 py-4 rounded-2xl font-medium text-lg flex items-center justify-center gap-2">📄 Exportar para PDF</button>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow p-8">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">Utilizadores: {{ count($usuarios ?? []) }}</h3>
                <div class="space-y-3">
                    @foreach ($usuarios ?? [] as $user)
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-2xl">
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full" style="background-color: {{ $this->getUserColor($user->id) }}"></div>
                                <span>{{ $user->nome }}</span>
                            </div>
                            <span class="text-xs px-3 py-1 bg-gray-200 dark:bg-gray-600 rounded-full">{{ $user->tipo }}</span>
                        </div>
                        
                    @endforeach
                    
                </div>
            </div>
        </div>
    </div>

</div>

