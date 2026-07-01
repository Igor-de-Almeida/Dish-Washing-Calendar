<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <!-- Scripts -->
        <!-- start webpushr code -->
        <script>
        (function(w,d, s, id) {if(typeof(w.webpushr)!=='undefined') return;w.webpushr=w.webpushr||function(){(w.webpushr.q=w.webpushr.q||[]).push(arguments)};var js, fjs = d.getElementsByTagName(s)[0];js = d.createElement(s); js.id = id;js.async=1;js.src = "https://cdn.webpushr.com/app.min.js";fjs.parentNode.appendChild(js);}(window,document, 'script', 'webpushr-jssdk'));webpushr('setup',{'key':'BKlgvTt6LMRJiElIVtdGemVKFTYB2MpmqKliwvSk9E5cSvg1HvXB82JUWrU2Rhkf1AaaUXQKGpcaZ93Jcjmr5Qk' });

        async function requestNotificationPermission() {

            if (!('Notification' in window)) {
                return;
            }

            const permission = await Notification.requestPermission();

            if (permission === 'granted') {
                console.log('✅ Permissão concedida');

    
            } else {
                console.log('❌ Permissão negada');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            if ('Notification' in window &&
                Notification.permission === 'granted') {

                document.getElementById('enableNotificationsBtn').style.display = 'none';
            }
        });


        
        </script>
        <!-- end webpushr code -->
        @stack('scripts')
    </body>
</html>
