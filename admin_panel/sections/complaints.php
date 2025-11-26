<?php
require __DIR__ . '/../include/auth.php';
require __DIR__ . '/../include/config.php';

$apiBaseUrl = API_BASE_URL;
$token      = $_SESSION['auth_token'] ?? null;

$statusFilter          = $_GET['status'] ?? '';
$complexFilter         = $_GET['residential_complex_id'] ?? '';
$page                  = max(1, (int)($_GET['page'] ?? 1));

$complaints            = [];
$totalPages            = 1;
$errorMessage          = null;
$successMessage        = null;
$complexes             = [];

function apiRequestComplaints(string $method, string $url, string $token, ?array $data = null): array
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
        $id = (int)($_POST['complaint_id'] ?? 0);
        if ($id) {
            [$status, $data] = apiRequestComplaints(
                    'POST',
                    $apiBaseUrl . '/complaints/' . $id . '/status',
                    $token
            );

            if ($status === 200) {
                $successMessage = $data['message'] ?? 'Статус жалобы обновлён';
            } else {
                $errorMessage = $data['message'] ?? 'Ошибка обновления статуса';
            }
        }
    }

    if ($action === 'delete_complaint') {
        $id = (int)($_POST['complaint_id'] ?? 0);
        if ($id) {
            [$status, $data] = apiRequestComplaints(
                    'DELETE',
                    $apiBaseUrl . '/complaints/' . $id,
                    $token
            );

            if ($status === 200) {
                $successMessage = $data['message'] ?? 'Жалоба удалена';
            } else {
                $errorMessage = $data['message'] ?? 'Ошибка удаления жалобы';
            }
        }
    }

    $qs = $_SERVER['QUERY_STRING'] ? ('?' . $_SERVER['QUERY_STRING']) : '';
    header('Location: ' . $_SERVER['PHP_SELF'] . $qs);
    exit;
}

$query = $apiBaseUrl . '/complaints?page=' . $page;

if ($statusFilter !== '') {
    $query .= '&status=' . urlencode($statusFilter);
}
if ($complexFilter !== '') {
    $query .= '&residential_complex_id=' . urlencode($complexFilter);
}

