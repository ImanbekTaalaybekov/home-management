<?php
require __DIR__ . '/../include/auth.php';
require __DIR__ . '/../include/config.php';

$apiBaseUrl = API_BASE_URL;
$token = $_SESSION['auth_token'] ?? null;

$search = $_GET['search'] ?? '';
$page   = max(1, (int)($_GET['page'] ?? 1));

$complexes = [];
$errorMessage = null;
$successMessage = null;

function apiRequest($method, $url, $token, $data = null)
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

    if ($action === 'create_complex') {
        $payload = [
            'name'    => $_POST['name'] ?? '',
            'address' => $_POST['address'] ?? '',
        ];

        [$status, $data] = apiRequest('POST', $apiBaseUrl . '/residential-complexes', $token, $payload);
        $successMessage = $status === 201 ? 'Жилой комплекс создан' : ($data['message'] ?? 'Ошибка');
    }

    if ($action === 'update_complex') {
        $id = $_POST['complex_id'] ?? 0;

        $payload = [
            'name'    => $_POST['name'] ?? null,
            'address' => $_POST['address'] ?? null,
        ];

        [$status, $data] = apiRequest('PUT', $apiBaseUrl . '/residential-complexes/' . $id, $token, $payload);
        $successMessage = $status === 200 ? 'Жилой комплекс обновлён' : ($data['message'] ?? 'Ошибка');
    }

    if ($action === 'delete_complex') {
        $id = $_POST['complex_id'] ?? 0;
        [$status, $data] = apiRequest('DELETE', $apiBaseUrl . '/residential-complexes/' . $id, $token);
        $successMessage = $status === 200 ? 'Жилой комплекс удалён' : ($data['message'] ?? 'Ошибка');
    }

    $qs = $_SERVER['QUERY_STRING'] ? ('?' . $_SERVER['QUERY_STRING']) : '';
    header('Location: ' . $_SERVER['PHP_SELF'] . $qs);
    exit;
}

$query = $apiBaseUrl . '/residential-complexes?page=' . $page;
if ($search) $query .= '&search=' . urlencode($search);

[$status, $result] = apiRequest('GET', $query, $token);
$complexes  = $result['data'] ?? [];
$totalPages = $result['last_page'] ?? 1;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Жилые комплексы</title>
    <link rel="stylesheet" href="/include/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<div class="layout">
    <?php include __DIR__ . '/../include/header.php'; ?>
    <aside class="sidebar"><?php include __DIR__ . '/../include/sidebar.php'; ?></aside>

    <main class="content">
        <h1 class="content__title">Жилые комплексы</h1>

        <form class="filter-form" method="get">
            <input type="text" name="search" placeholder="Поиск по названию или адресу"
                   value="<?= htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <button class="filter-button">Найти</button>
            <button type="button" class="button-primary" onclick="openCreateModal()">+ Добавить</button>
        </form>

        <div class="table-wrapper">
            <table class="admins-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Адрес</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($complexes)): ?>
                    <tr><td colspan="4">Нет жилых комплексов</td></tr>
                <?php else: ?>
                    <?php foreach ($complexes as $complex): ?>
                        <tr>
                            <td><?= htmlspecialchars($complex['id'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($complex['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($complex['address'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <button type="button" class="btn-small btn-edit"
                                        onclick="openEditModal(this)"
                                        data-id="<?= htmlspecialchars($complex['id'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-name="<?= htmlspecialchars($complex['name'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-address="<?= htmlspecialchars($complex['address'], ENT_QUOTES, 'UTF-8') ?>">
                                    Редактировать
                                </button>
                                <form method="post" style="display:inline" onsubmit="return confirm('Удалить комплекс?')">
                                    <input type="hidden" name="action" value="delete_complex">
                                    <input type="hidden" name="complex_id" value="<?= htmlspecialchars($complex['id'], ENT_QUOTES, 'UTF-8') ?>">
                                    <button class="btn-small btn-delete">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <?php for($i=1;$i<=$totalPages;$i++): ?>
                <a href="?page=<?= $i ?>" class="<?= $i==$page?'active-page':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>

    </main>
</div>

<div class="modal-backdrop" id="complexModal">
    <div class="modal">
        <div class="modal-header">
            <strong id="complexModalTitle">Жилой комплекс</strong>
            <button onclick="closeComplexModal()">×</button>
        </div>
        <form method="post" id="complexForm" class="login-form">
            <input type="hidden" name="action" id="complexFormAction">
            <input type="hidden" name="complex_id" id="complexId">

            <input type="text" name="name" id="complexName" placeholder="Название" required>
            <input type="text" name="address" id="complexAddress" placeholder="Адрес" required>

            <button class="login-button">Сохранить</button>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('complexModal').classList.add('modal-open');
        document.getElementById('complexModalTitle').textContent = 'Новый жилой комплекс';
        document.getElementById('complexFormAction').value = 'create_complex';
        complexId.value = '';
        complexName.value = '';
        complexAddress.value = '';
    }

    function openEditModal(btn) {
        document.getElementById('complexModal').classList.add('modal-open');
        document.getElementById('complexModalTitle').textContent = 'Редактировать ЖК';
        document.getElementById('complexFormAction').value = 'update_complex';
        complexId.value = btn.dataset.id;
        complexName.value = btn.dataset.name;
        complexAddress.value = btn.dataset.address;
    }

    function closeComplexModal() {
        document.getElementById('complexModal').classList.remove('modal-open');
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar-admin');

        if (sidebar) {
            sidebar.classList.add('sidebar__group--open');
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('menu_complexes');

        if (sidebar) {
            sidebar.classList.add('menu-selected-point');
        }
    });
</script>
<?php include __DIR__ . '/../include/footer.php'; ?>
</body>
</html>