<?php
require __DIR__ . '/../include/auth.php';
require __DIR__ . '/../include/config.php';

$apiBaseUrl = API_BASE_URL;
$token      = $_SESSION['auth_token'] ?? null;

$statusFilter = $_GET['status'] ?? '';
$search       = trim($_GET['search'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));

$requests       = [];
$totalPages     = 1;
$errorMessage   = null;
$successMessage = null;

function apiRequestService(string $method, string $url, string $token, ?array $data = null): array
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

    if ($action === 'set_status') {
        $id     = (int)($_POST['request_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if ($id && $status) {
            [$code, $data] = apiRequestService(
                    'PUT',
                    $apiBaseUrl . '/service-requests/' . $id . '/status',
                    $token,
                    ['status' => $status]
            );

            if ($code === 200) {
                $successMessage = $data['message'] ?? 'Статус заявки обновлён';
            } else {
                $errorMessage = $data['message'] ?? 'Ошибка обновления статуса';
            }
        }
    }

    if ($action === 'assign_master') {
        $id        = (int)($_POST['request_id'] ?? 0);
        $masterId  = (int)($_POST['master_id'] ?? 0);
        $newStatus = trim($_POST['assign_status'] ?? '');

        if ($id && $masterId) {
            $payload = ['master_id' => $masterId];
            if ($newStatus !== '') {
                $payload['status'] = $newStatus;
            }

            [$code, $data] = apiRequestService(
                    'POST',
                    $apiBaseUrl . '/service-requests/' . $id . '/assign-master',
                    $token,
                    $payload
            );

            if ($code === 200) {
                $successMessage = $data['message'] ?? 'Мастер назначен';
            } else {
                $errorMessage = $data['message'] ?? 'Ошибка назначения мастера';
            }
        }
    }

    if ($action === 'delete_request') {
        $id = (int)($_POST['request_id'] ?? 0);

        if ($id) {
            [$code, $data] = apiRequestService(
                    'DELETE',
                    $apiBaseUrl . '/service-requests/' . $id,
                    $token
            );

            if ($code === 200) {
                $successMessage = $data['message'] ?? 'Заявка удалена';
            } else {
                $errorMessage = $data['message'] ?? 'Ошибка удаления заявки';
            }
        }
    }
}

$query = $apiBaseUrl . '/service-requests?page=' . $page;
if ($statusFilter !== '') {
    $query .= '&status=' . urlencode($statusFilter);
}
if ($search !== '') {
    $query .= '&search=' . urlencode($search);
}

if ($token) {
    [$code, $data] = apiRequestService('GET', $query, $token);

    if ($code === 200) {
        $requests   = $data['data'] ?? [];
        $totalPages = $data['last_page'] ?? 1;
    } else {
        $errorMessage = $data['message'] ?? ('Ошибка загрузки заявок (' . $code . ')');
    }
} else {
    $errorMessage = 'Нет токена авторизации';
}

