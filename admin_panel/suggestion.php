<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'database.php';

$stmt = $pdo->query("SELECT suggestions.*, users.name AS user_name FROM suggestions LEFT JOIN users ON suggestions.user_id = users.id ORDER BY suggestions.created_at DESC");
$suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Предложения жителей</title>
    <link rel="stylesheet" href="include/style.css">
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
        </tr>
        </thead>
        <tbody>
        <?php foreach ($suggestions as $suggestion): ?>
            <tr>
                <td><?= $suggestion['id'] ?></td>
                <td><?= htmlspecialchars($suggestion['user_name']) ?></td>
                <td><?= nl2br(htmlspecialchars($suggestion['message'])) ?></td>
                <td><?= htmlspecialchars($suggestion['status']) ?></td>
                <td><?= date('d.m.Y H:i', strtotime($suggestion['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <a href="main.php">← Вернуться в меню</a>
</div>
</body>
</html>
