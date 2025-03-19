<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'include/database.php';

$stmt = $pdo->query("
    SELECT announcements.*, residential_complexes.name AS complex_name, 
           (SELECT path FROM photos WHERE photoable_type = 'App\\Models\\Announcement' 
            AND photoable_id = announcements.id LIMIT 1) AS photo_path
    FROM announcements 
    LEFT JOIN residential_complexes ON announcements.residential_complex_id = residential_complexes.id 
    ORDER BY announcements.created_at DESC");
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Управление объявлениями</title>
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
    <h1>Объявления</h1>

    <table class="announcements-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Заголовок</th>
            <th>Содержание</th>
            <th>Жилой комплекс</th>
            <th>Дата создания</th>
            <th>Фото</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($announcements as $announcement): ?>
            <tr id="announcement-<?= $announcement['id'] ?>">
                <td><?= $announcement['id'] ?></td>
                <td><?= safeField($announcement['title']) ?></td>
                <td><?= nl2br(safeField($announcement['content'])) ?></td>
                <td><?= safeField($announcement['complex_name']) ?></td>
                <td><?= safeDate($announcement['created_at']) ?></td>
                <td>
                    <?php if ($announcement['photo_path']): ?>
                        <img src="<?= htmlspecialchars('http://212.112.105.242:8800/storage/' . $announcement['photo_path']) ?>" class="preview-img" alt="Фото">
                    <?php else: ?>
                        Нет
                    <?php endif; ?>
                </td>
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
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    document.getElementById('announcement-' + id).remove();
                })
                .catch(err => alert('Ошибка: ' + err));
        }
    }
</script>
</body>
</html>
