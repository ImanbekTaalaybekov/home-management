<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Протокол голосования</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        h1, h2, h3, p {
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        .signature-table td {
            border: none;
            padding: 12px 0;
        }
    </style>
</head>
<body>

<h2>Лист регистрации собственников квартир, нежилого помещения<br>
    многоквартирного жилого дома, участвующих на собрании</h2>

<p>«________» _________________________ 20___ года</p>
<p><strong>Местонахождение многоквартирного жилого дома:</strong> {{ $residentialComplex->address }}</p>

<table>
    <thead>
    <tr>
        <th>№</th>
        <th>Фамилия Имя Отчество</th>
        <th>№ квартиры</th>
        <th>Нежилое помещение</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($votes as $index => $vote)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $vote->user->name ?? '—' }}</td>
            <td>{{ $vote->user->apartment_number ?? '—' }}</td>
            <td>{{ $vote->user->non_residential_premises ?? '—' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<table class="signature-table">
    <tr>
        <td>Председатель собрания: ___________________________ (Ф.И.О.) ___________________</td>
    </tr>
    <tr>
        <td>Секретарь собрания: _____________________________ (Ф.И.О.) ___________________</td>
    </tr>
    <tr>
        <td>Член совета дома: _______________________________ (Ф.И.О.) ___________________</td>
    </tr>
    <tr>
        <td>Член совета дома: _______________________________ (Ф.И.О.) ___________________</td>
    </tr>
    <tr>
        <td>Член совета дома: _______________________________ (Ф.И.О.) ___________________</td>
    </tr>
</table>

<hr>

<h2>Лист голосования собственников квартир, нежилых помещений<br>
    проголосовавших на собрании (проводимый путем явочного порядка)</h2>

<p>«________» ____________________ 20___ года, время ____________</p>
<p><strong>Местонахождение многоквартирного жилого дома:</strong> {{ $residentialComplex->address }}</p>

<h4>Вопросы, внесенные для обсуждения:</h4>
<p>{{ $poll->description }}</p>

<table>
    <thead>
    <tr>
        <th>№</th>
        <th>Фамилия Имя Отчество</th>
        <th>№ квартиры</th>
        <th>Нежилое помещение</th>
        <th>«За» (подпись)</th>
        <th>«Против» (подпись)</th>
        <th>Воздержусь (подпись)</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($votes as $index => $vote)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $vote->user->name ?? '—' }}</td>
            <td>{{ $vote->user->apartment_number ?? '—' }}</td>
            <td>{{ $vote->user->non_residential_premises ?? '—' }}</td>
            <td>{{ $vote->vote === 'yes' ? '✔' : '' }}</td>
            <td>{{ $vote->vote === 'no' ? '✔' : '' }}</td>
            <td>{{ $vote->vote === 'abstain' ? '✔' : '' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<table class="signature-table">
    <tr>
        <td>Председатель собрания: ___________________________ (Ф.И.О.) ___________________</td>
    </tr>
    <tr>
        <td>Секретарь собрания: _____________________________ (Ф.И.О.) ___________________</td>
    </tr>
    <tr>
        <td>Член совета дома: _______________________________ (Ф.И.О.) ___________________</td>
    </tr>
    <tr>
        <td>Член совета дома: _______________________________ (Ф.И.О.) ___________________</td>
    </tr>
    <tr>
        <td>Член совета дома: _______________________________ (Ф.И.О.) ___________________</td>
    </tr>
</table>

</body>
</html>
