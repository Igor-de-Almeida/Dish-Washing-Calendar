<div>
    {{-- Because she competes with no one, no one can compete with her. --}}
    <!-- Toast Notifications -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2"></div>
    <div class="p-8 max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Pedidos de Troca</h1>

        <div class="flex gap-4 mb-8 border-b">
            <button wire:click="$set('tab', 'received')" class="pb-4 px-6 font-medium {{ $tab === 'received' ? 'border-b-4 border-violet-600 text-violet-600' : 'text-gray-500' }}">Recebidos</button>
            <button wire:click="$set('tab', 'sent')" class="pb-4 px-6 font-medium {{ $tab === 'sent' ? 'border-b-4 border-violet-600 text-violet-600' : 'text-gray-500' }}">Enviados</button>
            <button wire:click="$set('tab', 'history')" class="pb-4 px-6 font-medium {{ $tab === 'history' ? 'border-b-4 border-violet-600 text-violet-600' : 'text-gray-500' }}">Histórico</button>
        </div>

        @if ($tab === 'received')
            <h2 class="text-xl font-semibold mb-4">Pedidos Recebidos</h2>
            @if ($received->isEmpty())
                <p class="text-gray-500">Nenhum Pedido Recebido.</p>
            @else
                @foreach ($received as $req)
                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 mb-4 flex justify-between items-center text-gray-800 dark:text-gray-200">
                        <div>
                            <strong>{{ $req->fromUser->nome }}</strong> quer trocar <strong>{{ $req->fromDishDay->scheduled_date }}</strong> por <strong>{{ $req->toDishDay?->scheduled_date }}</strong>
                            @if ($req->notes)
                                <p class="text-sm text-gray-500 mt-1">{{ $req->notes }}</p>
                            @endif
                        </div>
                        <div class="flex gap-3">
                            <button wire:click="rejectRequest({{ $req->id }})" class="px-5 py-2.5 text-red-600 hover:bg-red-50 rounded-2xl font-medium">Recusar</button>
                            <button wire:click="acceptRequest({{ $req->id }})" class="px-5 py-2.5 bg-green-600 text-white rounded-2xl font-medium">Aceitar</button>
                        </div>
                    </div>
                @endforeach
            @endif
        @elseif($tab === 'sent')
            <h2 class="text-xl font-semibold mb-4">Meus Pedidos Enviados</h2>
            @if ($sent->isEmpty())
                <p class="text-gray-500">Nenhum pedido enviado.</p>
            @else
                @foreach ($sent as $req)
                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 mb-4">
                        <p>Pedido enviado para <strong>{{ $req->toUser->nome }}</strong></p>
                        <p class="text-sm text-gray-500">{{ $req->fromDishDay->scheduled_date }} -- {{ $req->toDishDay?->scheduled_date }}</p>
                        <span class="inline-block mt-2 px-4 py-1 text-xs rounded-full
                            {{ $req->status === 'accepted' ? 'bg-green-100 text-green-700' : ($req->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700')  }}">
                            {{ ucfirst($req->status) }}
                        </span>
                    </div>
                @endforeach
            @endif
        @else
            <h2 class="text-xl font-semibold mb-4">Histórico de Trocas</h2>
             @if ($history->isEmpty())
                <p class="text-gray-500">Ainda não tens trocas concluídas.</p>
            @else
                @foreach ($history as $trade)
                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 mb-4 shadow flex justify-between items-center">
                        <p>
                            <strong>{{ $trade->fromUser->nome }}</strong>
                            <span class="mx-2 text-gray-400">➡</span>
                            <strong>{{ $trade->toUser->nome }}</strong>
                        </p>
                        <p class="text-sm text-gray-500">
                            {{ $trade->fromDishDay->scheduled_date }} ➡ {{ $trade->toDishDay?->scheduled_date }}
                        </p>
                        <span class="inline-block mt-2 px-4 py-1 text-xs rounded-full font-medium 
                            {{ $trade->status === 'accepted' ? 'bg-green-100 text-green-700' : ($trade->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700')  }}">
                            {{ ucfirst($trade->status) }}
                        </span>
                    </div>
                @endforeach
            @endif
        @endif
    </div>
</div>
