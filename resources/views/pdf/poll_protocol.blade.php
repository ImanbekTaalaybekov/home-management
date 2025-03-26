<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Протокол голосования</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
    </style>
</head>
<body>
<h1>Протокол голосования</h1>
<p>Опрос: {{ $poll->title }}</p>
<p>Всего голосов: {{ $totalVotes }}</p>
<p>Да: {{ $yesCount }}</p>
<p>Нет: {{ $noCount }}</p>
<p>Воздержались: {{ $abstainCount }}</p>
</body>
</html>
