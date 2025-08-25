<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'include/database.php';

function safeField($value){ return $value ? htmlspecialchars($value) : '—'; }
function safeDate($date){ return $date ? date('d.m.Y H:i', strtotime($date)) : '—'; }
function humanStatus($status){ return $status === 'done' ? 'Готово' : 'В обработке'; }

$categories = $pdo->query("SELECT DISTINCT name_rus FROM service_request_categories ORDER BY name_rus ASC")->fetchAll(PDO::FETCH_COLUMN);

$allMasters = $pdo->query("
    SELECT m.id, m.name, m.service_request_category_id, c.name AS category_name, c.name_rus AS category_name_rus
    FROM service_request_masters m
    LEFT JOIN service_request_categories c ON c.id = m.service_request_category_id
    ORDER BY m.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("
    SELECT sr.*, 
           u.name AS user_name, 
           c.id AS category_id,
           c.name AS type_tech,
           c.name_rus AS type_rus,
           m.name AS master_name,
           (SELECT path FROM photos 
            WHERE photoable_type = 'App\\Models\\ServiceRequest' AND photoable_id = sr.id 
            LIMIT 1) AS photo_path
    FROM service_requests sr
    LEFT JOIN users u ON sr.user_id = u.id
    LEFT JOIN service_request_categories c ON sr.type = c.name
    LEFT JOIN service_request_masters m ON m.id = sr.master_id
    ORDER BY sr.created_at DESC
");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

function renderMasterOptions(array $allMasters, $categoryId, $selectedId = null){
    $html = '<option value="">— Выберите мастера —</option>';
    foreach ($allMasters as $m) {
        if ((int)$m['service_request_category_id'] === (int)$categoryId) {
            $sel = ($selectedId && (int)$selectedId === (int)$m['id']) ? ' selected' : '';
            $name = htmlspecialchars($m['name']);
            $html .= "<option value=\"{$m['id']}\"{$sel}>{$name}</option>";
        }
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление заявками на вызов мастера</title>
    <link rel="stylesheet" href="include/style.css">
    <style>
        .preview-img { max-width: 50px; height: auto; display: block; margin: 0 auto; }
        .assign-wrap { display:flex; gap:8px; align-items: center; }
        .assign-wrap select { min-width: 200px; }
        .note { font-size: 12px; color: #666; }
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
    <a href="main.php"><button>← Вернуться в меню</button></a>

    <div style="margin: 20px 0;">
        <label for="statusFilter">Фильтр по статусу:</label>
        <select id="statusFilter">
            <option value="">Все</option>
            <option value="pending">В обработке</option>
            <option value="done">Готово</option>
        </select>

        <label for="typeFilter" style="margin-left: 20px;">Фильтр по типу мастера:</label>
        <select id="typeFilter">
            <option value="">Все</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <table class="service-requests-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Пользователь</th>
            <th>Тип мастера</th>
            <th>Описание проблемы</th>
            <th>Статус</th>
            <th>Назначенный мастер</th>
            <th>Назначить/сменить мастера</th>
            <th>Дата заявки</th>
            <th>Фото</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody id="requestsList">
        <?php foreach ($requests as $request): ?>
            <tr id="request-<?= (int)$request['id'] ?>">
                <td><?= (int)$request['id'] ?></td>
                <td><?= safeField($request['user_name']) ?></td>
                <td><?= safeField($request['type_rus']) ?></td>
                <td><?= nl2br(safeField($request['description'])) ?></td>
                <td id="status-<?= (int)$request['id'] ?>"><?= humanStatus($request['status']) ?></td>

                <td id="current-master-<?= (int)$request['id'] ?>">
                    <?= $request['master_name'] ? htmlspecialchars($request['master_name']) : '<span class="note">не назначен</span>' ?>
                </td>

                <td>
                    <?php if (!empty($request['category_id'])): ?>
                        <div class="assign-wrap">
                            <select id="assign-select-<?= (int)$request['id'] ?>">
                                <?= renderMasterOptions($allMasters, $request['category_id'], $request['master_id'] ?? null) ?>
                            </select>
                            <button onclick="assignMaster(<?= (int)$request['id'] ?>)">Назначить</button>
                        </div>
                        <div class="note">Категория: <?= htmlspecialchars($request['type_rus'] ?: $request['type_tech'] ?: '—') ?></div>
                    <?php else: ?>
                        <span class="note">категория не определена</span>
                    <?php endif; ?>
                </td>

                <td><?= safeDate($request['created_at']) ?></td>
                <td>
                    <?php if ($request['photo_path']): ?>
                        <img src="<?= htmlspecialchars('https://home-folder.wires.kz/storage/' . $request['photo_path']) ?>"
                             class="preview-img" alt="Фото" onclick="openModal(this)">
                    <?php else: ?>
                        Нет
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($request['status'] !== 'done'): ?>
                        <button onclick="markDone(<?= (int)$request['id'] ?>)">Готово</button>
                    <?php endif; ?>
                    <button onclick="deleteRequest(<?= (int)$request['id'] ?>)">Удалить</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function markDone(id) {
        fetch('service_request.php?action=done&id=' + id)
            .then(r => r.text())
            .then(data => {
                document.getElementById('status-' + id).innerText = 'Готово';
                alert(data);
            })
            .catch(err => alert('Ошибка: ' + err));
    }

    function deleteRequest(id) {
        if (confirm('Удалить заявку ID ' + id + '?')) {
            fetch('service_request.php?action=delete&id=' + id)
                .then(r => r.text())
                .then(data => {
                    alert(data);
                    const row = document.getElementById('request-' + id);
                    if (row) row.remove();
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
    document.getElementById("imageModal").addEventListener("click", function (e) {
        if (e.target === this) this.style.display = "none";
    });

    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('typeFilter').addEventListener('change', applyFilters);

    function applyFilters() {
        const status = document.getElementById('statusFilter').value;
        const type = document.getElementById('typeFilter').value;
        let url = 'service_request.php?filter=1';
        if (status) url += '&status=' + encodeURIComponent(status);
        if (type) url += '&type=' + encodeURIComponent(type);

        fetch(url)
            .then(r => r.text())
            .then(html => {
                document.getElementById('requestsList').innerHTML = html;
            })
            .catch(err => console.error('Ошибка фильтрации:', err));
    }

    function assignMaster(requestId){
        const sel = document.getElementById('assign-select-' + requestId);
        if (!sel || !sel.value) { alert('Выберите мастера'); return; }

        const masterId = sel.value;
        fetch('service_request.php?action=assign_master&id=' + requestId + '&master_id=' + masterId)
            .then(r => r.text())
            .then(msg => {
                alert(msg);
                const currentCell = document.getElementById('current-master-' + requestId);
                const selectedText = sel.options[sel.selectedIndex].text;
                if (currentCell) currentCell.innerText = selectedText;
            })
            .catch(err => alert('Ошибка: ' + err));
    }
</script>

</body>
</html>