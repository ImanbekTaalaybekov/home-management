<?php
require __DIR__ . '/../include/auth.php';
require __DIR__ . '/../include/config.php';

$apiBaseUrl     = API_BASE_URL;
$token          = $_SESSION['auth_token'] ?? null;
$storageBaseUrl = 'https://home-folder.wires.kz/storage/';

$statusFilter   = $_GET['status'] ?? '';
$complexFilter  = $_GET['residential_complex_id'] ?? '';
$page           = max(1, (int)($_GET['page'] ?? 1));

$suggestions    = [];
$complexes      = [];
$totalPages     = 1;
$errorMessage   = null;
$successMessage = null;

function apiRequestSuggestions(string $method, string $url, string $token, ?array $data = null): array
{
    $ch = curl_init($url);

    $headers = [
            'Accept: application/json',
            'Authorization: ' . 'Bearer ' . $token,
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

    if ($action === 'set_done') {
        $id = (int)($_POST['suggestion_id'] ?? 0);
        if ($id) {
            [$status, $data] = apiRequestSuggestions(
                    'PATCH',
                    $apiBaseUrl . '/suggestions/' . $id . '/status',
                    $token
            );

            if ($status === 200) {
                $successMessage = $data['message'] ?? 'Статус предложения обновлён';
            } else {
                $errorMessage = $data['message'] ?? 'Ошибка обновления статуса';
            }
        }
    }

    if ($action === 'delete_suggestion') {
        $id = (int)($_POST['suggestion_id'] ?? 0);
        if ($id) {
            [$status, $data] = apiRequestSuggestions(
                    'DELETE',
                    $apiBaseUrl . '/suggestions/' . $id,
                    $token
            );

            if ($status === 200) {
                $successMessage = $data['message'] ?? 'Предложение удалено';
            } else {
                $errorMessage = $data['message'] ?? 'Ошибка удаления предложения';
            }
        }
    }

    $qs = $_SERVER['QUERY_STRING'] ? ('?' . $_SERVER['QUERY_STRING']) : '';
    header('Location: ' . $_SERVER['PHP_SELF'] . $qs);
    exit;
}

$query = $apiBaseUrl . '/suggestions?page=' . $page;

if ($statusFilter !== '') {
    $query .= '&status=' . urlencode($statusFilter);
}
if ($complexFilter !== '') {
    $query .= '&residential_complex_id=' . urlencode($complexFilter);
}

if ($token) {
    [$status, $data] = apiRequestSuggestions('GET', $query, $token);

    if ($status === 200) {
        $suggestions = $data['data'] ?? [];
        $totalPages  = $data['last_page'] ?? 1;
    } else {
        $errorMessage = $data['message'] ?? ('Ошибка загрузки предложений (' . $status . ')');
    }

    [$cStatus, $cData] = apiRequestSuggestions(
            'GET',
            $apiBaseUrl . '/residential-complexes',
            $token
    );

    if ($cStatus === 200) {
        $complexes = $cData['data'] ?? [];
    }
} else {
    $errorMessage = 'Нет токена авторизации';
}

function shortText(string $text, int $limit = 80): string
{
    if (mb_strlen($text, 'UTF-8') <= $limit) {
        return $text;
    }
    return mb_substr($text, 0, $limit, 'UTF-8') . '…';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Предложения</title>
    <link rel="stylesheet" href="/include/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<div class="layout">
    <?php include __DIR__ . '/../include/header.php'; ?>
    <aside class="sidebar"><?php include __DIR__ . '/../include/sidebar.php'; ?></aside>

    <main class="content">
        <h1 class="content__title">Предложения</h1>

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

        <form method="get" class="filter-form">
            <select name="status">
                <option value="">Все статусы</option>
                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>В процессе</option>
                <option value="done" <?= $statusFilter === 'done' ? 'selected' : '' ?>>Готово</option>
            </select>

            <select name="residential_complex_id">
                <option value="">Все ЖК</option>
                <?php foreach ($complexes as $complex): ?>
                    <?php
                    $cxId   = $complex['id']   ?? '';
                    $cxName = $complex['name'] ?? '';
                    ?>
                    <option value="<?= htmlspecialchars((string)$cxId, ENT_QUOTES, 'UTF-8') ?>"
                            <?= (string)$complexFilter === (string)$cxId ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string)$cxName, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button class="filter-button">Применить</button>
        </form>

        <div class="table-wrapper">
            <table class="admins-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Дата</th>
                    <th>Статус</th>
                    <th>ЖК</th>
                    <th>Пользователь</th>
                    <th>Лицевой счёт</th>
                    <th>Текст</th>
                    <th>Фото</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($suggestions)): ?>
                    <tr><td colspan="9">Предложений не найдено</td></tr>
                <?php else: ?>
                    <?php foreach ($suggestions as $s): ?>
                        <?php
                        $id        = $s['id'] ?? '';
                        $createdAt = $s['created_at'] ?? '';
                        $statusVal = $s['status'] ?? '';

                        $user        = $s['user'] ?? [];
                        $userName    = $user['name'] ?? '';
                        $personalAcc = $user['personal_account'] ?? '';
                        $rc          = $user['residential_complex'] ?? $user['residentialComplex'] ?? [];
                        $rcName      = $rc['name'] ?? '';

                        $textFull  = $s['message'] ?? $s['text'] ?? '';
                        $textShort = shortText($textFull ?: '', 70);

                        $photos    = $s['photos'] ?? [];
                        $photoPath = '';
                        $photoUrl  = '';

                        if (!empty($photos) && isset($photos[0]['path'])) {
                            $photoPath = (string)$photos[0]['path'];
                            $photoUrl  = $storageBaseUrl . ltrim($photoPath, '/');
                        }

                        $statusLabel = '—';
                        $statusClass = 'badge-gray';

                        if ($statusVal === 'pending') {
                            $statusLabel = 'В процессе';
                            $statusClass = 'badge-yellow';
                        }

                        if ($statusVal === 'done') {
                            $statusLabel = 'Готово';
                            $statusClass = 'badge-green';
                        }

                        $textFullForJs = str_replace(["\r", "\n"], ["\\r", "\\n"], $textFull);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$createdAt, ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="badge <?= $statusClass ?>">
                                    <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars((string)$rcName, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$userName, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$personalAcc, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($textShort, ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if ($photoUrl): ?>
                                    <a href="<?= htmlspecialchars($photoUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank">
                                        <img src="<?= htmlspecialchars($photoUrl, ENT_QUOTES, 'UTF-8') ?>"
                                             alt="Фото"
                                             class="complaint-thumb">
                                    </a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <button
                                        type="button"
                                        class="btn-small"
                                        onclick="alert('Полный текст:\\n\\n<?= htmlspecialchars($textFullForJs, ENT_QUOTES, 'UTF-8') ?>')"
                                >
                                    Подробнее
                                </button>

                                <?php if ($statusVal !== 'done'): ?>
                                    <form method="post" style="display:inline">
                                        <input type="hidden" name="action" value="set_done">
                                        <input type="hidden" name="suggestion_id" value="<?= htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="btn-small btn-success">Выполнено</button>
                                    </form>
                                <?php endif; ?>

                                <form method="post" style="display:inline" onsubmit="return confirm('Удалить предложение?')">
                                    <input type="hidden" name="action" value="delete_suggestion">
                                    <input type="hidden" name="suggestion_id" value="<?= htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8') ?>">
                                    <button class="btn-small btn-delete">Удалить</button>
                                </form>
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
                    if ($statusFilter !== '') {
                        $link .= '&status=' . urlencode($statusFilter);
                    }
                    if ($complexFilter !== '') {
                        $link .= '&residential_complex_id=' . urlencode($complexFilter);
                    }
                    ?>
                    <a href="<?= $link ?>" class="<?= $i === $page ? 'active-page' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    </main>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar-utilites');
        if (sidebar) {
            sidebar.classList.add('sidebar__group--open');
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('menu_suggestions');
        if (sidebar) {
            sidebar.classList.add('menu-selected-point');
        }
    });
</script>

</body>
</html>