function mc_short(string $text, int $limit = 80): string
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
    <title>Вызов мастера</title>
    <link rel="stylesheet" href="/include/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<div class="layout">
    <?php include __DIR__ . '/../include/header.php'; ?>
    <aside class="sidebar"><?php include __DIR__ . '/../include/sidebar.php'; ?></aside>

    <main class="content">
        <h1 class="content__title">Вызов мастера</h1>

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
            <input type="text"
                   name="search"
                   placeholder="Поиск по описанию / типу"
                   value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">

            <select name="status">
                <option value="">Все статусы</option>
                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Ожидает</option>
                <option value="in_progress" <?= $statusFilter === 'in_progress' ? 'selected' : '' ?>>В работе</option>
                <option value="done" <?= $statusFilter === 'done' ? 'selected' : '' ?>>Выполнено</option>
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
                    <th>Тип</th>
                    <th>ЖК</th>
                    <th>Пользователь</th>
                    <th>Телефон</th>
                    <th>Описание</th>
                    <th>Мастер</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="10">Заявок не найдено</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $r): ?>
                        <?php
                        $id        = $r['id'] ?? '';
                        $createdAt = $r['created_at'] ?? '';
                        $statusVal = $r['status'] ?? '';

                        $type        = $r['type'] ?? '';
                        $description = $r['description'] ?? '';
                        $shortDesc   = mc_short($description ?: '', 60);

                        $user      = $r['user'] ?? [];
                        $userName  = $user['name'] ?? '';
                        $phone     = $user['phone_number'] ?? '';
                        $rc        = $user['residential_complex'] ?? $user['residentialComplex'] ?? [];
                        $rcName    = $rc['name'] ?? '';
                        $masterId  = $r['master_id'] ?? null;

                        $statusClass = 'badge-gray';
                        $statusLabel = '—';

                        if ($statusVal === 'pending') {
                            $statusClass = 'badge-blue';
                            $statusLabel = 'Ожидает';
                        }

                        if ($statusVal === 'in_progress') {
                            $statusClass = 'badge-yellow';
                            $statusLabel = 'В работе';
                        }

                        if ($statusVal === 'done') {
                            $statusClass = 'badge-green';
                            $statusLabel = 'Выполнено';
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
                            <td><?= htmlspecialchars((string)$type, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$rcName, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$userName, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$phone, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($shortDesc, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $masterId ? htmlspecialchars((string)$masterId, ENT_QUOTES, 'UTF-8') : '—' ?></td>
                            <td>
                                <div class="admins-actions">
                                    <button
                                            type="button"
                                            class="btn-small"
                                            onclick="openRequestModal(this)"
                                            data-id="<?= htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8') ?>"
                                            data-created="<?= htmlspecialchars((string)$createdAt, ENT_QUOTES, 'UTF-8') ?>"
                                            data-status="<?= htmlspecialchars((string)$statusVal, ENT_QUOTES, 'UTF-8') ?>"
                                            data-status-label="<?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>"
                                            data-type="<?= htmlspecialchars((string)$type, ENT_QUOTES, 'UTF-8') ?>"
                                            data-rc="<?= htmlspecialchars((string)$rcName, ENT_QUOTES, 'UTF-8') ?>"
                                            data-name="<?= htmlspecialchars((string)$userName, ENT_QUOTES, 'UTF-8') ?>"
                                            data-phone="<?= htmlspecialchars((string)$phone, ENT_QUOTES, 'UTF-8') ?>"
                                            data-desc="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>"
                                            data-master="<?= htmlspecialchars((string)($masterId ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                    >Подробнее</button>

                                    <button
                                            type="button"
                                            class="btn-small btn-secondary"
                                            onclick="openAssignMasterModal(this)"
                                            data-id="<?= htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8') ?>"
                                            data-master="<?= htmlspecialchars((string)($masterId ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                    >Назначить мастера</button>

                                    <?php if ($statusVal !== 'done'): ?>
                                        <form method="post" style="display:inline">
                                            <input type="hidden" name="action" value="set_status">
                                            <input type="hidden" name="request_id"
                                                   value="<?= htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="status" value="done">
                                            <button type="submit" class="btn-small btn-success">
                                                Выполнено
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="post" style="display:inline"
                                          onsubmit="return confirm('Удалить заявку?');">
                                        <input type="hidden" name="action" value="delete_request">
                                        <input type="hidden" name="request_id"
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
                    if ($statusFilter !== '') $link .= '&status=' . urlencode($statusFilter);
                    if ($search !== '')       $link .= '&search=' . urlencode($search);
                    ?>
                    <a href="<?= $link ?>" class="<?= $i === $page ? 'active-page' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    </main>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>

<script>
    function reqViewEls() {
        return {
            modal:   document.getElementById('requestModal'),
            id:      document.getElementById('reqViewId'),
            created: document.getElementById('reqViewCreated'),
            status:  document.getElementById('reqViewStatus'),
            type:    document.getElementById('reqViewType'),
            rc:      document.getElementById('reqViewRc'),
            user:    document.getElementById('reqViewUser'),
            phone:   document.getElementById('reqViewPhone'),
            master:  document.getElementById('reqViewMaster'),
            desc:    document.getElementById('reqViewDesc')
        };
    }

    function openRequestModal(btn) {
        const e = reqViewEls();
        e.modal.classList.add('modal-open');
        e.id.textContent      = btn.dataset.id || '';
        e.created.textContent = btn.dataset.created || '';
        e.status.textContent  = btn.dataset.statusLabel || btn.dataset.status || '';
        e.type.textContent    = btn.dataset.type || '';
        e.rc.textContent      = btn.dataset.rc || '';
        e.user.textContent    = btn.dataset.name || '';
        e.phone.textContent   = btn.dataset.phone || '';
        e.master.textContent  = btn.dataset.master || '—';
        e.desc.textContent    = btn.dataset.desc || '';
    }

    function closeRequestModal() {
        reqViewEls().modal.classList.remove('modal-open');
    }

    function assignEls() {
        return {
            modal:   document.getElementById('assignMasterModal'),
            idField: document.getElementById('assignRequestId'),
            master:  document.getElementById('assignMasterId')
        };
    }

    function openAssignMasterModal(btn) {
        const e = assignEls();
        e.modal.classList.add('modal-open');
        e.idField.value = btn.dataset.id || '';
        e.master.value  = btn.dataset.master || '';
    }

    function closeAssignMasterModal() {
        assignEls().modal.classList.remove('modal-open');
    }
</script>

<div class="modal-backdrop" id="requestModal">
    <div class="modal modal--lg">
        <div class="modal-header">
            <strong>Заявка <span id="reqViewId"></span></strong>
            <button type="button" class="modal-close" onclick="closeRequestModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="kb-view-grid">
                <div><strong>Дата:</strong> <span id="reqViewCreated"></span></div>
                <div><strong>Статус:</strong> <span id="reqViewStatus"></span></div>
                <div><strong>Тип:</strong> <span id="reqViewType"></span></div>
                <div><strong>ЖК:</strong> <span id="reqViewRc"></span></div>
                <div><strong>Пользователь:</strong> <span id="reqViewUser"></span></div>
                <div><strong>Телефон:</strong> <span id="reqViewPhone"></span></div>
                <div><strong>ID мастера:</strong> <span id="reqViewMaster"></span></div>
            </div>
            <div class="kb-view-text">
                <strong>Описание:</strong>
                <pre id="reqViewDesc" style="white-space: pre-wrap;"></pre>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeRequestModal()">Закрыть</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="assignMasterModal">
    <div class="modal">
        <div class="modal-header">
            <strong>Назначить мастера</strong>
            <button type="button" class="modal-close" onclick="closeAssignMasterModal()">×</button>
        </div>
        <form method="post" class="login-form">
            <input type="hidden" name="action" value="assign_master">
            <input type="hidden" name="request_id" id="assignRequestId">

            <div class="login-group">
                <label>ID мастера</label>
                <input type="number" name="master_id" id="assignMasterId" required>
            </div>

            <div class="login-group">
                <label>Статус после назначения</label>
                <select name="assign_status">
                    <option value="">Оставить как есть (по умолчанию in_progress)</option>
                    <option value="pending">Ожидает</option>
                    <option value="in_progress">В работе</option>
                    <option value="done">Выполнено</option>
                </select>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeAssignMasterModal()">Отмена</button>
                <button type="submit" class="login-button">Сохранить</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
