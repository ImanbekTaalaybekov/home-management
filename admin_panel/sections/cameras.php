<?php
require __DIR__ . '/../include/auth.php';
require __DIR__ . '/../include/config.php';

$apiBaseUrl = API_BASE_URL;
$token      = $_SESSION['auth_token'] ?? null;

$currentResidentialComplexId = isset($_GET['residential_complex_id']) ? (int)$_GET['residential_complex_id'] : 0;
$page                        = max(1, (int)($_GET['page'] ?? 1));

$cameras        = [];
$complexes      = [];
$meta           = null;
$errorMessage   = null;
$successMessage = null;

function apiRequest(string $method, string $url, string $token, ?array $data = null): array
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

    if ($action === 'create_camera') {
        $payload = [
                'residential_complex_id' => (int)($_POST['residential_complex_id'] ?? 0),
                'type'                   => $_POST['type'] ?? null,
                'name'                   => $_POST['name'] ?? null,
                'link'                   => $_POST['link'] ?? null,
        ];

        [$status, $data] = apiRequest('POST', $apiBaseUrl . '/cameras', $token, $payload);
        if ($status === 201) {
            $successMessage = 'Камера создана';
        } else {
            $errorMessage = $data['message'] ?? 'Ошибка создания камеры';
        }
    }

    if ($action === 'update_camera') {
        $id = (int)($_POST['camera_id'] ?? 0);

        if ($id) {
            $payload = [
                    'residential_complex_id' => (int)($_POST['residential_complex_id'] ?? 0),
                    'type'                   => $_POST['type'] ?? null,
                    'name'                   => $_POST['name'] ?? null,
                    'link'                   => $_POST['link'] ?? null,
            ];

            [$status, $data] = apiRequest('POST', $apiBaseUrl . '/cameras/' . $id, $token, $payload);

            if ($status === 200) {
                $successMessage = 'Камера обновлена';
            } else {
                $errorMessage = $data['message'] ?? 'Ошибка обновления камеры';
            }
        }
    }

    if ($action === 'delete_camera') {
        $id = (int)($_POST['camera_id'] ?? 0);

        if ($id) {
            [$status, $data] = apiRequest('DELETE', $apiBaseUrl . '/cameras/' . $id, $token);
            if ($status === 200) {
                $successMessage = $data['message'] ?? 'Камера удалена';
            } else {
                $errorMessage = $data['message'] ?? 'Ошибка удаления камеры';
            }
        }
    }

    $qs = $_SERVER['QUERY_STRING'] ? ('?' . $_SERVER['QUERY_STRING']) : '';
    header('Location: ' . $_SERVER['PHP_SELF'] . $qs);
    exit;
}

if ($token) {
    $complexQuery = $apiBaseUrl . '/residential-complexes?page=1&per_page=1000';
    [$statusComplex, $resultComplex] = apiRequest('GET', $complexQuery, $token);
    if ($statusComplex === 200) {
        $complexes = $resultComplex['data'] ?? $resultComplex ?? [];
    }

    $queryParams = [];
    if ($currentResidentialComplexId > 0) {
        $queryParams['residential_complex_id'] = $currentResidentialComplexId;
    }
    if ($page > 1) {
        $queryParams['page'] = $page;
    }

    $cameraQuery = $apiBaseUrl . '/cameras';
    if (!empty($queryParams)) {
        $cameraQuery .= '?' . http_build_query($queryParams);
    }

    [$statusCam, $resultCam] = apiRequest('GET', $cameraQuery, $token);
    if ($statusCam === 200) {
        if (isset($resultCam['data'])) {
            $cameras = $resultCam['data'];
            $meta    = $resultCam['meta'] ?? null;
        } else {
            $cameras = $resultCam;
        }
    } else {
        $errorMessage = $resultCam['message'] ?? 'Ошибка получения списка камер';
    }
} else {
    $errorMessage = 'Нет токена авторизации';
}

