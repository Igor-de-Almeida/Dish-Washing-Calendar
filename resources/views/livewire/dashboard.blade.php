<div>
    {{-- Stop trying to control. --}}
    <div id="toast-container" class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2"></div>
    <div class="p-6 max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Dashboard - Estatísticas</h1>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-10">
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow">
                <p class="text-sm text-gray-800 dark:text-gray-200">Total de Dias</p>
                <p class="text-4xl font-bold text-gray-800 dark:text-gray-200">{{ $totalDays }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow">
                <p class="text-green-600 text-sm">Concluídos</p>
                <p class="text-4xl text-green-600 font-bold">{{ $completedDays }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow">
                <p class="text-amber-600 text-sm">Pendentes</p>
                <p class="text-4xl text-amber-600 font-bold">{{ $pendingDays }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow">
                <p class="text-blue-600 text-sm">Cumprimento</p>
                <p class="text-4xl text-blue-600 font-bold">{{ $monthlyCompletion }}%</p>
            </div>
        </div>

        <!-- LeaderBoard --> 
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow p-8">
            <h2 class="text-2xl font-semibold mb-6 text-gray-800 dark:text-gray-200">Leaderboard do Mês</h2>

            <div class="space-y-4 text-gray-800 dark:text-gray-200">
                @foreach ($userStats as $user)
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-2xl">
                        <div class="flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full" style="background-color> {{ $user['color'] }}"></div>
                            <span class="font-medium">{{ $user['name'] }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-sm text-gray-500">{{ $user['done_days'] }} / {{  $user['total_days']}}</span>
                            <span class="ml-4 font-bold text-lg">{{ $user['completion'] }}%</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
