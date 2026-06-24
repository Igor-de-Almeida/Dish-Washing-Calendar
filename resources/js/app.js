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

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});

console.log('✅ Laravel Echo initialized');

// Torna as coisas disponíveis globalmente para o Livewire
window.FullCalendar = {
    Calendar: Calendar,
    dayGridPlugin: dayGridPlugin,
    interactionPlugin: interactionPlugin
};

console.log('✅ FullCalendar modules loaded and attached to window.FullCalendar');