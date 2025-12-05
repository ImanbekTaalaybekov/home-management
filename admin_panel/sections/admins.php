<?php

require __DIR__ . '/../include/auth.php';
require __DIR__ . '/../include/config.php';

$apiBaseUrl = defined('API_BASE_URL') ? API_BASE_URL : 'https://home-folder.wires.kz/api/admin';
$token      = $_SESSION['auth_token'] ?? null;

$adminName  = $_SESSION['admin_name'] ?? '';
$adminRole  = $_SESSION['admin_role'] ?? '';

$roleFilter = $_GET['role'] ?? '';
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 10;

$admins         = [];
$errorMessage   = null;
$successMessage = null;

$roleLabels = [
        'admin'      => 'Администратор',
        'service'    => 'Сервис',
        'accounting' => 'Бухгалтер',
];

$accessLabels = [
        'all'        => 'Все',
        'service'    => 'Службы',
        'accounting' => 'Бухгалтерия',
];

if ($token && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    $commonHeaders = [
            'Accept: application/json',
            'Authorization: ' . 'Bearer ' . $token,
            'Content-Type: application/json',
    ];

    if ($action === 'create_admin') {
        $username = trim($_POST['username'] ?? '');
        $name     = trim($_POST['name'] ?? '');
        $role     = trim($_POST['role'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($username === '' || $name === '' || $role === '' || $password === '') {
            $errorMessage = 'Заполните логин, имя, роль и пароль';
        } else {
            $accesses = [];
            if (isset($_POST['accesses'])) {
                if (is_array($_POST['accesses'])) {
                    $accesses = array_values(array_unique(array_filter(array_map('trim', $_POST['accesses']))));
                } else {
                    $accessesRaw = trim($_POST['accesses']);
                    if ($accessesRaw !== '') {
                        $accesses = array_filter(array_map('trim', explode(',', $accessesRaw)));
                    }
                }
            }

            $payload = [
                    'username' => $username,
                    'name'     => $name,
                    'role'     => $role,
                    'password' => $password,
                    'accesses' => $accesses,
                    'device'   => 'web-admin',
            ];

            $ch = curl_init($apiBaseUrl . '/register');
            curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_HTTPHEADER     => $commonHeaders,
                    CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]);
            $response = curl_exec($ch);
            $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($status === 200) {
                $successMessage = 'Сотрудник успешно создан';
            } else {
                $data = json_decode($response, true);
                $errorMessage = $data['message'] ?? ('Ошибка создания сотрудника (' . $status . ')');
            }
        }
    }

    if ($action === 'update_admin') {
        $id       = (int)($_POST['admin_id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $name     = trim($_POST['name'] ?? '');
        $role     = trim($_POST['role'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($id <= 0) {
            $errorMessage = 'Не указан ID сотрудника для обновления';
        } else {
            $payload = [];

            if ($username !== '') $payload['username'] = $username;
            if ($name !== '')     $payload['name']     = $name;
            if ($role !== '')     $payload['role']     = $role;

            if (isset($_POST['accesses'])) {
                $accesses = [];
                if (is_array($_POST['accesses'])) {
                    $accesses = array_values(array_unique(array_filter(array_map('trim', $_POST['accesses']))));
                } else {
                    $accessesRaw = trim($_POST['accesses']);
                    if ($accessesRaw !== '') {
                        $accesses = array_filter(array_map('trim', explode(',', $accessesRaw)));
                    }
                }
                $payload['accesses'] = $accesses;
            }

            if ($password !== '') {
                $payload['password'] = $password;
            }

            if (!empty($payload)) {
                $ch = curl_init($apiBaseUrl . '/admins/' . $id);
                curl_setopt_array($ch, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_CUSTOMREQUEST  => 'PUT',
                        CURLOPT_HTTPHEADER     => $commonHeaders,
                        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
                ]);
                $response = curl_exec($ch);
                $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($status === 200) {
                    $successMessage = 'Данные сотрудника обновлены';
                } else {
                    $data = json_decode($response, true);
                    $errorMessage = $data['message'] ?? ('Ошибка обновления сотрудника (' . $status . ')');
                }
            } else {
                $errorMessage = 'Нет данных для обновления';
            }
        }
    }

    if ($action === 'delete_admin') {
        $id = (int)($_POST['admin_id'] ?? 0);

        if ($id <= 0) {
            $errorMessage = 'Некорректный ID для удаления';
        } else {
            $ch = curl_init($apiBaseUrl . '/admins/' . $id);
            curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CUSTOMREQUEST  => 'DELETE',
                    CURLOPT_HTTPHEADER     => [
                            'Accept: application/json',
                            'Authorization: ' . 'Bearer ' . $token,
                    ],
            ]);
            $response = curl_exec($ch);
            $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($status === 200) {
                $successMessage = 'Сотрудник удалён';
            } else {
                $data = json_decode($response, true);
                $errorMessage = $data['message'] ?? ('Ошибка удаления сотрудника (' . $status . ')');
            }
        }
    }

    $qs = $_SERVER['QUERY_STRING'] ? ('?' . $_SERVER['QUERY_STRING']) : '';
    header('Location: ' . $_SERVER['PHP_SELF'] . $qs);
    exit;
}

if ($token) {
    $url = $apiBaseUrl . '/admins';
    if ($roleFilter !== '') {
        $url .= '?role=' . urlencode($roleFilter);
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                    'Accept: application/json',
                    'Authorization: ' . 'Bearer ' . $token,
            ],
    ]);
    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status === 200) {
        $data   = json_decode($response, true);
        $admins = $data['users'] ?? [];
    } else {
        if (!$errorMessage) {
            $errorMessage = 'Не удалось загрузить список сотрудников (' . $status . ')';
        }
    }
} else {
    $errorMessage = 'Нет токена авторизации';
}