if ($token) {
    [$status, $data] = apiRequestComplaints('GET', $query, $token);

    if ($status === 200) {
        $complaints = $data['data'] ?? [];
        $totalPages = $data['last_page'] ?? 1;
    } else {
        $errorMessage = $data['message'] ?? ('Ошибка загрузки жалоб (' . $status . ')');
    }

    [$cStatus, $cData] = apiRequestComplaints(
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

function kb_short(string $text, int $limit = 80): string
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
    <title>Жалобы</title>
    <link rel="stylesheet" href="/include/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<div class="layout">
    <?php include __DIR__ . '/../include/header.php'; ?>
    <aside class="sidebar"><?php include __DIR__ . '/../include/sidebar.php'; ?></aside>

    <main class="content">
        <h1 class="content__title">Жалобы</h1>

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
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($complaints)): ?>
                    <tr>
                        <td colspan="8">Жалоб не найдено</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($complaints as $c): ?>
                        <?php
                        $id        = $c['id'] ?? '';
                        $createdAt = $c['created_at'] ?? '';
                        $statusVal = $c['status'] ?? '';

                        $user          = $c['user'] ?? [];
                        $userName      = $user['name'] ?? '';
                        $personalAcc   = $user['personal_account'] ?? '';
                        $phone         = $user['phone_number'] ?? '';
                        $rc            = $user['residential_complex'] ?? $user['residentialComplex'] ?? [];
                        $rcName        = $rc['name'] ?? '';

                        $textFull  = $c['message'] ?? $c['text'] ?? '';
                        $textShort = kb_short($textFull ?: '', 70);

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
                                <div class="admins-actions">
                                    <button
                                            type="button"
                                            class="btn-small"
                                            onclick="openComplaintModal(this)"
                                            data-id="<?= htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8') ?>"
                                            data-created="<?= htmlspecialchars((string)$createdAt, ENT_QUOTES, 'UTF-8') ?>"
                                            data-status="<?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>"
                                            data-rc="<?= htmlspecialchars((string)$rcName, ENT_QUOTES, 'UTF-8') ?>"
                                            data-name="<?= htmlspecialchars((string)$userName, ENT_QUOTES, 'UTF-8') ?>"
                                            data-phone="<?= htmlspecialchars((string)$phone, ENT_QUOTES, 'UTF-8') ?>"
                                            data-account="<?= htmlspecialchars((string)$personalAcc, ENT_QUOTES, 'UTF-8') ?>"
                                            data-text="<?= htmlspecialchars($textFull, ENT_QUOTES, 'UTF-8') ?>"
                                    >Подробнее</button>

                                    <?php if ($statusVal !== 'done'): ?>
                                        <form method="post" style="display:inline"
                                              onsubmit="return confirm('Отметить жалобу как выполненную?');">
                                            <input type="hidden" name="action" value="set_done">
                                            <input type="hidden" name="complaint_id"
                                                   value="<?= htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" class="btn-small btn-success">
                                                Выполнено
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="post" style="display:inline"
                                          onsubmit="return confirm('Удалить жалобу?');">
                                        <input type="hidden" name="action" value="delete_complaint">
                                        <input type="hidden" name="complaint_id"
                                               value="<?= htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8') ?>">
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
                    <a href="<?= $link ?>" class="<?= $i === $page ? 'active-page' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    </main>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>

<script>
    function complaintEls() {
        return {
            modal: document.getElementById('complaintModal'),
            id: document.getElementById('complaintViewId'),
            created: document.getElementById('complaintViewCreated'),
            status: document.getElementById('complaintViewStatus'),
            rc: document.getElementById('complaintViewRc'),
            user: document.getElementById('complaintViewUser'),
            phone: document.getElementById('complaintViewPhone'),
            account: document.getElementById('complaintViewAccount'),
            text: document.getElementById('complaintViewText')
        };
    }

    function openComplaintModal(btn) {
        const e = complaintEls();
        e.modal.classList.add('modal-open');
        e.id.textContent       = btn.dataset.id || '';
        e.created.textContent  = btn.dataset.created || '';
        e.rc.textContent       = btn.dataset.rc || '';
        e.user.textContent     = btn.dataset.name || '';
        e.phone.textContent    = btn.dataset.phone || '';
        e.account.textContent  = btn.dataset.account || '';
        e.text.textContent     = btn.dataset.text || '';

        const statusText = btn.dataset.status || '';
        e.status.textContent = statusText;

        e.status.className = '';
        if (statusText === 'Готово') {
            e.status.classList.add('badge', 'badge-green');
        } else if (statusText === 'В процессе') {
            e.status.classList.add('badge', 'badge-yellow');
        } else {
            e.status.classList.add('badge', 'badge-gray');
        }
    }

    function closeComplaintModal() {
        complaintEls().modal.classList.remove('modal-open');
    }
</script>

<div class="modal-backdrop" id="complaintModal">
    <div class="modal modal--lg">
        <div class="modal-header">
            <strong>Жалоба <span id="complaintViewId"></span></strong>
            <button type="button" class="modal-close" onclick="closeComplaintModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="kb-view-grid">
                <div><strong>Дата:</strong> <span id="complaintViewCreated"></span></div>
                <div><strong>Статус:</strong> <span id="complaintViewStatus"></span></div>
                <div><strong>ЖК:</strong> <span id="complaintViewRc"></span></div>
                <div><strong>Пользователь:</strong> <span id="complaintViewUser"></span></div>
                <div><strong>Телефон:</strong> <span id="complaintViewPhone"></span></div>
                <div><strong>Лицевой счёт:</strong> <span id="complaintViewAccount"></span></div>
            </div>
            <div class="kb-view-text">
                <strong>Текст жалобы:</strong>
                <pre id="complaintViewText" style="white-space: pre-wrap;"></pre>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeComplaintModal()">Закрыть</button>
        </div>
    </div>
</div>

</body>
</html>
