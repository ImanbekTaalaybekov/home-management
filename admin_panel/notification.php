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
$totalNotifications = (int)$countStmt->fetchColumn();

$perPage = 20;
$totalPages = max(1, (int)ceil($totalNotifications / $perPage));
$currentPage = isset($_GET['page']) ? max(1, min($totalPages, (int)$_GET['page'])) : 1;
$offset = ($currentPage - 1) * $perPage;

$sql = "
    SELECT n.*,
           rc.name AS complex_name,
           u.name AS user_name,
           u.personal_account AS user_personal_account,
           (SELECT path FROM photos WHERE photoable_type = 'App\\Models\\Notification' AND photoable_id = n.id LIMIT 1) AS photo_path
    FROM notifications n
    LEFT JOIN residential_complexes rc ON n.residential_complex_id = rc.id
    LEFT JOIN users u ON n.user_id = u.id
    $whereSql
    ORDER BY n.created_at DESC
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

function safeField($value){ return $value !== null && $value !== '' ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : '—'; }
function safeDate($date){ return $date ? date('d.m.Y H:i', strtotime($date)) : '—'; }
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

            <div id="complexWrapper">
                <label>Жилой комплекс:</label>
                <select name="residential_complex_id" id="notificationComplexId">
                    <option value="">— Выберите ЖК —</option>
                    <?php foreach ($complexes as $c): ?>
                        <option value="<?= htmlspecialchars($c['id'], ENT_QUOTES) ?>">
                            <?= htmlspecialchars($c['name'], ENT_QUOTES) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="personalWrapper" style="display:none;">
                <label>Лицевой счёт пользователя:</label>
                <input type="text" name="personal_account" id="notificationPersonalAccount" placeholder="например, 123456">
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
                <th>Лицевой счёт</th>
                <th>Дата</th>
                <th>Фото</th>
                <th>Документ</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody id="notificationList">
            <?php foreach ($notifications as $n): ?>
                <tr id="notification-<?= (int)$n['id'] ?>">
                    <td><?= (int)$n['id'] ?></td>
                    <td><?= safeField($n['type']) ?></td>
                    <td><?= safeField($n['category']) ?></td>
                    <td><?= safeField($n['title']) ?></td>
                    <td><?= safeField($n['message']) ?></td>
                    <td><?= safeField($n['complex_name']) ?></td>
                    <td><?= safeField($n['user_name']) ?></td>
                    <td><?= safeField($n['user_personal_account']) ?></td>
                    <td><?= safeDate($n['created_at']) ?></td>
                    <td>
                        <?php if (!empty($n['photo_path'])): ?>
                            <img src="<?= 'https://home-folder.wires.kz/storage/' . htmlspecialchars($n['photo_path'], ENT_QUOTES) ?>" class="preview-img" alt="Фото" onclick="openModal(this)">
                        <?php else: ?>Нет<?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($n['document'])): ?>
                            <a href="<?= 'https://home-folder.wires.kz/storage/' . htmlspecialchars($n['document'], ENT_QUOTES) ?>" target="_blank">Скачать</a>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td>
                        <button onclick="editNotification(
                        <?= (int)$n['id'] ?>,
                                '<?= htmlspecialchars($n['type'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($n['title'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($n['message'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($n['residential_complex_id'] ?? '', ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($n['user_personal_account'] ?? '', ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($n['category'], ENT_QUOTES) ?>'
                                )">Изменить</button>
                        <button onclick="deleteNotification(<?= (int)$n['id'] ?>)">Удалить</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>

<script>
    function toggleTargetFields() {
        const type = document.getElementById('notificationType').value;
        const complexWrapper = document.getElementById('complexWrapper');
        const personalWrapper = document.getElementById('personalWrapper');
        const complexSelect = document.getElementById('notificationComplexId');
        const personalInput = document.getElementById('notificationPersonalAccount');

        if (type === 'complex') {
            complexWrapper.style.display = '';
            personalWrapper.style.display = 'none';
            complexSelect.required = true;
            personalInput.required = false;
            personalInput.value = '';
        } else if (type === 'personal') {
            complexWrapper.style.display = 'none';
            personalWrapper.style.display = '';
            complexSelect.required = false;
            personalInput.required = true;
            complexSelect.value = '';
        } else {
            complexWrapper.style.display = 'none';
            personalWrapper.style.display = 'none';
            complexSelect.required = false;
            personalInput.required = false;
            complexSelect.value = '';
            personalInput.value = '';
        }
    }

    function editNotification(id, type, title, message, complexId, personalAccount, category) {
        document.getElementById('notificationId').value = id;
        document.getElementById('notificationType').value = type;
        document.getElementById('notificationTitle').value = title;
        document.getElementById('notificationMessage').value = message;
        document.getElementById('notificationCategory').value = category;

        toggleTargetFields();

        if (type === 'complex') {
            document.getElementById('notificationComplexId').value = complexId || '';
            document.getElementById('notificationPersonalAccount').value = '';
        } else if (type === 'personal') {
            document.getElementById('notificationPersonalAccount').value = personalAccount || '';
            document.getElementById('notificationComplexId').value = '';
        } else {
            document.getElementById('notificationComplexId').value = '';
            document.getElementById('notificationPersonalAccount').value = '';
        }

        document.getElementById('cancelEdit').style.display = 'inline-block';
    }

    document.getElementById('notificationType').addEventListener('change', toggleTargetFields);
    toggleTargetFields();

    document.getElementById('cancelEdit').addEventListener('click', function(){
        document.getElementById('notificationForm').reset();
        document.getElementById('notificationId').value = '';
        toggleTargetFields();
        this.style.display = 'none';
    });

    document.getElementById('notificationForm').addEventListener('submit', function(e){
        e.preventDefault();
        const formData = new FormData(this);
        const id = document.getElementById('notificationId').value;
        const type = document.getElementById('notificationType').value;
        const complexId = document.getElementById('notificationComplexId').value;
        const personalAccount = document.getElementById('notificationPersonalAccount').value.trim();

        if (type === 'complex' && !complexId) {
            document.getElementById('notificationResult').innerHTML = "<p style='color:red;'>Для типа 'Для комплекса' нужно выбрать ЖК.</p>";
            return;
        }
        if (type === 'personal' && !personalAccount) {
            document.getElementById('notificationResult').innerHTML = "<p style='color:red;'>Для типа 'Личное' укажите лицевой счёт пользователя.</p>";
            return;
        }

        const url = id ? `notification_request.php?update=${encodeURIComponent(id)}` : `notification_request.php`;

        fetch(url, { method: 'POST', body: formData })
            .then(r => r.text())
            .then(html => {
                document.getElementById('notificationResult').innerHTML = html;
                setTimeout(() => location.reload(), 1000);
            });
    });

    function deleteNotification(id){
        if(confirm("Удалить уведомление?")){
            fetch(`notification_request.php?delete=${encodeURIComponent(id)}`)
                .then(r => r.text())
                .then(alert)
                .then(() => location.reload());
        }
    }

    function openModal(img) {
        alert('Открытие превью не реализовано в этом шаблоне.');
    }
</script>
</body>
</html>
