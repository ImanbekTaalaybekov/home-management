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
    <title>Предложения жителей</title>
    <link rel="stylesheet" href="include/style.css">
</head>
<body>
<div id="imageModal" class="modal-overlay">
    <span class="close-modal">&times;</span>
    <div class="modal-content">
        <img id="modalImage" src="" alt="Увеличенное изображение">
    </div>
</div>
<div class="suggestion-container">
    <div class="suggestion-block">
    <h1 class="suggestion-h1">Предложения жителей</h1>
    <br>
    <a href="main.php">
        <button>← Вернуться в меню</button>
    </a>
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
                        <img src="<?= htmlspecialchars('https://212.112.105.242:443/storage/' . $suggestion['photo_path']) ?>"
                             class="preview-img"
                             alt="Фото"
                             onclick="openModal(this)">
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
</div>
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
