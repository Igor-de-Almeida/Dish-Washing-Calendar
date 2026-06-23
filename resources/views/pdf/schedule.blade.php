<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Escala - {{ $month }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <h1>Escala de Loiça - {{ $month }}</h1>
    <table>
        <tr>
            <th>Data</th>
            <th>Turno</th>
            <th>Responsável</th>
            <th>Estado</th>
        </tr>
        @foreach ($schedules as $item)
            <tr>
                <td>{{ $item->scheduled_date }}</td>
                <td>{{ $item->shift === 'tarde' ? 'Tarde' : ($item->shift === 'noite' ? 'Noite' : 'Dia inteiro') }}</td>
                <td>{{ $item->usuario->nome ?? 'Desconhecido' }}</td>
                td>{{ ucfirst($item->status) }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>