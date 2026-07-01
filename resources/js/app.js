console.log('Carregando o Ficheiro app.js');
import { Calendar } from '@fullcalendar/core';
console.log('Passou do import 1');
import dayGridPlugin from '@fullcalendar/daygrid';
console.log('Passou do import 2');
import interactionPlugin from '@fullcalendar/interaction';
console.log('Passou do import 3');
import Echo from 'laravel-echo';
console.log('Passou do import 4');
import Pusher from 'pusher-js';
console.log('Passou do import 5');


window.Pusher = Pusher;
console.log('Passou do Pusher');

try {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
        forceTLS: true
    });

    console.log('✅ Echo iniciado');
} catch (e) {
    console.error('❌ Echo falhou:', e);
}

console.log('✅ Laravel Echo initialized');

// Torna as coisas disponíveis globalmente para o Livewire
window.FullCalendar = {
    Calendar: Calendar,
    dayGridPlugin: dayGridPlugin,
    interactionPlugin: interactionPlugin
};
console.log('Passou do FullCalendar');

console.log('✅ FullCalendar modules loaded and attached to window.FullCalendar');

if ('serviceWorker' in navigator) {

    window.addEventListener('load', async () => {

        try {

            const registration = await navigator.serviceWorker.register('/service-worker.js');

            let subscription = await registration.pushManager.getSubscription();
            
            if (!subscription) {
                subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array('BI7jeMM3k-ylgmUmC7CWV-CoEFH6nIY3kGCSRk5B7H72ZYdZePsnXiUZgjSCwduAW128Wus_pemEhy_jeS4RVpM')
                });    
            }

            console.log('✅ Service Worker registado!', registration);


            

            const json = subscription.toJSON();

            console.log(subscription);
            console.log(subscription.toJSON());

            // Envia a subscription para o backend
            await fetch('/api/push/subscribe', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                 },
                body: JSON.stringify({
                    endpoint: json.endpoint,
                    public_key: json.keys.p256dh,
                    auth_token: json.keys.auth,
                    content_encoding: 'aes128gcm'
                })
            });

            alert('✅ Notificações ativadas com sucesso!');

        } catch (error) {

            console.error('❌ Erro ao registar Service Worker', error);

        }

    });

}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);

    return Uint8Array.from([...rawData].map(char => char.charCodeAt(0)));
}