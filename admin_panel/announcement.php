<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'include/database.php';

$complexes = $pdo->query("SELECT id, name FROM residential_complexes ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("
    SELECT announcements.*, residential_complexes.name AS complex_name, 
           (SELECT path FROM photos WHERE photoable_type = 'App\\Models\\Announcement' 
            AND photoable_id = announcements.id LIMIT 1) AS photo_path
    FROM announcements 
    LEFT JOIN residential_complexes ON announcements.residential_complex_id = residential_complexes.id 
    ORDER BY announcements.created_at DESC
");
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

function safeField($value)
{
    return $value ? htmlspecialchars($value) : '—';
}

function safeDate($date)
{
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

<div id="imageModal" class="modal-overlay">
    <span class="close-modal">&times;</span>
    <div class="modal-content">
        <img id="modalImage" src="" alt="Увеличенное изображение">
    </div>
</div>

<div class="announcement-container">
    <h1>Объявления</h1>
    <a href="main.php">
        <button>← Вернуться в меню</button>
    </a>

    <div style="margin: 20px 0;">
        <label for="complexFilter">Фильтр по жилому комплексу:</label>
        <select id="complexFilter">
            <option value="">Все ЖК</option>
            <?php foreach ($complexes as $complex): ?>
                <option value="<?= $complex['id'] ?>"><?= safeField($complex['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

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
        <tbody id="announcementsList">
        <?php foreach ($announcements as $announcement): ?>
            <tr id="announcement-<?= $announcement['id'] ?>">
                <td><?= $announcement['id'] ?></td>
                <td><?= safeField($announcement['title']) ?></td>
                <td><?= nl2br(safeField($announcement['content'])) ?></td>
                <td><?= safeField($announcement['complex_name']) ?></td>
                <td><?= safeDate($announcement['created_at']) ?></td>
                <td>
                    <?php if ($announcement['photo_path']): ?>
                        <img src="<?= htmlspecialchars('https://home-folder.wires.kz/storage/' . $announcement['photo_path']) ?>"
                             class="preview-img" alt="Фото" onclick="openModal(this)">
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
</div>

<script>
    function deleteAnnouncement(id) {
        if (confirm('Удалить объявление ID ' + id + '?')) {
            fetch('announcement_request.php?delete=' + id)
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    document.getElementById('announcement-' + id).remove();
                })
                .catch(err => alert('Ошибка: ' + err));
        }
    }

    function openModal(imgElement) {
        const modal = document.getElementById("imageModal");
        const modalImg = document.getElementById("modalImage");
        modal.style.display = "flex";
        modalImg.src = imgElement.src;
    }

    document.querySelector(".close-modal").addEventListener("click", function () {
        document.getElementById("imageModal").style.display = "none";
    });

    document.getElementById("imageModal").addEventListener("click", function (event) {
        if (event.target === this) {
            this.style.display = "none";
        }
    });

    document.getElementById('complexFilter').addEventListener('change', function () {
        let complexId = this.value;
        let url = 'announcement_request.php?filter=1';
        if (complexId) {
            url += '&complex_id=' + complexId;
        }
        fetch(url)
            .then(response => response.text())
            .then(html => {
                document.getElementById('announcementsList').innerHTML = html;
            })
            .catch(err => console.error('Ошибка фильтрации:', err));
    });
</script>

</body>
</html>
