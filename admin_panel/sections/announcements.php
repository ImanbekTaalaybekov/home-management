<?php
require __DIR__ . '/../include/auth.php';
require __DIR__ . '/../include/config.php';

$apiBaseUrl = API_BASE_URL;
$token      = $_SESSION['auth_token'] ?? null;

$page      = max(1, (int)($_GET['page'] ?? 1));
$search    = $_GET['search'] ?? '';
$complexId = $_GET['residential_complex_id'] ?? '';

$announcements  = [];
$totalPages     = 1;
$errorMessage   = null;
$successMessage = null;
$complexes      = [];

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = $_POST['id'] ?? 0;

    [$status, $data] = apiRequest('DELETE', $apiBaseUrl . '/announcements/' . $id, $token);

    if ($status === 200) {
        $successMessage = $data['message'] ?? 'Объявление удалено';
    } else {
        $errorMessage = $data['message'] ?? 'Ошибка удаления';
    }
}

[$cStatus, $cData] = apiRequest('GET', $apiBaseUrl . '/residential-complexes', $token);
$complexes = $cData['data'] ?? [];

$query = $apiBaseUrl . '/announcements?page=' . $page;
if ($search) $query .= '&search=' . urlencode($search);
if ($complexId) $query .= '&residential_complex_id=' . urlencode($complexId);

[$status, $data] = apiRequest('GET', $query, $token);
if ($status === 200) {
    $announcements = $data['data'] ?? [];
    $totalPages = $data['last_page'] ?? 1;
} else {
    $errorMessage = $data['message'] ?? 'Ошибка загрузки объявлений';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Объявления</title>
    <link rel="stylesheet" href="/include/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<div class="layout">
    <?php include __DIR__ . '/../include/header.php'; ?>
    <aside class="sidebar"><?php include __DIR__ . '/../include/sidebar.php'; ?></aside>

    <main class="content">
        <h1 class="content__title">Объявления</h1>

        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <form class="filter-form" method="get">
            <input type="text" name="search" placeholder="Поиск по заголовку / тексту" value="<?= htmlspecialchars($search) ?>">
            <select name="residential_complex_id">
                <option value="">Все ЖК</option>
                <?php foreach ($complexes as $cx): ?>
                    <option value="<?= $cx['id'] ?>" <?= (string)$complexId === (string)$cx['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cx['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="filter-button">Фильтр</button>
        </form>

        <div class="table-wrapper">
            <table class="admins-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Заголовок</th>
                    <th>ЖК</th>
                    <th>Автор</th>
                    <th>Дата</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($announcements as $item): ?>
                    <tr>
                        <td><?= $item['id'] ?></td>
                        <td><?= htmlspecialchars($item['title'] ?? '') ?></td>
                        <td>
                            <?= $item['residential_complex']['name'] ?? 'Для всех ЖК' ?>
                        </td>
                        <td><?= $item['created_by']['name'] ?? '-' ?></td>
                        <td><?= $item['created_at'] ?></td>
                        <td>
                            <button class="btn-small" onclick='showAnnouncement(<?= json_encode($item, JSON_UNESCAPED_UNICODE) ?>)'>Просмотр</button>
                            <form method="post" style="display:inline" onsubmit="return confirm('Удалить объявление?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <button class="btn-small btn-delete">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active-page' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </main>
</div>

<div class="modal-backdrop" id="viewModal">
    <div class="modal modal--lg">
        <div class="modal-header">
            <strong id="viewTitle"></strong>
            <button onclick="closeViewModal()">×</button>
        </div>
        <div class="modal-body">
            <p id="viewContent"></p>
            <div id="viewPhotos"></div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>

<script>
    function showAnnouncement(data) {
        document.getElementById('viewModal').classList.add('modal-open');
        document.getElementById('viewTitle').innerText = data.title || '';
        document.getElementById('viewContent').innerText = data.content || '';

        const container = document.getElementById('viewPhotos');
        container.innerHTML = '';

        if (Array.isArray(data.photos)) {
            data.photos.forEach(photo => {
                const img = document.createElement('img');
                img.src = '/storage/' + photo.path;
                img.style.maxWidth = '150px';
                img.style.margin = '5px';
                container.appendChild(img);
            });
        }
    }

    function closeViewModal() {
        document.getElementById('viewModal').classList.remove('modal-open');
    }
</script>
</body>
</html>
