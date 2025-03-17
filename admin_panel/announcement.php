<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'include/database.php';

$stmt = $pdo->query("SELECT announcements.*, residential_complexes.name AS complex_name FROM announcements LEFT JOIN residential_complexes ON announcements.residential_complex_id = residential_complexes.id ORDER BY announcements.created_at DESC");
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление объявлениями</title>
    <link rel="stylesheet" href="include/style.css">
</head>
<body>
<div class="container">
    <h1>Объявления</h1>

    <table class="announcements-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Заголовок</th>
            <th>Содержание</th>
            <th>Жилой комплекс</th>
            <th>Дата создания</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody id="announcementsList">
        <?php foreach ($announcements as $announcement): ?>
            <tr id="announcement-<?= $announcement['id'] ?>">
                <td><?= $announcement['id'] ?></td>
                <td><?= htmlspecialchars($announcement['title']) ?></td>
                <td><?= nl2br(htmlspecialchars($announcement['content'])) ?></td>
                <td><?= htmlspecialchars($announcement['complex_name'] ?: '-') ?></td>
                <td><?= date('d.m.Y H:i', strtotime($announcement['created_at'])) ?></td>
                <td>
                    <button onclick="deleteAnnouncement(<?= $announcement['id'] ?>)">Удалить</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <a href="main.php">← Вернуться в меню</a>
</div>

<script>
    function deleteAnnouncement(id){
        if(confirm('Удалить объявление ID ' + id + '?')){
            fetch('announcement_request.php?delete=' + id)
                .then(res => res.text())
                .then(response => {
                    alert(response);
                    document.getElementById('announcement-' + id).remove();
                })
                .catch(err => alert('Ошибка: ' + err));
        }
    }
</script>
</body>
</html>
