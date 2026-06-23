<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pusher Test</title>
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <script>
        Pusher.logToConsole = true;

        var pusher = new Pusher('c47ebe3976b796d6e122', { cluster: 'mt1' });

        var channel = pusher.subscribe('my-channel');
        channerl.bind('my-event', function(data) {
            alert(JSON.stringify(data));
        });
    </script>
</head>
<body>
    <h1>Pusher Test</h1>
    <p>Try publishing an event to channel <code>my-channel</code> with event name <code>my-event</code></p>
</body>
</html>