function complexNameById(array $complexes, $id): string
{
    foreach ($complexes as $c) {
        if ((int)($c['id'] ?? 0) === (int)$id) {
            return (string)($c['name'] ?? '');
        }
    }
    return '';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Камеры видеонаблюдения</title>
    <link rel="stylesheet" href="/include/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<div class="layout">
    <?php include __DIR__ . '/../include/header.php'; ?>
    <aside class="sidebar"><?php include __DIR__ . '/../include/sidebar.php'; ?></aside>

    <main class="content">
        <h1 class="content__title">Камеры видеонаблюдения</h1>

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

        <section class="card card--main">
            <div class="card-header card-header--space-between">
                <div>
                    <h2 class="card-title">Список камер</h2>
                    <div class="card-subtitle">Камеры по жилым комплексам</div>
                </div>

                <div class="card-header-actions">
                    <form method="get" class="filter-inline">
                        <select name="residential_complex_id" class="select-small">
                            <option value="0">Все ЖК</option>
                            <?php foreach ($complexes as $complex): ?>
                                <?php
                                $cid   = (int)($complex['id'] ?? 0);
                                $cname = $complex['name'] ?? '';
                                ?>
                                <option value="<?= $cid ?>" <?= $cid === $currentResidentialComplexId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cname, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="button-primary button-xs">Фильтр</button>
                        <a href="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') ?>" class="button-primary button-xs button-danger ">Сброс</a>

                        <button type="button" class="button-primary button-xs btn-success" onclick="openCameraCreateModal()">
                            + Камера
                        </button>
                    </form>
                </div>
            </div>

            <?php if (empty($cameras)): ?>
                <p>Камеры пока не добавлены.</p>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="admins-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Жилой комплекс</th>
                            <th>Тип</th>
                            <th>Название</th>
                            <th>Ссылка</th>
                            <th>Создана</th>
                            <th>Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($cameras as $camera): ?>
                            <?php
                            $cId      = $camera['id'] ?? '';
                            $rcId     = $camera['residential_complex_id'] ?? ($camera['residential_complex']['id'] ?? null);
                            $rcName   = $camera['residential_complex']['name'] ?? complexNameById($complexes, $rcId);
                            $type     = $camera['type'] ?? '';
                            $name     = $camera['name'] ?? '';
                            $link     = $camera['link'] ?? '';
                            $created  = $camera['created_at'] ?? '';
                            ?>
                            <tr>
                                <td><?= htmlspecialchars((string)$cId, ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if ($rcId): ?>
                                        <?= htmlspecialchars((string)$rcId, ENT_QUOTES, 'UTF-8') ?>
                                        <?php if ($rcName): ?>
                                            <br><small><?= htmlspecialchars($rcName, ENT_QUOTES, 'UTF-8') ?></small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if ($link): ?>
                                        <a href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>" target="_blank">Открыть</a>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($created, ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <div class="admins-actions">
                                        <button
                                                type="button"
                                                class="btn-small btn-edit"
                                                onclick="openCameraEditModal(this)"
                                                data-id="<?= htmlspecialchars((string)$cId, ENT_QUOTES, 'UTF-8') ?>"
                                                data-residential-complex-id="<?= htmlspecialchars((string)$rcId, ENT_QUOTES, 'UTF-8') ?>"
                                                data-type="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>"
                                                data-name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                                                data-link="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>"
                                        >Редактировать</button>

                                        <form method="post" style="display:inline" onsubmit="return confirm('Удалить камеру?');">
                                            <input type="hidden" name="action" value="delete_camera">
                                            <input type="hidden" name="camera_id" value="<?= htmlspecialchars((string)$cId, ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" class="btn-small btn-delete">Удалить</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($meta && isset($meta['current_page'], $meta['last_page']) && $meta['last_page'] > 1): ?>
                    <div class="pagination">
                        <?php if ($meta['current_page'] > 1): ?>
                            <?php
                            $prevParams = $_GET;
                            $prevParams['page'] = $meta['current_page'] - 1;
                            $prevUrl = $_SERVER['PHP_SELF'] . '?' . http_build_query($prevParams);
                            ?>
                            <a href="<?= htmlspecialchars($prevUrl, ENT_QUOTES, 'UTF-8') ?>" class="pagination-link">« Назад</a>
                        <?php endif; ?>

                        <span class="pagination-info">
                            Страница <?= (int)$meta['current_page'] ?> из <?= (int)$meta['last_page'] ?>
                        </span>

                        <?php if ($meta['current_page'] < $meta['last_page']): ?>
                            <?php
                            $nextParams = $_GET;
                            $nextParams['page'] = $meta['current_page'] + 1;
                            $nextUrl = $_SERVER['PHP_SELF'] . '?' . http_build_query($nextParams);
                            ?>
                            <a href="<?= htmlspecialchars($nextUrl, ENT_QUOTES, 'UTF-8') ?>" class="pagination-link">Вперёд »</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>
</div>

<div class="modal-backdrop" id="cameraModal">
    <div class="modal modal--lg">
        <div class="modal-header">
            <strong id="cameraModalTitle">Камера</strong>
            <button type="button" class="modal-close" onclick="closeCameraModal()">×</button>
        </div>
        <form method="post" class="login-form">
            <input type="hidden" name="action" id="cameraFormAction">
            <input type="hidden" name="camera_id" id="cameraId">

            <div class="login-group">
                <label>Жилой комплекс</label>
                <select name="residential_complex_id" id="cameraResidentialComplexId" required>
                    <option value="">Выберите ЖК</option>
                    <?php foreach ($complexes as $complex): ?>
                        <?php
                        $cid   = (int)($complex['id'] ?? 0);
                        $cname = $complex['name'] ?? '';
                        ?>
                        <option value="<?= $cid ?>">
                            <?= htmlspecialchars($cname, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="login-group">
                <label>Тип камеры</label>
                <select name="type" id="cameraType" required>
                    <option value="">Выберите тип</option>
                    <option value="hikvision">Hikvision</option>
                    <option value="dahua">Dahua</option>
                </select>
            </div>

            <div class="login-group">
                <label>Название</label>
                <input type="text" name="name" id="cameraName" required>
            </div>

            <div class="login-group">
                <label>Ссылка</label>
                <input type="text" name="link" id="cameraLink">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeCameraModal()">Отмена</button>
                <button type="submit" class="login-button">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var sidebarGroup = document.getElementById('sidebar-information');
        if (sidebarGroup) {
            sidebarGroup.classList.add('sidebar__group--open');
        }

        var menuItem = document.getElementById('menu_cameras');
        if (menuItem) {
            menuItem.classList.add('menu-selected-point');
        }
    });

    function cameraEls() {
        return {
            modal: document.getElementById('cameraModal'),
            action: document.getElementById('cameraFormAction'),
            id: document.getElementById('cameraId'),
            rcSelect: document.getElementById('cameraResidentialComplexId'),
            type: document.getElementById('cameraType'),
            name: document.getElementById('cameraName'),
            link: document.getElementById('cameraLink'),
            title: document.getElementById('cameraModalTitle')
        };
    }

    function openCameraCreateModal() {
        var e = cameraEls();
        e.modal.classList.add('modal-open');
        e.title.textContent = 'Новая камера';
        e.action.value = 'create_camera';
        e.id.value = '';
        e.rcSelect.value = '';
        e.type.value = '';
        e.name.value = '';
        e.link.value = '';
    }

    function openCameraEditModal(btn) {
        var e = cameraEls();
        e.modal.classList.add('modal-open');
        e.title.textContent = 'Редактировать камеру';
        e.action.value = 'update_camera';
        e.id.value = btn.dataset.id || '';
        e.rcSelect.value = btn.dataset.residentialComplexId || '';
        e.type.value = btn.dataset.type || '';
        e.name.value = btn.dataset.name || '';
        e.link.value = btn.dataset.link || '';
    }

    function closeCameraModal() {
        cameraEls().modal.classList.remove('modal-open');
    }
</script>
</body>
</html>
