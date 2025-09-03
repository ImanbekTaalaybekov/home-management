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
    $where[] = "(users.name ILIKE :search OR users.phone_number ILIKE :search OR users.personal_account ILIKE :search)";
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
</head>
<body>
<div class="container">
    <section class="user-form-section">
        <h1 class="user-h1">Управление пользователями</h1>
        <h2>Создать нового пользователя</h2>
        <form id="userForm">
            <input type="hidden" name="id" id="userId">
            <div class="form-group">
                <label>Имя:</label>
                <input type="text" name="name" id="userName" required>
            </div>
            <div class="form-group">
                <label>Лицевой счёт:</label>
                <input type="text" name="personal_account" id="userAccount">
            </div>
            <div class="form-group">
                <label>Телефон:</label>
                <input type="text" name="phone_number" id="userPhone" placeholder="+XXXXXXXXXXXXXXX" inputmode="tel">
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
            <button type="submit">Сохранить</button>
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
                   placeholder="Поиск (лицевой счёт / имя / телефон)" class="user-search-input">
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
                    <td><?= e($user['personal_account']) ?></td>
                    <td><?= e($user['phone_number']) ?></td>
                    <td><?= e($user['block_number']) ?></td>
                    <td><?= e($user['apartment_number']) ?></td>
                    <td><?= e($user['complex_name'] ?: '-') ?></td>
                    <td><?= e(date('d.m.Y H:i', strtotime($user['created_at']))) ?></td>
                    <td>
                        <button onclick="editUser(
                        <?= (int)$user['id'] ?>,
                                '<?= e($user['name']) ?>',
                                '<?= e($user['personal_account']) ?>',
                                '<?= e($user['phone_number']) ?>',
                                '<?= e($user['residential_complex_id']) ?>',
                                '<?= e($user['block_number']) ?>',
                                '<?= e($user['apartment_number']) ?>'
                                )">Изменить</button>
                        <button class="delete-btn" onclick="deleteUser(<?= (int)$user['id'] ?>)">Удалить</button>
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

<script>
    document.getElementById('userForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const userId = document.getElementById('userId').value;
        const currentPage = new URLSearchParams(window.location.search).get('page') || 1;
        const url = userId ? `user_request.php?update=${encodeURIComponent(userId)}&page=${encodeURIComponent(currentPage)}` : `user_request.php?page=${encodeURIComponent(currentPage)}`;

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

    function editUser(id, name, account, phone, complex, block, apartment) {
        document.getElementById('userId').value = id;
        document.getElementById('userName').value = name;
        document.getElementById('userAccount').value = account;
        document.getElementById('userPhone').value = phone;
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
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const phoneInput = document.getElementById('userPhone');

        function normalizePhone(value) {
            let v = value.replace(/[^\d+]/g, '');

            if (!v.startsWith('+')) {
                v = '+' + v.replace(/\D/g, '');
            } else {
                v = '+' + v.slice(1).replace(/\D/g, '');
            }

            const digits = v.slice(1, 1 + 15);
            return '+' + digits;
        }

        phoneInput.addEventListener('input', function () {
            const before = phoneInput.value;
            const pos = phoneInput.selectionStart || before.length;
            phoneInput.value = normalizePhone(before);
            const delta = phoneInput.value.length - before.length;
            try {
                const newPos = Math.max(1, pos + delta);
                phoneInput.setSelectionRange(newPos, newPos);
            } catch(e) {}
        });

        const form = document.getElementById('userForm');
        form.addEventListener('submit', function (e) {
            const v = phoneInput.value.trim();
            if (v !== '' && !/^\+\d{5,15}$/.test(v)) {
                e.preventDefault();
                document.getElementById('userResult').innerHTML =
                    '<p style="color:red;">Телефон должен быть в формате: + и от 5 до 15 цифр (E.164).</p>';
                return false;
            }
        });
    });
</script>
</body>
</html>