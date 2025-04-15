<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'include/database.php';

function safeField($value)
{
    return $value ? htmlspecialchars($value) : '—';
}

function safeDate($date)
{
    return $date ? date('d.m.Y H:i', strtotime($date)) : '—';
}

function humanStatus($status)
{
    return $status === 'done' ? 'Готово' : 'В обработке';
}

$stmt = $pdo->query("
    SELECT complaints.*, users.name AS user_name, 
           (SELECT path FROM photos WHERE photoable_type = 'App\\Models\\Complaint' AND photoable_id = complaints.id LIMIT 1) AS photo_path
    FROM complaints
    LEFT JOIN users ON complaints.user_id = users.id
    ORDER BY complaints.created_at DESC
");
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

        <div style="margin-top: 10px; margin-bottom: 20px;">
            <label for="statusFilter">Фильтр по статусу:</label>
            <select id="statusFilter">
                <option value="">Все</option>
                <option value="pending">В обработке</option>
                <option value="done">Готово</option>
            </select>
        </div>
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
                        <?= humanStatus($complaint['status']) ?>
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
                document.getElementById('status-' + id).innerText = 'Готово';
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

    document.getElementById('statusFilter').addEventListener('change', function () {
        const status = this.value;
        let url = 'complaint_request.php?filter=1';
        if (status) {
            url += '&status=' + status;
        }

        fetch(url)
            .then(response => response.text())
            .then(html => {
                document.getElementById('complaintsList').innerHTML = html;
            });
    });
</script>

</body>
</html>
