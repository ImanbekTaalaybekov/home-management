<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'include/database.php';

$complexes = $pdo->query("SELECT * FROM residential_complexes ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$perPage = 20;
$where = [];
$params = [];

if (!empty($_GET['search'])) {
    $where[] = "(users.name ILIKE :search OR users.phone_number ILIKE :search OR users.personal_account ILIKE :search OR users.login ILIKE :search)";
    $params[':search'] = '%' . $_GET['search'] . '%';
}

if (!empty($_GET['complex_id'])) {
    $where[] = "users.residential_complex_id::bigint = :complex_id";
    $params[':complex_id'] = (int)$_GET['complex_id'];
}

$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users $whereSql");
$countStmt->execute($params);
$totalUsers = (int)$countStmt->fetchColumn();

$totalPages = max(1, (int)ceil($totalUsers / $perPage));
$currentPage = isset($_GET['page']) ? max(1, min($totalPages, (int)$_GET['page'])) : 1;
$offset = ($currentPage - 1) * $perPage;

$sql = "
SELECT users.*, residential_complexes.name AS complex_name 
FROM users 
LEFT JOIN residential_complexes ON users.residential_complex_id::bigint = residential_complexes.id
$whereSql
ORDER BY users.created_at DESC
LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Wires Home</title>
    <link rel="stylesheet" href="include/style.css">
    <style>
        .modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.45); display: none; align-items: center; justify-content: center; z-index: 999; }
        .modal { width: 100%; max-width: 520px; background: #fff; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,.2); overflow: hidden; }
        .modal-header, .modal-footer { padding: 14px 16px; background: #f6f7f8; }
        .modal-header { display:flex; justify-content:space-between; align-items:center; }
        .modal-title { margin: 0; font-size: 18px; }
        .modal-close { border:0; background:transparent; font-size:20px; cursor:pointer; }
        .modal-body { padding: 16px; }
        .modal .form-row { margin-bottom: 12px; }
        .modal label { display:block; margin-bottom: 6px; font-weight: 600; }
        .modal input[type="text"], .modal input[type="password"], .modal select { width:100%; padding:8px 10px; border:1px solid #d0d7de; border-radius:6px; }
        .modal .hint { color:#6a737d; font-size: 12px; margin-top: 4px; }
        .modal .row-two { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .modal-footer .btn { padding: 8px 14px; border-radius: 6px; border: 0; cursor: pointer; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-secondary { background: #e5e7eb; color: #111827; }
        .btn-danger { background: #ef4444; color: #fff; }
        .actions-cell button { margin-right: 6px; }
        .plus-btn { padding: 4px 10px; font-size: 18px; line-height: 1; }
        .text-muted { color: #6b7280; }
    </style>
</head>
<body>
<div class="container">
    <section class="user-form-section">
        <h1 class="user-h1">Управление пользователями</h1>
        <h2>Создать нового пользователя (роль: owner)</h2>
        <form id="userForm">
            <input type="hidden" name="id" id="userId">
            <div class="form-group">
                <label>Имя:</label>
                <input type="text" name="name" id="userName" required>
            </div>

            <div class="form-group">
                <label>Логин:</label>
                <input type="text" name="login" id="userLogin" required>
            </div>

            <div class="form-group">
                <label>Лицевой счёт:</label>
                <input type="text" name="personal_account" id="userAccount">
            </div>

            <div class="form-group">
                <label>Пароль:</label>
                <input type="password" name="password" id="userPassword" required>
            </div>

            <div class="form-group">
                <label>Номер блока:</label>
                <input type="text" name="block_number" id="userBlock">
            </div>
            <div class="form-group">
                <label>Номер квартиры:</label>
                <input type="text" name="apartment_number" id="userApartment">
            </div>

            <div class="form-group">
                <label>Жилой комплекс:</label>
                <select name="residential_complex_id" id="userComplex">
                    <option value="">- Выберите ЖК -</option>
                    <?php foreach ($complexes as $complex): ?>
                        <option value="<?= e($complex['id']) ?>"><?= e($complex['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit">Сохранить (owner)</button>
            <button type="button" id="cancelEdit" style="display:none;">Отмена</button>
        </form>
        <div id="userResult"></div>
    </section>

    <section class="user-list-section">
        <a href="main.php">
            <button>← Вернуться в меню</button>
        </a>

        <h2>Фильтрация и поиск</h2>

        <form id="searchForm" method="get" style="margin-bottom: 20px;" class="user-search-form">
            <input type="text" name="search" value="<?= e($_GET['search'] ?? '') ?>"
                   placeholder="Поиск (лицевой счёт / имя / телефон / логин)" class="user-search-input">
            <select name="complex_id" class="user-search-select">
                <option value="">Все ЖК</option>
                <?php foreach ($complexes as $complex): ?>
                    <option value="<?= e($complex['id']) ?>" <?= (isset($_GET['complex_id']) && $_GET['complex_id'] == $complex['id']) ? 'selected' : '' ?>>
                        <?= e($complex['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Поиск</button>
        </form>

        <h2>Список пользователей</h2>
        <table class="users-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Имя</th>
                <th>Логин</th>
                <th>Роль</th>
                <th>Лицевой счёт</th>
                <th>Телефон</th>
                <th>Блок</th>
                <th>Квартира</th>
                <th>Жилой комплекс</th>
                <th>Дата создания</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr id="user-<?= e($user['id']) ?>">
                    <td><?= e($user['id']) ?></td>
                    <td><?= e($user['name']) ?></td>
                    <td><?= e($user['login'] ?? '') ?></td>
                    <td><?= e($user['role'] ?? '-') ?></td>
                    <td><?= e($user['personal_account']) ?></td>
                    <td><?= e($user['phone_number']) ?></td>
                    <td><?= e($user['block_number']) ?></td>
                    <td><?= e($user['apartment_number']) ?></td>
                    <td><?= e($user['complex_name'] ?: '-') ?></td>
                    <td><?= e(date('d.m.Y H:i', strtotime($user['created_at']))) ?></td>
                    <td class="actions-cell">
                        <button onclick="editUser(
                        <?= (int)$user['id'] ?>,
                                '<?= e($user['name']) ?>',
                                '<?= e($user['login']) ?>',
                                '<?= e($user['personal_account']) ?>',
                                '<?= e($user['residential_complex_id']) ?>',
                                '<?= e($user['block_number']) ?>',
                                '<?= e($user['apartment_number']) ?>'
                                )">Изменить</button>
                        <button class="delete-btn btn-danger" onclick="deleteUser(<?= (int)$user['id'] ?>)">Удалить</button>

                        <?php if (($user['role'] ?? '') === 'owner'): ?>
                            <button class="plus-btn" title="Добавить пользователя (family/tenant) этого владельца"
                                    onclick="openSubUserModal(<?= (int)$user['id'] ?>, '<?= e($user['login']) ?>')">＋</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>">&laquo;</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" <?= $i == $currentPage ? 'class="active"' : '' ?>><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>">&raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="footer-margin"></div>
    </section>
</div>

<div id="subUserBackdrop" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="subUserTitle">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="subUserTitle">Новый пользователь (family/tenant)</h3>
            <button class="modal-close" type="button" aria-label="Закрыть" onclick="closeSubUserModal()">×</button>
        </div>
        <form id="subUserForm">
            <div class="modal-body">
                <input type="hidden" id="subOwnerId">
                <div class="form-row">
                    <label>Логин владельца</label>
                    <input type="text" id="subOwnerLogin" readonly>
                    <div class="hint">К логину владельца добавится суффикс: <code>owner_login_suffix</code></div>
                </div>

                <div class="form-row">
                    <label>Имя под-пользователя *</label>
                    <input type="text" id="subName" placeholder="Например: Иван Иванов" required>
                </div>

                <div class="row-two">
                    <div class="form-row">
                        <label>Суффикс логина *</label>
                        <input type="text" id="subSuffix" placeholder="например: wife, son, t1" required>
                        <div class="hint">Разрешены буквы/цифры/._-</div>
                    </div>
                    <div class="form-row">
                        <label>Итоговый логин</label>
                        <input type="text" id="subPreviewLogin" readonly class="text-muted">
                    </div>
                </div>

                <div class="row-two">
                    <div class="form-row">
                        <label>Роль *</label>
                        <select id="subRole" required>
                            <option value="family">family</option>
                            <option value="tenant">tenant</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label>Пароль *</label>
                        <input type="password" id="subPassword" placeholder="Пароль" required>
                    </div>
                </div>

                <div id="subUserMsg" class="hint"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeSubUserModal()">Отмена</button>
                <button type="submit" class="btn btn-primary">Создать</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('userForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const userId = document.getElementById('userId').value;
        const currentPage = new URLSearchParams(window.location.search).get('page') || 1;
        const url = userId
            ? `user_request.php?update=${encodeURIComponent(userId)}&page=${encodeURIComponent(currentPage)}`
            : `user_request.php?page=${encodeURIComponent(currentPage)}`;

        fetch(url, { method: 'POST', body: formData })
            .then(res => res.text())
            .then(response => {
                document.getElementById('userResult').innerHTML = response;
                setTimeout(() => location.reload(), 1000);
            })
            .catch(err => {
                document.getElementById('userResult').innerHTML = '<p style="color:red;">Ошибка: ' + err + '</p>';
            });
    });

    function deleteUser(id) {
        if (confirm('Удалить пользователя ID ' + id + '?')) {
            const currentPage = new URLSearchParams(window.location.search).get('page') || 1;
            fetch(`user_request.php?delete=${encodeURIComponent(id)}&page=${encodeURIComponent(currentPage)}`)
                .then(res => res.text())
                .then(response => {
                    alert(response);
                    location.reload();
                })
                .catch(err => alert('Ошибка: ' + err));
        }
    }

    function editUser(id, name, login, account, complex, block, apartment) {
        document.getElementById('userId').value = id;
        document.getElementById('userName').value = name;
        document.getElementById('userLogin').value = login || '';
        document.getElementById('userAccount').value = account || '';
        document.getElementById('userComplex').value = complex || '';
        document.getElementById('userBlock').value = block || '';
        document.getElementById('userApartment').value = apartment || '';
        document.getElementById('userPassword').required = false;
        document.getElementById('cancelEdit').style.display = 'inline-block';
    }

    document.getElementById('cancelEdit').addEventListener('click', function () {
        document.getElementById('userForm').reset();
        document.getElementById('userId').value = '';
        document.getElementById('userPassword').required = true;
        this.style.display = 'none';
    });

    const backdrop = document.getElementById('subUserBackdrop');
    const formSub = document.getElementById('subUserForm');
    const ownerIdInput = document.getElementById('subOwnerId');
    const ownerLoginInput = document.getElementById('subOwnerLogin');
    const nameInput = document.getElementById('subName');
    const suffixInput = document.getElementById('subSuffix');
    const previewLoginInput = document.getElementById('subPreviewLogin');
    const roleSelect = document.getElementById('subRole');
    const passwordInput = document.getElementById('subPassword');
    const msgBox = document.getElementById('subUserMsg');

    function openSubUserModal(ownerId, ownerLogin) {
        ownerIdInput.value = ownerId;
        ownerLoginInput.value = ownerLogin || '';
        nameInput.value = '';
        suffixInput.value = '';
        roleSelect.value = 'family';
        passwordInput.value = '';
        msgBox.textContent = '';
        updatePreviewLogin();
        backdrop.style.display = 'flex';
        setTimeout(() => nameInput.focus(), 0);
    }

    function closeSubUserModal() {
        backdrop.style.display = 'none';
    }

    function updatePreviewLogin() {
        const base = ownerLoginInput.value.trim();
        const suf  = suffixInput.value.trim();
        previewLoginInput.value = suf ? (base + '_' + suf) : base;
    }

    suffixInput.addEventListener('input', updatePreviewLogin);

    formSub.addEventListener('submit', function (e) {
        e.preventDefault();
        msgBox.textContent = '';

        const ownerId = ownerIdInput.value;
        const name = nameInput.value.trim();
        const suffix = suffixInput.value.trim();
        const role = roleSelect.value.trim().toLowerCase();
        const password = passwordInput.value;

        if (!name) {
            msgBox.textContent = 'Имя обязательно';
            nameInput.focus();
            return;
        }
        if (!suffix || !/^[a-zA-Z0-9_\-\.]+$/.test(suffix)) {
            msgBox.textContent = 'Некорректный суффикс. Разрешены буквы/цифры/._-';
            suffixInput.focus();
            return;
        }
        if (!['family','tenant'].includes(role)) {
            msgBox.textContent = 'Некорректная роль. Разрешены: family, tenant';
            roleSelect.focus();
            return;
        }
        if (!password) {
            msgBox.textContent = 'Пароль обязателен';
            passwordInput.focus();
            return;
        }

        const fd = new FormData();
        fd.append('name', name);
        fd.append('suffix', suffix);
        fd.append('role', role);
        fd.append('password', password);

        fetch('user_request.php?create_sub=' + encodeURIComponent(ownerId), {
            method: 'POST',
            body: fd
        })
            .then(r => r.text())
            .then(t => {
                alert(t);
                closeSubUserModal();
                location.reload();
            })
            .catch(e => {
                msgBox.textContent = 'Ошибка: ' + e;
            });
    });

    backdrop.addEventListener('click', function (e) {
        if (e.target === backdrop) closeSubUserModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && backdrop.style.display === 'flex') {
            closeSubUserModal();
        }
    });
</script>
</body>
</html>