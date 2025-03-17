<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'include/database.php';

$stmt = $pdo->query("SELECT polls.*, residential_complexes.name AS complex_name FROM polls LEFT JOIN residential_complexes ON polls.residential_complex_id = residential_complexes.id ORDER BY polls.created_at DESC");
$polls = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление голосованиями</title>
    <link rel="stylesheet" href="include/style.css">
</head>
<body>
<div class="container">
    <h1>Голосования</h1>

    <table class="polls-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Заголовок</th>
            <th>Описание</th>
            <th>Жилой комплекс</th>
            <th>Дата начала</th>
            <th>Дата окончания</th>
            <th>Создано</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody id="pollsList">
        <?php foreach ($polls as $poll): ?>
            <tr id="poll-<?= $poll['id'] ?>">
                <td><?= $poll['id'] ?></td>
                <td><?= htmlspecialchars($poll['title']) ?></td>
                <td><?= nl2br(htmlspecialchars($poll['description'])) ?></td>
                <td><?= htmlspecialchars($poll['complex_name'] ?: '-') ?></td>
                <td><?= date('d.m.Y', strtotime($poll['start_date'])) ?></td>
                <td><?= date('d.m.Y', strtotime($poll['end_date'])) ?></td>
                <td><?= date('d.m.Y H:i', strtotime($poll['created_at'])) ?></td>
                <td>
                    <button onclick="deletePoll(<?= $poll['id'] ?>)">Удалить</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <a href="main.php">← Вернуться в меню</a>
</div>

<script>
    function deletePoll(id){
        if(confirm('Удалить голосование ID ' + id + '?')){
            fetch('poll_request.php?delete=' + id)
                .then(res => res.text())
                .then(response => {
                    alert(response);
                    document.getElementById('poll-' + id).remove();
                })
                .catch(err => alert('Ошибка: ' + err));
        }
    }
</script>
</body>
</html>