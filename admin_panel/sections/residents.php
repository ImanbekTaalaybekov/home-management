<?php
require __DIR__ . '/../include/auth.php';
require __DIR__ . '/../include/config.php';

$apiBaseUrl = API_BASE_URL;
$token      = $_SESSION['auth_token'] ?? null;

$search        = $_GET['search'] ?? '';
$complexFilter = $_GET['residential_complex_id'] ?? '';
$page          = max(1, (int)($_GET['page'] ?? 1));

$residents      = [];
$errorMessage   = null;
$successMessage = null;
$complexes      = [];

function apiRequest(string $method, string $url, string $token, ?array $data = null): array
{
    $ch = curl_init($url);

    $headers = [
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
    ];

    if ($data !== null) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
    ]);

    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$status, json_decode($response, true)];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token) {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_user') {
        $payload = [
                'login'                   => $_POST['login'] ?? '',
                'name'                    => $_POST['name'] ?? '',
                'phone_number'            => $_POST['phone_number'] ?? null,
                'password'                => $_POST['password'] ?? 'default123',
                'role'                    => $_POST['role'] ?? 'owner',
                'residential_complex_id'  => $_POST['residential_complex_id'] ?? null,
                'personal_account'        => $_POST['personal_account'] ?? null,
                'block_number'            => $_POST['block_number'] ?? null,
                'apartment_number'        => $_POST['apartment_number'] ?? null,
        ];

        [$status, $data] = apiRequest('POST', $apiBaseUrl . '/residents', $token, $payload);
        if ($status === 201) {
            $successMessage = 'Пользователь создан';
        } else {
            $errorMessage = $data['message'] ?? 'Ошибка создания пользователя';
        }
    }

    if ($action === 'update_user') {
        $id = (int)($_POST['user_id'] ?? 0);

        $payload = [
                'login'             => $_POST['login']             ?? null,
                'phone_number'      => $_POST['phone_number']      ?? null,
                'name'              => $_POST['name']              ?? null,
                'role'              => $_POST['role']              ?? null,
                'personal_account'  => $_POST['personal_account']  ?? null,
                'block_number'      => $_POST['block_number']      ?? null,
                'apartment_number'  => $_POST['apartment_number']  ?? null,
        ];

        if (isset($_POST['residential_complex_id']) && $_POST['residential_complex_id'] !== '') {
            $payload['residential_complex_id'] = $_POST['residential_complex_id'];
        }

        [$status, $data] = apiRequest('PUT', $apiBaseUrl . '/residents/' . $id, $token, $payload);
        if ($status === 200) {
            $successMessage = 'Пользователь обновлён';
        } else {
            $errorMessage = $data['message'] ?? 'Ошибка обновления пользователя';
        }
    }

    if ($action === 'delete_user') {
        $id = (int)($_POST['user_id'] ?? 0);
        [$status, $data] = apiRequest('DELETE', $apiBaseUrl . '/residents/' . $id, $token);
        if ($status === 200) {
            $successMessage = 'Пользователь удалён';
        } else {
            $errorMessage = $data['message'] ?? 'Ошибка удаления пользователя';
        }
    }

    if ($action === 'create_tenant') {
        $ownerId = (int)($_POST['owner_id'] ?? 0);

        $payload = [
                'new_login'    => $_POST['new_login']    ?? '',
                'name'         => $_POST['name']         ?? '',
                'phone_number' => $_POST['phone_number'] ?? null,
                'role'         => $_POST['role']         ?? 'tenant',
        ];

        [$status, $data] = apiRequest('POST', $apiBaseUrl . '/residents/' . $ownerId . '/attach-tenant', $token, $payload);
        if ($status === 200) {
            $successMessage = 'Арендатор добавлен';
        } else {
            $errorMessage = $data['message'] ?? 'Ошибка добавления арендатора';
        }
    }
}

if ($token) {
    [$cStatus, $cData] = apiRequest('GET', $apiBaseUrl . '/residential-complexes', $token);
    if ($cStatus === 200) {
        $complexes = $cData['data'] ?? [];
    }
}

$query = $apiBaseUrl . '/residents?page=' . $page;
if ($search)        $query .= '&search=' . urlencode($search);
if ($complexFilter) $query .= '&residential_complex_id=' . urlencode($complexFilter);

[$status, $result] = apiRequest('GET', $query, $token);
$residents  = $result['data']      ?? [];
$totalPages = $result['last_page'] ?? 1;

