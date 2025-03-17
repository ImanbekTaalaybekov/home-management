<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'database.php';

$stmt = $pdo->query("SELECT notifications.*, residential_complexes.name AS complex_name, users.name AS user_name FROM notifications
    LEFT JOIN residential_complexes ON notifications.residential_complex_id = residential_complexes.id
    LEFT JOIN users ON notifications.user_id = users.id
    ORDER BY notifications.created_at DESC");
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Notifications Admin Panel</title>
    <link rel="stylesheet" href="include/style.css">
</head>
<body>
<div class="container">
    <h1>Управление уведомлениями</h1>

    <section>
        <h2>Создать уведомление</h2>
        <form id="notificationForm" enctype="multipart/form-data">
            <div>
                <label>Тип уведомления:</label>
                <input type="text" name="type" value="complex" required>
            </div>
            <div>
                <label>Заголовок:</label>
                <input type="text" name="title" placeholder="Например: Ремонт дорог" required>
            </div>
            <div>
                <label>Сообщение:</label>
                <textarea name="message" rows="3" placeholder="Будет перекрыт главный вход" required></textarea>
            </div>
            <div>
                <label>ID ЖК (если нужно):</label>
                <input type="text" name="residential_complex_id">
            </div>
            <div>
                <label>ID пользователя (если нужно адресно):</label>
                <input type="text" name="user_id">
            </div>
            <div>
                <label>Фотографии (необязательно, можно несколько):</label>
                <input type="file" name="photos[]" multiple>
            </div>
            <button type="submit">Создать уведомление</button>
        </form>
        <div id="notificationResult"></div>
    </section>

    <section>
        <h2>Существующие уведомления</h2>
        <table class="notification-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Тип</th>
                <th>Заголовок</th>
                <th>Сообщение</th>
                <th>ЖК</th>
                <th>Пользователь</th>
                <th>Дата создания</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody id="notificationList">
            <?php foreach ($notifications as $notification): ?>
                <tr id="notification-<?= $notification['id'] ?>">
                    <td><?= $notification['id'] ?></td>
                    <td><?= htmlspecialchars($notification['type']) ?></td>
                    <td><?= htmlspecialchars($notification['title']) ?></td>
                    <td><?= htmlspecialchars($notification['message']) ?></td>
                    <td><?= htmlspecialchars($notification['complex_name']) ?></td>
                    <td><?= htmlspecialchars($notification['user_name']) ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($notification['created_at'])) ?></td>
                    <td><button onclick="deleteNotification(<?= $notification['id'] ?>)">Удалить</button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <a href="main.php">← Вернуться в меню</a>
</div>

<script>
    document.getElementById('notificationForm').addEventListener('submit', function(e){
        e.preventDefault();
        let formData = new FormData(this);

        fetch('notification_request.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(data => {
                document.getElementById('notificationResult').innerHTML = data;
                setTimeout(() => location.reload(), 1000);
            })
            .catch(error => {
                document.getElementById('notificationResult').innerHTML = '<p style="color:red;">Ошибка: ' + error + '</p>';
            });
    });

    function deleteNotification(id){
        if(confirm('Удалить уведомление ID ' + id + '?')){
            fetch('notification_request.php?delete=' + id)
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    document.getElementById('notification-' + id).remove();
                })
                .catch(error => alert('Ошибка: ' + error));
        }
    }
</script>
</body>
</html>
