<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'include/database.php';

$stmt = $pdo->query("
    SELECT complaints.*, users.name AS user_name, 
           (SELECT path FROM photos WHERE photoable_type = 'App\\Models\\Complaint' AND photoable_id = complaints.id LIMIT 1) AS photo_path
    FROM complaints
    LEFT JOIN users ON complaints.user_id = users.id
    ORDER BY complaints.created_at DESC
");
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Управление жалобами</title>
    <link rel="stylesheet" href="include/style.css">
</head>
<body>
<div id="imageModal" class="modal-overlay">
    <span class="close-modal">&times;</span>
    <div class="modal-content">
        <img id="modalImage" src="" alt="Увеличенное изображение">
    </div>
</div>
<div class="complaint-container">
    <div class="complaint-block">
        <h1>Жалобы жителей</h1>
        <a href="main.php">
            <button>← Вернуться в меню</button>
        </a>
    </div>
    <div class="complaint-block">
        <table class="complaints-table">
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
            <tbody id="complaintsList">
            <?php foreach ($complaints as $complaint): ?>
                <tr id="complaint-<?= $complaint['id'] ?>">
                    <td><?= $complaint['id'] ?></td>
                    <td><?= safeField($complaint['user_name']) ?></td>
                    <td><?= nl2br(safeField($complaint['message'])) ?></td>
                    <td id="status-<?= $complaint['id'] ?>">
                        <?= safeField($complaint['status']) ?>
                    </td>
                    <td><?= safeDate($complaint['created_at']) ?></td>
                    <td>
                        <?php if ($complaint['photo_path']): ?>
                            <img src="<?= htmlspecialchars('https://212.112.105.242:443/storage/' . $complaint['photo_path']) ?>"
                                 class="preview-img"
                                 alt="Фото"
                                 onclick="openModal(this)">
                        <?php else: ?>
                            Нет
                        <?php endif; ?>
                    </td>
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
    </div>
</div>

<script>
    function markDone(id) {
        fetch('complaint_request.php?action=done&id=' + id)
            .then(response => response.text())
            .then(data => {
                document.getElementById('status-' + id).innerText = 'done';
                alert(data);
            })
            .catch(err => alert('Ошибка: ' + err));
    }

    function deleteComplaint(id) {
        if (confirm('Удалить жалобу ID ' + id + '?')) {
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
<script>
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
</script>
</body>
</html>