function roleLabel(?string $code): string
{
    if ($code === 'owner') return 'Владелец';
    if ($code === 'tenant') return 'Арендатор';
    if ($code === 'family') return 'Член семьи';
    return $code ?? '';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Жители</title>
    <link rel="stylesheet" href="/include/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<div class="layout">
    <?php include __DIR__ . '/../include/header.php'; ?>
    <aside class="sidebar"><?php include __DIR__ . '/../include/sidebar.php'; ?></aside>

    <main class="content">
        <h1 class="content__title">Жители</h1>

        <?php if ($successMessage): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($successMessage ?? '', ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($errorMessage ?? '', ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form class="filter-form" method="get">
            <input
                    type="text"
                    name="search"
                    placeholder="Поиск"
                    value="<?= htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >
            <select name="residential_complex_id">
                <option value="">Все ЖК</option>
                <?php foreach ($complexes as $cx): ?>
                    <?php
                    $cxId   = $cx['id'] ?? '';
                    $cxName = $cx['name'] ?? '';
                    ?>
                    <option value="<?= htmlspecialchars((string)$cxId, ENT_QUOTES, 'UTF-8') ?>"
                            <?= (string)$complexFilter === (string)$cxId ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cxName, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="filter-button">Найти</button>
            <button type="button" class="button-primary" onclick="openCreateModal()">
                + Добавить
            </button>
        </form>

        <div class="table-wrapper">
            <table class="admins-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Логин</th>
                    <th>Телефон</th>
                    <th>Роль</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($residents)): ?>
                    <tr>
                        <td colspan="6">Жители не найдены</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($residents as $user): ?>
                        <?php
                        $id              = $user['id']                     ?? '';
                        $name            = $user['name']                   ?? '';
                        $login           = $user['login']                  ?? '';
                        $phone           = $user['phone_number']           ?? '';
                        $role            = $user['role']                   ?? '';
                        $rcId            = $user['residential_complex_id'] ?? '';
                        $personalAccount = $user['personal_account']       ?? '';
                        $blockNumber     = $user['block_number']           ?? '';
                        $apartmentNumber = $user['apartment_number']       ?? '';
                        $roleRu          = roleLabel($role);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($login, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($roleRu, ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <div class="admins-actions">
                                    <button
                                            type="button"
                                            class="btn-small btn-edit"
                                            onclick="openEditModal(this)"
                                            data-id="<?= htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8') ?>"
                                            data-name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                                            data-login="<?= htmlspecialchars($login, ENT_QUOTES, 'UTF-8') ?>"
                                            data-phone="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>"
                                            data-role="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>"
                                            data-rc-id="<?= htmlspecialchars((string)$rcId, ENT_QUOTES, 'UTF-8') ?>"
                                            data-personal-account="<?= htmlspecialchars($personalAccount, ENT_QUOTES, 'UTF-8') ?>"
                                            data-block-number="<?= htmlspecialchars($blockNumber, ENT_QUOTES, 'UTF-8') ?>"
                                            data-apartment-number="<?= htmlspecialchars($apartmentNumber, ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                        Редактировать
                                    </button>

                                    <button
                                            type="button"
                                            class="btn-small"
                                            onclick="openTenantModal(this)"
                                            data-owner-id="<?= htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8') ?>"
                                            data-owner-login="<?= htmlspecialchars($login, ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                        + Арендатор
                                    </button>

                                    <form method="post" style="display:inline"
                                          onsubmit="return confirm('Удалить пользователя?');">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id"
                                               value="<?= htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="btn-small btn-delete">Удалить</button>
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
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php
                    $link = '?page=' . $i;
                    if ($search !== '') {
                        $link .= '&search=' . urlencode($search);
                    }
                    if ($complexFilter !== '') {
                        $link .= '&residential_complex_id=' . urlencode($complexFilter);
                    }
                    ?>
                    <a href="<?= $link ?>" class="<?= $i === $page ? 'active-page' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    </main>
</div>

<div class="modal-backdrop" id="residentModal">
    <div class="modal">
        <div class="modal-header">
            <strong id="modalTitle">Пользователь</strong>
            <button type="button" class="modal-close" onclick="closeResidentModal()">×</button>
        </div>
        <form method="post" id="residentForm" class="login-form">
            <input type="hidden" name="action" id="formAction">
            <input type="hidden" name="user_id" id="userId">

            <div class="login-group">
                <label>Имя</label>
                <input type="text" name="name" id="name" required>
            </div>

            <div class="login-group">
                <label>Логин</label>
                <input type="text" name="login" id="login">
            </div>

            <div class="login-group">
                <label>Пароль (при создании)</label>
                <input type="password" name="password" id="password">
            </div>

            <div class="login-group">
                <label>Телефон</label>
                <input type="text" name="phone_number" id="phone">
            </div>

            <div class="login-group">
                <label>Лицевой счёт</label>
                <input type="text" name="personal_account" id="personal_account">
            </div>

            <div class="login-group">
                <label>Номер блока</label>
                <input type="text" name="block_number" id="block_number">
            </div>

            <div class="login-group">
                <label>Номер квартиры</label>
                <input type="text" name="apartment_number" id="apartment_number">
            </div>

            <div class="login-group">
                <label>Роль</label>
                <select name="role" id="role">
                    <option value="owner">Владелец</option>
                    <option value="tenant">Арендатор</option>
                    <option value="family">Член семьи</option>
                </select>
            </div>

            <div class="login-group">
                <label>Жилой комплекс</label>
                <select name="residential_complex_id" id="rcId" required>
                    <option value="">Выберите ЖК</option>
                    <?php foreach ($complexes as $cx): ?>
                        <?php
                        $cxId   = $cx['id'] ?? '';
                        $cxName = $cx['name'] ?? '';
                        ?>
                        <option value="<?= htmlspecialchars((string)$cxId, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($cxName, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeResidentModal()">Отмена</button>
                <button class="login-button" id="residentSubmit">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="tenantModal">
    <div class="modal">
        <div class="modal-header">
            <strong>Добавить арендатора</strong>
            <button type="button" class="modal-close" onclick="closeTenantModal()">×</button>
        </div>
        <form method="post" id="tenantForm" class="login-form">
            <input type="hidden" name="action" value="create_tenant">
            <input type="hidden" name="owner_id" id="tenantOwnerId">

            <div class="login-group">
                <label>Логин владельца</label>
                <input type="text" id="tenantOwnerLogin" disabled>
            </div>

            <div class="login-group">
                <label>Суффикс логина арендатора</label>
                <input type="text" name="new_login" id="tenantNewLogin" placeholder="например: tenant1" required>
            </div>

            <div class="login-group">
                <label>Имя арендатора</label>
                <input type="text" name="name" id="tenantName" required>
            </div>

            <div class="login-group">
                <label>Телефон арендатора</label>
                <input type="text" name="phone_number" id="tenantPhone">
            </div>

            <div class="login-group">
                <label>Роль</label>
                <select name="role" id="tenantRole">
                    <option value="tenant">Арендатор</option>
                    <option value="family">Член семьи</option>
                </select>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeTenantModal()">Отмена</button>
                <button class="login-button">Создать арендатора</button>
            </div>
        </form>
    </div>
</div>

<script src="/include/scripts.js"></script>
<script>
    function openCreateModal() {
        const modal = document.getElementById('residentModal');
        document.getElementById('modalTitle').textContent = 'Новый пользователь';
        document.getElementById('formAction').value = 'create_user';
        document.getElementById('userId').value = '';
        document.getElementById('name').value = '';
        document.getElementById('login').value = '';
        document.getElementById('password').value = '';
        document.getElementById('phone').value = '';
        document.getElementById('personal_account').value = '';
        document.getElementById('block_number').value = '';
        document.getElementById('apartment_number').value = '';
        document.getElementById('role').value = 'owner';
        document.getElementById('rcId').value = '';
        document.getElementById('residentSubmit').textContent = 'Создать';
        modal.classList.add('modal-open');
    }

    function openEditModal(btn) {
        const modal = document.getElementById('residentModal');
        document.getElementById('modalTitle').textContent = 'Редактировать пользователя';
        document.getElementById('formAction').value = 'update_user';
        document.getElementById('userId').value = btn.dataset.id || '';
        document.getElementById('name').value = btn.dataset.name || '';
        document.getElementById('login').value = btn.dataset.login || '';
        document.getElementById('password').value = '';
        document.getElementById('phone').value = btn.dataset.phone || '';
        document.getElementById('personal_account').value = btn.dataset.personalAccount || '';
        document.getElementById('block_number').value = btn.dataset.blockNumber || '';
        document.getElementById('apartment_number').value = btn.dataset.apartmentNumber || '';
        document.getElementById('role').value = btn.dataset.role || 'owner';
        document.getElementById('rcId').value = btn.dataset.rcId || '';
        document.getElementById('residentSubmit').textContent = 'Сохранить';
        modal.classList.add('modal-open');
    }

    function closeResidentModal() {
        document.getElementById('residentModal').classList.remove('modal-open');
    }

    function openTenantModal(btn) {
        const modal = document.getElementById('tenantModal');
        document.getElementById('tenantOwnerId').value = btn.dataset.ownerId || '';
        document.getElementById('tenantOwnerLogin').value = btn.dataset.ownerLogin || '';
        document.getElementById('tenantNewLogin').value = '';
        document.getElementById('tenantName').value = '';
        document.getElementById('tenantPhone').value = '';
        document.getElementById('tenantRole').value = 'tenant';
        modal.classList.add('modal-open');
    }

    function closeTenantModal() {
        document.getElementById('tenantModal').classList.remove('modal-open');
    }
</script>
</body>
</html>
