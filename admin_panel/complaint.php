<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'include/database.php';

$stmt = $pdo->query("SELECT complaints.*, users.name AS user_name FROM complaints LEFT JOIN users ON complaints.user_id = users.id ORDER BY complaints.created_at DESC");
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление жалобами</title>
    <link rel="stylesheet" href="include/style.css">
</head>
<body>
<div class="container">
    <h1>Жалобы жителей</h1>

    <table class="complaints-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Пользователь</th>
            <th>Сообщение</th>
            <th>Статус</th>
            <th>Дата подачи</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody id="complaintsList">
        <?php foreach ($complaints as $complaint): ?>
            <tr id="complaint-<?= $complaint['id'] ?>">
                <td><?= $complaint['id'] ?></td>
                <td><?= htmlspecialchars($complaint['user_name']) ?></td>
                <td><?= nl2br(htmlspecialchars($complaint['message'])) ?></td>
                <td id="status-<?= $complaint['id'] ?>">
                    <?= htmlspecialchars($complaint['status']) ?>
                </td>
                <td><?= date('d.m.Y H:i', strtotime($complaint['created_at'])) ?></td>
                <td>
                    <?php if ($complaint['status'] !== 'done'): ?>
                        <button onclick="markDone(<?= $complaint['id'] ?>)">Готово</button>
                    <?php endif; ?>
                    <button onclick="deleteComplaint(<?= $complaint['id'] ?>)">Удалить</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <a href="main.php">← Вернуться в меню</a>
</div>

<script>
    function markDone(id){
        fetch('complaint_request.php?action=done&id=' + id)
            .then(response => response.text())
            .then(data => {
                document.getElementById('status-' + id).innerText = 'done';
                alert(data);
            })
            .catch(err => alert('Ошибка: ' + err));
    }

    function deleteComplaint(id){
        if(confirm('Удалить жалобу ID ' + id + '?')){
            fetch('complaint_request.php?action=delete&id=' + id)
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    document.getElementById('complaint-' + id).remove();
                })
                .catch(err => alert('Ошибка: ' + err));
        }
    }
</script>
</body>
</html>