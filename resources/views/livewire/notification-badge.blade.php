<a href="{{ route('swap-requests') }}" wire:poll.5s="loadNotifications" class="text-gray-700 dark:text-gray-300 dark:hover:text-gray-900 font-medium">🔄 Pedidos de Troca

    @if ($count > 0)
        <span class="absolute -top-1 -rigth-1 bg-red-500 text-white text-xs font-bold min-w-[20px] w-5 h-5 flex items-center justify-center rounded-full shadow" style="top: 0.40rem">{{ $count > 9 ? '9+' : $count }}</span>

    @endif
</a>

@push('scripts')
<script>
    // Notificações em tempo real
    console.log("TESTANDO NOTIFICAÇÕES EM TEMPO REAL");
    window.Echo.private('user.{{ auth()->id() }}')
        .listen('.swap-request-created', (e) => {
            console.log('📬 Novo pedido de troca recebido!', e);
            
            // Atualiza o badge de notificações
            Livewire.dispatch('refresh-badge');
            
            // Opcional: atualiza o calendário se estiver aberto
            if (typeof window.refreshCalendar === 'function') {
                window.refreshCalendar();
            }
        });
</script>
@endpush
