self.addEventListener('install', (event) => {
    console.log('✅ Service Worker instalado');
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    console.log('✅ Service Worker ativado');
});

self.addEventListener('push', (event) => {

    const data = event.data
        ? event.data.json()
        : {
            title: 'DishCalendar',
            body: 'Nova notificação',
        };

    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: '/favicon.ico',
            badge: '/favicon.ico',
            data: {
                url: data.url || '/calendar'
            }
        })
    );

    self.addEventListener('notificationclick', function(event) {
        event.notification.close();
        event.waitUntil(
            clients.openWindow(event.notification.data.url)
        );
    });
});