<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'include/database.php';

$complexes = $pdo->query("SELECT id, name FROM residential_complexes ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$typeFilter = $_GET['filter_type'] ?? '';
$complexFilter = $_GET['filter_complex'] ?? '';

$where = [];
$params = [];

if ($typeFilter !== '') {
    $where[] = "notifications.type = :type";
    $params[':type'] = $typeFilter;
}

if ($complexFilter !== '') {
    $where[] = "notifications.residential_complex_id = :complex";
    $params[':complex'] = $complexFilter;
}

$whereSql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications $whereSql");
$countStmt->execute($params);
$totalNotifications = $countStmt->fetchColumn();

$perPage = 20;
$totalPages = ceil($totalNotifications / $perPage);
$currentPage = isset($_GET['page']) ? max(1, min($totalPages, (int)$_GET['page'])) : 1;
$offset = ($currentPage - 1) * $perPage;

$sql = "
    SELECT notifications.*, 
           residential_complexes.name AS complex_name, 
           users.name AS user_name, 
           (SELECT path FROM photos WHERE photoable_type = 'App\\Models\\Notification' AND photoable_id = notifications.id LIMIT 1) AS photo_path
    FROM notifications
    LEFT JOIN residential_complexes ON notifications.residential_complex_id = residential_complexes.id
    LEFT JOIN users ON notifications.user_id = users.id
    $whereSql
    ORDER BY notifications.created_at DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

function safeField($value){
    return $value ? htmlspecialchars($value) : '—';
}

function safeDate($date){
    return $date ? date('d.m.Y H:i', strtotime($date)) : '—';
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Wires Home</title>
    <link rel="stylesheet" href="include/style.css">
</head>
<body>
<div class="notification-container">
    <h1>Управление уведомлениями</h1>

    <section class="notification-section">
        <a href="main.php"><button>← Вернуться в меню</button></a>
        <h2><span id="formTitle">Создать уведомление</span></h2>

        <form id="notificationForm" enctype="multipart/form-data">
            <input type="hidden" name="id" id="notificationId">
            <div>
                <label>Тип уведомления:</label>
                <select name="type" id="notificationType" required>
                    <option value="complex">Для комплекса</option>
                    <option value="global">Общее</option>
                    <option value="personal">Личное</option>
                </select>
            </div>
            <div>
                <label>Категория:</label>
                <select name="category" id="notificationCategory" required>
                    <option value="technical">Техническая</option>
                    <option value="common">Общая</option>
                </select>
            </div>
            <div>
                <label>Заголовок:</label>
                <input type="text" name="title" id="notificationTitle" required>
            </div>
            <div>
                <label>Сообщение:</label>
                <textarea name="message" id="notificationMessage" rows="3" required></textarea>
            </div>
            <div>
                <label>ID ЖК (если нужно):</label>
                <input type="text" name="residential_complex_id" id="notificationComplexId">
            </div>
            <div>
                <label>ID пользователя (если нужно адресно):</label>
                <input type="text" name="user_id" id="notificationUserId">
            </div>
            <div>
                <label>Фотографии:</label>
                <input type="file" name="photos[]" multiple>
            </div>
            <div>
                <label>PDF-документ:</label>
                <input type="file" name="document" accept="application/pdf">
            </div>
            <button type="submit">Сохранить</button>
            <button type="button" id="cancelEdit" style="display:none;">Отмена</button>
        </form>
        <div id="notificationResult"></div>
    </section>

    <section class="notification-section">
        <h2>Существующие уведомления</h2>

        <table class="notification-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Тип</th>
                <th>Категория</th>
                <th>Заголовок</th>
                <th>Сообщение</th>
                <th>ЖК</th>
                <th>Пользователь</th>
                <th>Дата</th>
                <th>Фото</th>
                <th>Документ</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody id="notificationList">
            <?php foreach ($notifications as $notification): ?>
                <tr id="notification-<?= $notification['id'] ?>">
                    <td><?= $notification['id'] ?></td>
                    <td><?= safeField($notification['type']) ?></td>
                    <td><?= safeField($notification['category']) ?></td>
                    <td><?= safeField($notification['title']) ?></td>
                    <td><?= safeField($notification['message']) ?></td>
                    <td><?= safeField($notification['complex_name']) ?></td>
                    <td><?= safeField($notification['user_name']) ?></td>
                    <td><?= safeDate($notification['created_at']) ?></td>
                    <td>
                        <?php if ($notification['photo_path']): ?>
                            <img src="<?= 'https://home-folder.wires.kz/storage/' . $notification['photo_path'] ?>" class="preview-img" alt="Фото" onclick="openModal(this)">
                        <?php else: ?>Нет<?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($notification['document'])): ?>
                            <a href="<?= 'https://home-folder.wires.kz/storage/' . $notification['document'] ?>" target="_blank">Скачать</a>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td>
                        <button onclick="editNotification(
                        <?= $notification['id'] ?>,
                                '<?= $notification['type'] ?>',
                                '<?= htmlspecialchars($notification['title']) ?>',
                                '<?= htmlspecialchars($notification['message']) ?>',
                                '<?= $notification['residential_complex_id'] ?: '' ?>',
                                '<?= $notification['user_id'] ?: '' ?>',
                                '<?= $notification['category'] ?>'
                                )">Изменить</button>
                        <button onclick="deleteNotification(<?= $notification['id'] ?>)">Удалить</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>

<script>
    function editNotification(id, type, title, message, complexId, userId, category) {
        document.getElementById('notificationId').value = id;
        document.getElementById('notificationType').value = type;
        document.getElementById('notificationTitle').value = title;
        document.getElementById('notificationMessage').value = message;
        document.getElementById('notificationComplexId').value = complexId;
        document.getElementById('notificationUserId').value = userId;
        document.getElementById('notificationCategory').value = category;
        document.getElementById('cancelEdit').style.display = 'inline-block';
    }

    document.getElementById('cancelEdit').addEventListener('click', function(){
        document.getElementById('notificationForm').reset();
        document.getElementById('notificationId').value = '';
        this.style.display = 'none';
    });

    document.getElementById('notificationForm').addEventListener('submit', function(e){
        e.preventDefault();
        let formData = new FormData(this);
        let id = document.getElementById('notificationId').value;
        let url = id ? `notification_request.php?update=${id}` : `notification_request.php`;

        fetch(url, {
            method: 'POST',
            body: formData
        }).then(r => r.text()).then(html => {
            document.getElementById('notificationResult').innerHTML = html;
            setTimeout(() => location.reload(), 1000);
        });
    });

    function deleteNotification(id){
        if(confirm("Удалить уведомление?")){
            fetch(`notification_request.php?delete=${id}`)
                .then(r => r.text())
                .then(alert)
                .then(() => location.reload());
        }
    }

    function openModal(img) {
        const modal = document.getElementById("imageModal");
        const modalImg = document.getElementById("modalImage");
        modal.style.display = "flex";
        modalImg.src = img.src;
    }
</script>
</body>
</html>
