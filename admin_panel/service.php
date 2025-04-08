<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'include/database.php';

$stmt = $pdo->query("
    SELECT service_requests.*, users.name AS user_name, 
           categories.name_rus AS type_rus,
           (SELECT path FROM photos WHERE photoable_type = 'App\\Models\\ServiceRequest' 
            AND photoable_id = service_requests.id LIMIT 1) AS photo_path
    FROM service_requests 
    LEFT JOIN users ON service_requests.user_id = users.id 
    LEFT JOIN service_request_categories AS categories ON service_requests.type = categories.name
    ORDER BY service_requests.created_at DESC
");

$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Управление заявками на вызов мастера</title>
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
<div class="service-container">
    <h1>Заявки на вызов мастера</h1>
    <h1>Жалобы жителей</h1>
    <a href="main.php">
        <button>← Вернуться в меню</button>
    </a>
    <table class="service-requests-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Пользователь</th>
            <th>Тип мастера</th>
            <th>Описание проблемы</th>
            <th>Статус</th>
            <th>Дата заявки</th>
            <th>Фото</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($requests as $request): ?>
            <tr id="request-<?= $request['id'] ?>">
                <td><?= $request['id'] ?></td>
                <td><?= safeField($request['user_name']) ?></td>
                <td><?= safeField($request['type_rus']) ?></td>
                <td><?= nl2br(safeField($request['description'])) ?></td>
                <td id="status-<?= $request['id'] ?>"><?= safeField($request['status']) ?></td>
                <td><?= safeDate($request['created_at']) ?></td>
                <td>
                    <?php if ($request['photo_path']): ?>
                        <img src="<?= htmlspecialchars('https://212.112.105.242:443/storage/' . $request['photo_path']) ?>"
                             class="preview-img"
                             alt="Фото"
                             onclick="openModal(this)">
                    <?php else: ?>
                        Нет
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($request['status'] !== 'done'): ?>
                        <button onclick="markDone(<?= $request['id'] ?>)">Готово</button>
                    <?php endif; ?>
                    <button onclick="deleteRequest(<?= $request['id'] ?>)">Удалить</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function markDone(id) {
        fetch('service_request.php?action=done&id=' + id)
            .then(response => response.text())
            .then(data => {
                document.getElementById('status-' + id).innerText = 'done';
                alert(data);
            })
            .catch(err => alert('Ошибка: ' + err));
    }

    function deleteRequest(id) {
        if (confirm('Удалить заявку ID ' + id + '?')) {
            fetch('service_request.php?action=delete&id=' + id)
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    document.getElementById('request-' + id).remove();
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

    document.querySelector(".close-modal").addEventListener("click", function() {
        document.getElementById("imageModal").style.display = "none";
    });

    document.getElementById("imageModal").addEventListener("click", function(event) {
        if (event.target === this) {
            this.style.display = "none";
        }
    });
</script>
</body>
</html>