$totalAdmins = count($admins);
$totalPages  = max(1, (int)ceil($totalAdmins / $perPage));
$page        = min($page, $totalPages);

$offset      = ($page - 1) * $perPage;
$adminsPage  = array_slice($admins, $offset, $perPage);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>WIRES HOME — Сотрудники</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/include/style.css">
    <style>
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,0.65);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 50;
        }
        .modal-backdrop.modal-open {
            display: flex;
        }
        .modal {
            background: #ffffff;
            border-radius: 16px;
            padding: 18px 18px 20px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 15px 40px rgba(15,23,42,0.4);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .modal-title {
            font-size: 18px;
            font-weight: 600;
        }
        .modal-close {
            border: none;
            background: transparent;
            font-size: 20px;
            cursor: pointer;
        }
        .modal-footer {
            margin-top: 10px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }
        .btn-secondary {
            padding: 7px 14px;
            border-radius: 999px;
            border: 1px solid #d1d5db;
            background: #f9fafb;
            cursor: pointer;
            font-size: 14px;
        }
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 16px;
            margin-top: 6px;
        }
        .checkbox-group label {
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
    </style>
</head>
<body>

<div class="layout">
    <?php include __DIR__ . '/../include/header.php'; ?>

    <aside class="sidebar">
        <?php include __DIR__ . '/../include/sidebar.php'; ?>
    </aside>

    <main class="content">
        <h1 class="content__title">Сотрудники</h1>
        <p class="content__subtitle">Регистрация, список, обновление и удаление администраторов.</p>

        <?php if ($successMessage): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <form method="get" class="filter-form">
                <label>
                    Фильтр по роли
                    <select name="role">
                        <option value="">Все роли</option>
                        <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>
                            Администратор
                        </option>
                        <option value="service" <?= $roleFilter === 'service' ? 'selected' : '' ?>>
                            Сервис
                        </option>
                        <option value="accounting" <?= $roleFilter === 'accounting' ? 'selected' : '' ?>>
                            Бухгалтер
                        </option>
                    </select>
                </label>
                <button type="submit" class="filter-button">Применить</button>
                <button type="button"
                        class="button-primary"
                        style="margin-left:auto;"
                        onclick="openAdminModalCreate()">
                    + Добавить сотрудника
                </button>
            </form>
        </div>

        <div class="table-wrapper">
            <table class="admins-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Логин</th>
                    <th>Имя</th>
                    <th>Роль</th>
                    <th>Accesses</th>
                    <th>Client ID</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($adminsPage)): ?>
                    <tr>
                        <td colspan="7">Сотрудники не найдены</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($adminsPage as $admin): ?>
                        <?php
                        $id       = $admin['id']        ?? '';
                        $username = $admin['username']  ?? '';
                        $name     = $admin['name']      ?? '';
                        $roleVal  = $admin['role']      ?? '';
                        $clientId = $admin['client_id'] ?? '';
                        $accesses = $admin['accesses']  ?? '';

                        if (is_array($accesses)) {
                            $accessesReadable = [];
                            foreach ($accesses as $acc) {
                                $accessesReadable[] = $accessLabels[$acc] ?? $acc;
                            }
                            $accessesStr = implode(', ', $accessesReadable);
                            $accessesRawForData = implode(',', $accesses);
                        } else {
                            $accessesStr       = $accessLabels[$accesses] ?? (string)$accesses;
                            $accessesRawForData = (string)$accesses;
                        }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?= htmlspecialchars($roleLabels[$roleVal] ?? $roleVal, ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td>
                                <span class="badge-code">
                                    <?= htmlspecialchars($accessesStr, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($clientId, ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <div class="admins-actions">
                                    <button type="button"
                                            class="btn-small btn-edit"
                                            onclick="openAdminModalEdit(this)"
                                            data-id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>"
                                            data-username="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
                                            data-name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                                            data-role="<?= htmlspecialchars($roleVal, ENT_QUOTES, 'UTF-8') ?>"
                                            data-accesses="<?= htmlspecialchars($accessesRawForData, ENT_QUOTES, 'UTF-8') ?>">
                                        Редактировать
                                    </button>

                                    <form method="post"
                                          onsubmit="return confirm('Удалить сотрудника?');"
                                          style="display:inline;">
                                        <input type="hidden" name="action" value="delete_admin">
                                        <input type="hidden" name="admin_id" value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="btn-small btn-delete">
                                            Удалить
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <?php
                    $link  = '?role=' . urlencode($roleFilter) . '&page=' . $p;
                    $class = $p === $page ? 'active-page' : '';
                    ?>
                    <a href="<?= $link ?>" class="<?= $class ?>"><?= $p ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    </main>
</div>

<div class="modal-backdrop" id="adminModalBackdrop">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title" id="adminModalTitle">Новый сотрудник</div>
            <button type="button" class="modal-close" onclick="closeAdminModal()">×</button>
        </div>

        <form method="post" class="login-form" id="adminModalForm">
            <input type="hidden" name="action" id="adminModalAction" value="create_admin">
            <input type="hidden" name="admin_id" id="adminModalId" value="">

            <div class="login-group">
                <label>Логин</label>
                <input type="text" name="username" id="adminModalUsername" required>
            </div>

            <div class="login-group">
                <label>Имя</label>
                <input type="text" name="name" id="adminModalName" required>
            </div>

            <div class="login-group">
                <label>Роль</label>
                <select name="role" id="adminModalRole" required>
                    <option value="">Выберите роль</option>
                    <option value="admin">Администратор</option>
                    <option value="service">Сервис</option>
                    <option value="accounting">Бухгалтер</option>
                </select>
            </div>

            <div class="login-group">
                <label>Пароль <span id="adminModalPasswordHint"></span></label>
                <input type="password" name="password" id="adminModalPassword">
            </div>

            <div class="login-group">
                <label>Доступы</label>
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="accesses[]" value="all">
                        Все
                    </label>
                    <label>
                        <input type="checkbox" name="accesses[]" value="service">
                        Службы
                    </label>
                    <label>
                        <input type="checkbox" name="accesses[]" value="accounting">
                        Бухгалтерия
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeAdminModal()">Отмена</button>
                <button type="submit" class="login-button" id="adminModalSubmit">
                    Создать
                </button>
            </div>
        </form>
    </div>
</div>

<script src="/include/scripts.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar-admin');

        if (sidebar) {
            sidebar.classList.add('sidebar__group--open');
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('menu_admins');

        if (sidebar) {
            sidebar.classList.add('menu-selected-point');
        }
    });
</script>
<script>
    function resetAccessCheckboxes() {
        const accessCheckboxes = document.querySelectorAll('#adminModalForm input[name="accesses[]"]');
        accessCheckboxes.forEach(cb => cb.checked = false);
    }

    function openAdminModalCreate() {
        const backdrop = document.getElementById('adminModalBackdrop');
        const title    = document.getElementById('adminModalTitle');
        const action   = document.getElementById('adminModalAction');
        const idInput  = document.getElementById('adminModalId');
        const username = document.getElementById('adminModalUsername');
        const name     = document.getElementById('adminModalName');
        const role     = document.getElementById('adminModalRole');
        const password = document.getElementById('adminModalPassword');
        const submit   = document.getElementById('adminModalSubmit');
        const hint     = document.getElementById('adminModalPasswordHint');

        title.textContent      = 'Новый сотрудник';
        action.value           = 'create_admin';
        idInput.value          = '';
        username.value         = '';
        name.value             = '';
        role.value             = '';
        password.value         = '';
        submit.textContent     = 'Создать';
        hint.textContent       = '(обязательно)';

        resetAccessCheckboxes();

        backdrop.classList.add('modal-open');
    }

    function openAdminModalEdit(btn) {
        const backdrop = document.getElementById('adminModalBackdrop');
        const title    = document.getElementById('adminModalTitle');
        const action   = document.getElementById('adminModalAction');
        const idInput  = document.getElementById('adminModalId');
        const username = document.getElementById('adminModalUsername');
        const name     = document.getElementById('adminModalName');
        const role     = document.getElementById('adminModalRole');
        const password = document.getElementById('adminModalPassword');
        const submit   = document.getElementById('adminModalSubmit');
        const hint     = document.getElementById('adminModalPasswordHint');

        const idVal       = btn.getAttribute('data-id') || '';
        const usernameVal = btn.getAttribute('data-username') || '';
        const nameVal     = btn.getAttribute('data-name') || '';
        const roleVal     = btn.getAttribute('data-role') || '';
        const accessesVal = btn.getAttribute('data-accesses') || '';

        title.textContent      = 'Редактировать сотрудника';
        action.value           = 'update_admin';
        idInput.value          = idVal;
        username.value         = usernameVal;
        name.value             = nameVal;
        role.value             = roleVal;
        password.value         = '';
        submit.textContent     = 'Сохранить';
        hint.textContent       = '(оставьте пустым, если без изменений)';

        resetAccessCheckboxes();

        if (accessesVal) {
            const selected = accessesVal.split(',').map(v => v.trim()).filter(v => v.length > 0);
            const accessCheckboxes = document.querySelectorAll('#adminModalForm input[name="accesses[]"]');
            accessCheckboxes.forEach(cb => {
                if (selected.includes(cb.value)) {
                    cb.checked = true;
                }
            });
        }

        backdrop.classList.add('modal-open');
    }

    function closeAdminModal() {
        const backdrop = document.getElementById('adminModalBackdrop');
        backdrop.classList.remove('modal-open');
    }
</script>
</body>
</html>
