<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Протокол голосования</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        h1, h2 {
            text-align: center;
        }
        .table-header {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<h1>Протокол голосования</h1>
<h2>Собрание собственников квартир и нежилых помещений</h2>
<p><strong>Дата начала голосования:</strong> {{ \Carbon\Carbon::parse($poll->start_date)->format('d.m.Y') }}</p>
<p><strong>Дата окончания голосования:</strong> {{ \Carbon\Carbon::parse($poll->end_date)->format('d.m.Y') }}</p>
<p><strong>Жилой комплекс:</strong> {{ $residentialComplex->name }}</p>
<p><strong>Адрес:</strong> {{ $residentialComplex->address }}</p>

<h3>Общее количество голосов: {{ $totalVotes }}</h3>
<h3>Голоса "За": {{ $yesCount }} ({{ $yesVoters->count() }} голосов)</h3>
<h3>Голоса "Против": {{ $noCount }} ({{ $noVoters->count() }} голосов)</h3>
<h3>Голоса "Воздержался": {{ $abstainCount }} ({{ $abstainVoters->count() }} голосов)</h3>

<h3>Список проголосовавших:</h3>

<table>
    <thead>
    <tr class="table-header">
        <th>Фамилия, имя, отчество</th>
        <th>Голос</th>
    </tr>
    </thead>
    <tbody>
    <tr><td colspan="2"><strong>Голоса "За":</strong></td></tr>
    @foreach ($yesVoters as $voter)
        <tr>
            <td>{{ $voter }}</td>
            <td>За</td>
        </tr>
    @endforeach

    <tr><td colspan="2"><strong>Голоса "Против":</strong></td></tr>
    @foreach ($noVoters as $voter)
        <tr>
            <td>{{ $voter }}</td>
            <td>Против</td>
        </tr>
    @endforeach

    <tr><td colspan="2"><strong>Голоса "Воздержался":</strong></td></tr>
    @foreach ($abstainVoters as $voter)
        <tr>
            <td>{{ $voter }}</td>
            <td>Воздержался</td>
        </tr>
    @endforeach
    </tbody>
</table>

<p><strong>Председатель собрания:</strong> ___________________________</p>
<p><strong>Секретарь собрания:</strong> ______________________________</p>
<p><strong>Члены совета дома:</strong> ______________________________</p>
</body>
</html>
