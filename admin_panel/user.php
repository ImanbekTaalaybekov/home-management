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
$totalUsers = $countStmt->fetchColumn();

$totalPages = ceil($totalUsers / $perPage);
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
                <label>Лицевой счет:</label>
                <input type="text" name="personal_account" id="userAccount">
            </div>
            <div class="form-group">
                <label>Телефон:</label>
                <input type="text" name="phone_number" id="userPhone">
            </div>
            <div class="form-group">
                <label>Пароль:</label>
                <input type="password" name="password" id="userPassword" required>
            </div>
            <div class="form-group">
                <label>Жилой комплекс:</label>
                <select name="residential_complex_id" id="userComplex">
                    <option value="">- Выберите ЖК -</option>
                    <?php foreach ($complexes as $complex): ?>
                        <option value="<?= $complex['id'] ?>"><?= htmlspecialchars($complex['name']) ?></option>
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
            <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                   placeholder="Поиск (лицевой счет / имя / номер телефона)" class="user-search-input">
            <select name="complex_id" class="user-search-select">
                <option value="">Все ЖК</option>
                <?php foreach ($complexes as $complex): ?>
                    <option value="<?= $complex['id'] ?>" <?= (isset($_GET['complex_id']) && $_GET['complex_id'] == $complex['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($complex['name']) ?>
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
                <th>Лицевой счет</th>
                <th>Телефон</th>
                <th>Жилой комплекс</th>
                <th>Дата создания</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr id="user-<?= $user['id'] ?>">
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['personal_account']) ?></td>
                    <td><?= htmlspecialchars($user['phone_number']) ?></td>
                    <td><?= htmlspecialchars($user['complex_name'] ?: '-') ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                    <td>
                        <button onclick="editUser(<?= $user['id'] ?>, '<?= $user['name'] ?>', '<?= $user['personal_account'] ?>', '<?= $user['phone_number'] ?>', '<?= $user['residential_complex_id'] ?>')">
                            Изменить
                        </button>
                        <button class="delete-btn" onclick="deleteUser(<?= $user['id'] ?>)">Удалить</button>
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
        let formData = new FormData(this);
        let userId = document.getElementById('userId').value;
        let currentPage = new URLSearchParams(window.location.search).get('page') || 1;
        let url = userId ? `user_request.php?update=${userId}&page=${currentPage}` : `user_request.php?page=${currentPage}`;

        fetch(url, {
            method: 'POST',
            body: formData
        })
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
            let currentPage = new URLSearchParams(window.location.search).get('page') || 1;
            fetch(`user_request.php?delete=${id}&page=${currentPage}`)
                .then(res => res.text())
                .then(response => {
                    alert(response);
                    location.reload();
                })
                .catch(err => alert('Ошибка: ' + err));
        }
    }

    function editUser(id, name, account, phone, complex) {
        document.getElementById('userId').value = id;
        document.getElementById('userName').value = name;
        document.getElementById('userAccount').value = account;
        document.getElementById('userPhone').value = phone;
        document.getElementById('userComplex').value = complex || '';

        document.getElementById('cancelEdit').style.display = 'inline-block';
    }

    document.getElementById('cancelEdit').addEventListener('click', function () {
        document.getElementById('userForm').reset();
        document.getElementById('userId').value = '';
        this.style.display = 'none';
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const phoneInput = document.getElementById('userPhone');

        phoneInput.addEventListener('input', function (e) {
            let value = phoneInput.value.replace(/\D/g, '');

            if (value.startsWith('8')) {
                value = '7' + value.slice(1);
            }
            if (value.length > 11) {
                value = value.slice(0, 11);
            }

            let formatted = '+7';
            if (value.length > 1) {
                formatted += ' (' + value.substring(1, 4);
            }
            if (value.length >= 4) {
                formatted += ') ' + value.substring(4, 7);
            }
            if (value.length >= 7) {
                formatted += '-' + value.substring(7, 9);
            }
            if (value.length >= 9) {
                formatted += '-' + value.substring(9, 11);
            }

            phoneInput.value = formatted;
        });
    });
</script>
</body>
</html>