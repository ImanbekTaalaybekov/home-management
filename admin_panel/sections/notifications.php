<?php
require __DIR__ . '/../include/auth.php';
require __DIR__ . '/../include/config.php';

$apiBaseUrl = API_BASE_URL;
$token      = $_SESSION['auth_token'] ?? null;

$search   = $_GET['search']   ?? '';
$type     = $_GET['type']     ?? '';
$category = $_GET['category'] ?? '';
$page     = max(1, (int)($_GET['page'] ?? 1));

$notifications  = [];
$errorMessage   = null;
$successMessage = null;
$totalPages     = 1;

function apiRequest(string $method, string $url, string $token, ?array $data = null, bool $isMultipart = false): array
{
    $ch = curl_init($url);

    $headers = [
        'Accept: application/json',
        'Authorization: ' . 'Bearer ' . $token,
    ];

    if ($data !== null) {
        if ($isMultipart) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        }
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

    if ($action === 'create_notification') {
        $payload = [
            'type'                  => $_POST['type'] ?? '',
            'category'              => $_POST['category'] ?? '',
            'title'                 => $_POST['title'] ?? '',
            'message'               => $_POST['message'] ?? '',
            'personal_account'      => $_POST['personal_account'] ?? null,
            'residential_complex_id'=> $_POST['residential_complex_id'] ?? null,
        ];

        $multipart = $payload;

        if (!empty($_FILES['photos']['name'][0])) {
            foreach ($_FILES['photos']['tmp_name'] as $i => $tmp) {
                if (!$tmp) continue;
                $multipart["photos[$i]"] = new CURLFile(
                    $tmp,
                    $_FILES['photos']['type'][$i] ?? 'image/jpeg',
                    $_FILES['photos']['name'][$i] ?? ('photo_' . $i)
                );
            }
        }

        if (!empty($_FILES['document']['tmp_name'])) {
            $multipart['document'] = new CURLFile(
                $_FILES['document']['tmp_name'],
                $_FILES['document']['type'] ?? 'application/pdf',
                $_FILES['document']['name'] ?? 'document.pdf'
            );
        }

        [$status, $data] = apiRequest('POST', $apiBaseUrl . '/notifications', $token, $multipart, true);
        if ($status === 201) {
            $successMessage = $data['message'] ?? 'Уведомление отправлено';
        } else {
            $errorMessage = $data['message'] ?? 'Ошибка при создании уведомления';
        }
    }

    if ($action === 'update_notification') {
        $id = (int)($_POST['notification_id'] ?? 0);

        $payload = [
            'type'                  => $_POST['type'] ?? null,
            'category'              => $_POST['category'] ?? null,
            'title'                 => $_POST['title'] ?? null,
            'message'               => $_POST['message'] ?? null,
            'personal_account'      => $_POST['personal_account'] ?? null,
            'residential_complex_id'=> $_POST['residential_complex_id'] ?? null,
        ];

        foreach ($payload as $k => $v) {
            if ($v === '' || $v === null) {
                unset($payload[$k]);
            }
        }

        $multipart = $payload;

        if (!empty($_FILES['photos']['name'][0])) {
            foreach ($_FILES['photos']['tmp_name'] as $i => $tmp) {
                if (!$tmp) continue;
                $multipart["photos[$i]"] = new CURLFile(
                    $tmp,
                    $_FILES['photos']['type'][$i] ?? 'image/jpeg',
                    $_FILES['photos']['name'][$i] ?? ('photo_' . $i)
                );
            }
        }

        if (!empty($_FILES['document']['tmp_name'])) {
            $multipart['document'] = new CURLFile(
                $_FILES['document']['tmp_name'],
                $_FILES['document']['type'] ?? 'application/pdf',
                $_FILES['document']['name'] ?? 'document.pdf'
            );
        }

        [$status, $data] = apiRequest('POST', $apiBaseUrl . '/notifications/' . $id . '?_method=PUT', $token, $multipart, true);
        if ($status === 200) {
            $successMessage = $data['message'] ?? 'Уведомление обновлено';
        } else {
            $errorMessage = $data['message'] ?? 'Ошибка при обновлении уведомления';
        }
    }

    if ($action === 'delete_notification') {
        $id = (int)($_POST['notification_id'] ?? 0);

        [$status, $data] = apiRequest('DELETE', $apiBaseUrl . '/notifications/' . $id, $token);
        if ($status === 200) {
            $successMessage = $data['message'] ?? 'Уведомление удалено';
        } else {
            $errorMessage = $data['message'] ?? 'Ошибка при удалении уведомления';
        }
    }
}

