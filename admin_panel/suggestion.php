<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'include/database.php';

$stmt = $pdo->query("
    SELECT suggestions.*, users.name AS user_name,
           (SELECT path FROM photos WHERE photoable_type = 'App\\Models\\Suggestion' 
            AND photoable_id = suggestions.id LIMIT 1) AS photo_path
    FROM suggestions 
    LEFT JOIN users ON suggestions.user_id = users.id 
    ORDER BY suggestions.created_at DESC");
$suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

function safeField($value) {
    return $value ? htmlspecialchars($value) : '—';
}

function safeDate($date) {
    return $date ? date('d.m.Y H:i', strtotime($date)) : '—';
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Предложения жителей</title>
    <link rel="stylesheet" href="include/style.css">
    <style>
        .preview-img {
            max-width: 50px;
            height: auto;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Предложения жителей</h1>

    <table class="suggestions-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Пользователь</th>
            <th>Сообщение</th>
            <th>Статус</th>
            <th>Дата подачи</th>
            <th>Фото</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($suggestions as $suggestion): ?>
            <tr id="suggestion-<?= $suggestion['id'] ?>">
                <td><?= $suggestion['id'] ?></td>
                <td><?= safeField($suggestion['user_name']) ?></td>
                <td><?= nl2br(safeField($suggestion['message'])) ?></td>
                <td id="status-<?= $suggestion['id'] ?>">
                    <?= safeField($suggestion['status']) ?>
                </td>
                <td><?= safeDate($suggestion['created_at']) ?></td>
                <td>
                    <?php if ($suggestion['photo_path']): ?>
                        <img src="<?= htmlspecialchars($suggestion['photo_path']) ?>" class="preview-img" alt="Фото">
                    <?php else: ?>
                        Нет
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($suggestion['status'] !== 'done'): ?>
                        <button onclick="markDone(<?= $suggestion['id'] ?>)">Готово</button>
                    <?php endif; ?>
                    <button onclick="deleteSuggestion(<?= $suggestion['id'] ?>)">Удалить</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <a href="main.php">← Вернуться в меню</a>
</div>

<script>
    function markDone(id) {
        fetch('suggestion_request.php?action=done&id=' + id)
            .then(response => response.text())
            .then(data => {
                document.getElementById('status-' + id).innerText = 'done';
                alert(data);
            })
            .catch(err => alert('Ошибка: ' + err));
    }

    function deleteSuggestion(id) {
        if (confirm('Удалить предложение ID ' + id + '?')) {
            fetch('suggestion_request.php?action=delete&id=' + id)
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    document.getElementById('suggestion-' + id).remove();
                })
                .catch(err => alert('Ошибка: ' + err));
        }
    }
</script>
</body>
</html>