$query = $apiBaseUrl . '/notifications?page=' . $page;
if ($search !== '')   $query .= '&search=' . urlencode($search);
if ($type !== '')     $query .= '&type=' . urlencode($type);
if ($category !== '') $query .= '&category=' . urlencode($category);

[$status, $result] = apiRequest('GET', $query, $token);

if ($status === 200) {
    $notifications = $result['data'] ?? [];
    $totalPages    = $result['last_page'] ?? 1;
} else {
    $errorMessage = $result['message'] ?? ('Ошибка загрузки уведомлений (' . $status . ')');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Уведомления</title>
    <link rel="stylesheet" href="/include/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<div class="layout">
    <?php include __DIR__ . '/../include/header.php'; ?>
    <aside class="sidebar"><?php include __DIR__ . '/../include/sidebar.php'; ?></aside>

    <main class="content">
        <h1 class="content__title">Уведомления</h1>

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
            <input
                type="text"
                name="search"
                placeholder="Поиск по заголовку, тексту, ЛС"
                value="<?= htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >

            <select name="type">
                <option value="">Тип</option>
                <option value="global"  <?= $type === 'global'  ? 'selected' : '' ?>>Общее</option>
                <option value="complex" <?= $type === 'complex' ? 'selected' : '' ?>>ЖК</option>
                <option value="personal"<?= $type === 'personal'? 'selected' : '' ?>>Персональное</option>
            </select>

            <select name="category">
                <option value="">Категория</option>
                <option value="technical" <?= $category === 'technical' ? 'selected' : '' ?>>Техническое</option>
                <option value="common"    <?= $category === 'common'    ? 'selected' : '' ?>>Общее</option>
            </select>

            <button class="filter-button">Фильтр</button>

            <button type="button" class="button-primary" onclick="openNotificationModalCreate()">
                + Новое уведомление
            </button>
        </form>

        <div class="table-wrapper">
            <table class="admins-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Заголовок</th>
                    <th>Тип</th>
                    <th>Категория</th>
                    <th>Цель</th>
                    <th>Дата</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($notifications)): ?>
                    <tr>
                        <td colspan="7">Уведомления не найдены</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($notifications as $n): ?>
                        <?php
                        $id          = $n['id'] ?? '';
                        $title       = $n['title'] ?? '';
                        $nType       = $n['type'] ?? '';
                        $nCategory   = $n['category'] ?? '';
                        $createdAt   = $n['created_at'] ?? '';
                        $personalAcc = $n['personal_account'] ?? '';
                        $rcId        = $n['residential_complex_id'] ?? '';
                        $message     = $n['message'] ?? '';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($nType, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($nCategory, ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if ($nType === 'complex' && $rcId): ?>
                                    ЖК ID: <?= htmlspecialchars($rcId, ENT_QUOTES, 'UTF-8') ?>
                                <?php elseif ($nType === 'personal' && $personalAcc): ?>
                                    ЛС: <?= htmlspecialchars($personalAcc, ENT_QUOTES, 'UTF-8') ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($createdAt, ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <div class="admins-actions">
                                    <button
                                        type="button"
                                        class="btn-small btn-edit"
                                        onclick="openNotificationModalEdit(this)"
                                        data-id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>"
                                        data-title="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>"
                                        data-type="<?= htmlspecialchars($nType, ENT_QUOTES, 'UTF-8') ?>"
                                        data-category="<?= htmlspecialchars($nCategory, ENT_QUOTES, 'UTF-8') ?>"
                                        data-message="<?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>"
                                        data-personal-account="<?= htmlspecialchars($personalAcc, ENT_QUOTES, 'UTF-8') ?>"
                                        data-residential-complex-id="<?= htmlspecialchars($rcId, ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                        Редактировать
                                    </button>

                                    <form method="post" style="display:inline"
                                          onsubmit="return confirm('Удалить уведомление?');">
                                        <input type="hidden" name="action" value="delete_notification">
                                        <input type="hidden" name="notification_id"
                                               value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
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
                    if ($search !== '')   $link .= '&search=' . urlencode($search);
                    if ($type !== '')     $link .= '&type=' . urlencode($type);
                    if ($category !== '') $link .= '&category=' . urlencode($category);
                    ?>
                    <a href="<?= $link ?>" class="<?= $i === $page ? 'active-page' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    </main>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>

<script src="/include/scripts.js"></script>
<script>
    function getNotifElements() {
        return {
            modal: document.getElementById('notificationModal'),
            form: document.getElementById('notificationForm'),
            action: document.getElementById('notifAction'),
            id: document.getElementById('notifId'),
            type: document.getElementById('notifType'),
            category: document.getElementById('notifCategory'),
            title: document.getElementById('notifTitleInput'),
            message: document.getElementById('notifMessage'),
            pa: document.getElementById('notifPersonalAccount'),
            rc: document.getElementById('notifRcId'),
            groupPa: document.getElementById('fieldPersonalAccount'),
            groupRc: document.getElementById('fieldRcId'),
            headerTitle: document.getElementById('notifModalHeaderTitle')
        };
    }

    function updateTypeVisibility() {
        const el = getNotifElements();
        const t = el.type.value;

        if (t === 'complex') {
            el.groupRc.style.display = 'block';
            el.groupPa.style.display = 'none';
        } else if (t === 'personal') {
            el.groupRc.style.display = 'none';
            el.groupPa.style.display = 'block';
        } else {
            el.groupRc.style.display = 'none';
            el.groupPa.style.display = 'none';
        }
    }

    function openNotificationModalCreate() {
        const el = getNotifElements();
        el.modal.classList.add('modal-open');
        el.headerTitle.textContent = 'Новое уведомление';
        el.action.value = 'create_notification';
        el.id.value = '';

        el.type.value = 'global';
        el.category.value = 'common';
        el.title.value = '';
        el.message.value = '';
        el.pa.value = '';
        el.rc.value = '';

        updateTypeVisibility();
    }

    function openNotificationModalEdit(btn) {
        const el = getNotifElements();
        el.modal.classList.add('modal-open');
        el.headerTitle.textContent = 'Редактировать уведомление';
        el.action.value = 'update_notification';

        el.id.value       = btn.dataset.id || '';
        el.type.value     = btn.dataset.type || 'global';
        el.category.value = btn.dataset.category || 'common';
        el.title.value    = btn.dataset.title || '';
        el.message.value  = btn.dataset.message || '';
        el.pa.value       = btn.dataset.personalAccount || '';
        el.rc.value       = btn.dataset.residentialComplexId || '';

        updateTypeVisibility();
    }

    function closeNotificationModal() {
        const el = getNotifElements();
        el.modal.classList.remove('modal-open');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const el = getNotifElements();
        el.type.addEventListener('change', updateTypeVisibility);
        updateTypeVisibility();
    });
</script>
<script src="/include/scripts.js"></script>

<div class="modal-backdrop" id="notificationModal">
    <div class="modal">
        <div class="modal-header">
            <strong id="notifModalHeaderTitle">Новое уведомление</strong>
            <button type="button" class="modal-close" onclick="closeNotificationModal()">×</button>
        </div>

        <form method="post" enctype="multipart/form-data" id="notificationForm" class="login-form">
            <input type="hidden" name="action" id="notifAction" value="create_notification">
            <input type="hidden" name="notification_id" id="notifId">

            <div class="login-group">
                <label>Тип</label>
                <select name="type" id="notifType" required>
                    <option value="global">Общее</option>
                    <option value="complex">ЖК</option>
                    <option value="personal">Персональное</option>
                </select>
            </div>

            <div class="login-group">
                <label>Категория</label>
                <select name="category" id="notifCategory" required>
                    <option value="technical">Техническое</option>
                    <option value="common">Общее</option>
                </select>
            </div>

            <div class="login-group" id="fieldRcId" style="display:none">
                <label>ID жилого комплекса</label>
                <input type="number" name="residential_complex_id" id="notifRcId" placeholder="ID ЖК">
            </div>

            <div class="login-group" id="fieldPersonalAccount" style="display:none">
                <label>Лицевой счёт</label>
                <input type="text" name="personal_account" id="notifPersonalAccount" placeholder="Лицевой счёт">
            </div>

            <div class="login-group">
                <label>Заголовок</label>
                <input type="text" name="title" id="notifTitleInput" placeholder="Заголовок" required>
            </div>

            <div class="login-group">
                <label>Текст уведомления</label>
                <textarea name="message" id="notifMessage" placeholder="Текст уведомления" required></textarea>
            </div>

            <div class="login-group">
                <label>Фото (можно несколько)</label>
                <input type="file" name="photos[]" multiple accept="image/*">
            </div>

            <div class="login-group">
                <label>Документ (PDF)</label>
                <input type="file" name="document" accept="application/pdf">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeNotificationModal()">Отмена</button>
                <button type="submit" class="login-button">Сохранить</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
