<?php
require __DIR__ . '/../include/auth.php';
require __DIR__ . '/../include/config.php';

$apiBaseUrl = API_BASE_URL;
$token      = $_SESSION['auth_token'] ?? null;

$page      = max(1, (int)($_GET['page'] ?? 1));
$complexId = $_GET['residential_complex_id'] ?? '';

$polls          = [];
$totalPages     = 1;
$complexes      = [];
$errorMessage   = null;
$successMessage = null;

function apiRequestJsonVoting(string $method, string $url, string $token, ?array $data = null): array
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

    if ($action === 'create_poll') {
        $payload = [
            'title'       => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'start_date'  => $_POST['start_date'] ?? '',
            'end_date'    => $_POST['end_date'] ?? '',
        ];

        if (isset($_POST['residential_complex_id']) && $_POST['residential_complex_id'] !== '') {
            $payload['residential_complex_id'] = $_POST['residential_complex_id'];
        }

        [$status, $data] = apiRequestJsonVoting(
            'POST',
            $apiBaseUrl . '/polls',
            $token,
            $payload
        );

        if ($status === 201) {
            $successMessage = $data['message'] ?? 'Опрос успешно создан';
        } else {
            $errorMessage = $data['message'] ?? 'Ошибка создания опроса';
        }
    }

    if ($action === 'delete_poll') {
        $id = (int)($_POST['poll_id'] ?? 0);

        if ($id) {
            [$status, $data] = apiRequestJsonVoting(
                'DELETE',
                $apiBaseUrl . '/polls/' . $id,
                $token
            );

            if ($status === 200) {
                $successMessage = $data['message'] ?? 'Опрос удалён';
            } else {
                $errorMessage = $data['message'] ?? 'Ошибка удаления опроса';
            }
        }
    }

    $qs = $_SERVER['QUERY_STRING'] ? ('?' . $_SERVER['QUERY_STRING']) : '';
    header('Location: ' . $_SERVER['PHP_SELF'] . $qs);
    exit;
}

if ($token) {
    [$cStatus, $cData] = apiRequestJsonVoting('GET', $apiBaseUrl . '/residential-complexes', $token);

    if ($cStatus === 200) {
        $complexes = $cData['data'] ?? [];
    }
}

if ($token) {
    $query = $apiBaseUrl . '/polls?page=' . $page;
    if ($complexId !== '') {
        $query .= '&residential_complex_id=' . urlencode($complexId);
    }

    [$pStatus, $pData] = apiRequestJsonVoting('GET', $query, $token);

    if ($pStatus === 200) {
        $polls      = $pData['data'] ?? [];
        $totalPages = $pData['last_page'] ?? 1;
    } else {
        $errorMessage = $errorMessage ?: ($pData['message'] ?? 'Ошибка загрузки опросов');
    }
} else {
    $errorMessage = 'Нет токена авторизации';
}

function pollStatusLabel(?string $start, ?string $end): string
{
    if (!$start && !$end) {
        return '—';
    }

    $now = new DateTimeImmutable('now');
    $s   = $start ? new DateTimeImmutable($start) : null;
    $e   = $end ? new DateTimeImmutable($end) : null;

    if ($s && $now < $s) {
        return 'Запланирован';
    }

    if ($s && $e && $now >= $s && $now <= $e) {
        return 'Активен';
    }

    if ($e && $now > $e) {
        return 'Завершён';
    }

    return '—';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Голосования</title>
    <link rel="stylesheet" href="/include/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<div class="layout">
    <?php include __DIR__ . '/../include/header.php'; ?>
    <aside class="sidebar"><?php include __DIR__ . '/../include/sidebar.php'; ?></aside>

    <main class="content">
        <h1 class="content__title">Голосования</h1>

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

        <div class="card-header card-header--space-between" style="margin-bottom: 12px;">
            <form method="get" class="filter-form" style="margin:0;">
                <select name="residential_complex_id">
                    <option value="">Все ЖК</option>
                    <?php foreach ($complexes as $cx): ?>
                        <?php
                        $cxId   = $cx['id'] ?? '';
                        $cxName = $cx['name'] ?? '';
                        ?>
                        <option value="<?= htmlspecialchars((string)$cxId, ENT_QUOTES, 'UTF-8') ?>"
                            <?= (string)$complexId === (string)$cxId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cxName, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="filter-button">Фильтр</button>

                <button type="button"
                        class="button-primary button-xs add-vote-button"
                        onclick="openCreatePollModal()">
                    + Новый опрос
                </button>
            </form>
        </div>

        <div class="table-wrapper">
            <table class="admins-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>ЖК</th>
                    <th>Заголовок</th>
                    <th>Период</th>
                    <th>Статус</th>
                    <th>Описание</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($polls)): ?>
                    <tr>
                        <td colspan="7">Опросы не найдены</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($polls as $p): ?>
                        <?php
                        $id          = $p['id'] ?? '';
                        $title       = $p['title'] ?? '';
                        $description = $p['description'] ?? '';
                        $startDate   = $p['start_date'] ?? '';
                        $endDate     = $p['end_date'] ?? '';

                        $rc          = $p['residential_complex'] ?? $p['residentialComplex'] ?? null;
                        $rcId        = $rc['id'] ?? null;
                        $rcName      = $rc['name'] ?? ($rcId ? ('ЖК #' . $rcId) : 'Для всех ЖК');

                        $statusLabel = pollStatusLabel($startDate ?: null, $endDate ?: null);

                        $badgeClass = 'badge-gray';
                        if ($statusLabel === 'Запланирован') $badgeClass = 'badge-blue';
                        if ($statusLabel === 'Активен')      $badgeClass = 'badge-green';
                        if ($statusLabel === 'Завершён')     $badgeClass = 'badge-yellow';

                        $shortDesc = mb_strlen($description, 'UTF-8') > 70
                            ? mb_substr($description, 0, 70, 'UTF-8') . '…'
                            : $description;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$rcName, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$title, ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?= htmlspecialchars((string)$startDate, ENT_QUOTES, 'UTF-8') ?>
                                –
                                <?= htmlspecialchars((string)$endDate, ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td>
                                <span class="badge <?= $badgeClass ?>">
                                    <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($shortDesc, ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <div class="admins-actions">
                                    <button
                                        type="button"
                                        class="btn-small"
                                        onclick="openViewPollModal(this)"
                                        data-id="<?= htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8') ?>"
                                        data-title="<?= htmlspecialchars((string)$title, ENT_QUOTES, 'UTF-8') ?>"
                                        data-desc="<?= htmlspecialchars((string)$description, ENT_QUOTES, 'UTF-8') ?>"
                                        data-rcname="<?= htmlspecialchars((string)$rcName, ENT_QUOTES, 'UTF-8') ?>"
                                        data-start="<?= htmlspecialchars((string)$startDate, ENT_QUOTES, 'UTF-8') ?>"
                                        data-end="<?= htmlspecialchars((string)$endDate, ENT_QUOTES, 'UTF-8') ?>"
                                        data-status="<?= htmlspecialchars((string)$statusLabel, ENT_QUOTES, 'UTF-8') ?>"
                                    >Просмотр</button>

                                    <a class="btn-small btn-secondary"
                                       href="<?= htmlspecialchars($apiBaseUrl . '/polls/protocol/' . $id, ENT_QUOTES, 'UTF-8') ?>"
                                       target="_blank">
                                        Протокол
                                    </a>

                                    <form method="post" style="display:inline"
                                          onsubmit="return confirm('Удалить опрос?');">
                                        <input type="hidden" name="action" value="delete_poll">
                                        <input type="hidden" name="poll_id"
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
                    if ($complexId !== '') {
                        $link .= '&residential_complex_id=' . urlencode($complexId);
                    }
                    ?>
                    <a href="<?= $link ?>"
                       class="<?= $i === $page ? 'active-page' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>

<script>
    function pollFormEls() {
        return {
            modal:       document.getElementById('pollModal'),
            headerTitle: document.getElementById('pollModalHeaderTitle'),
            action:      document.getElementById('pollFormAction'),
            id:          document.getElementById('pollId'),
            title:       document.getElementById('pollTitle'),
            desc:        document.getElementById('pollDescription'),
            complex:     document.getElementById('pollComplex'),
            start:       document.getElementById('pollStartDate'),
            end:         document.getElementById('pollEndDate')
        };
    }

    function openCreatePollModal() {
        const e = pollFormEls();
        e.modal.classList.add('modal-open');
        e.headerTitle.textContent = 'Новый опрос';
        e.action.value = 'create_poll';
        e.id.value     = '';
        e.title.value  = '';
        e.desc.value   = '';
        e.complex.value = '';
        e.start.value  = '';
        e.end.value    = '';
    }

    function closePollModal() {
        pollFormEls().modal.classList.remove('modal-open');
    }

    function pollViewEls() {
        return {
            modal:   document.getElementById('pollViewModal'),
            title:   document.getElementById('viewPollTitle'),
            rc:      document.getElementById('viewPollRc'),
            period:  document.getElementById('viewPollPeriod'),
            status:  document.getElementById('viewPollStatus'),
            desc:    document.getElementById('viewPollDesc')
        };
    }

    function openViewPollModal(btn) {
        const e = pollViewEls();
        e.modal.classList.add('modal-open');
        e.title.textContent  = btn.dataset.title || '';
        e.rc.textContent     = btn.dataset.rcname || '';
        e.period.textContent = (btn.dataset.start || '') + ' – ' + (btn.dataset.end || '');
        e.status.textContent = btn.dataset.status || '';
        e.desc.textContent   = btn.dataset.desc || '';
    }

    function closeViewPollModal() {
        pollViewEls().modal.classList.remove('modal-open');
    }
</script>

<div class="modal-backdrop" id="pollModal">
    <div class="modal modal--lg">
        <div class="modal-header">
            <strong id="pollModalHeaderTitle">Новый опрос</strong>
            <button type="button" class="modal-close" onclick="closePollModal()">×</button>
        </div>
        <form method="post" class="login-form">
            <input type="hidden" name="action" id="pollFormAction">
            <input type="hidden" name="poll_id" id="pollId">

            <div class="login-group">
                <label>Жилой комплекс</label>
                <select name="residential_complex_id" id="pollComplex">
                    <option value="">Для всех ЖК</option>
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

            <div class="login-group">
                <label>Заголовок</label>
                <input type="text" name="title" id="pollTitle" required>
            </div>

            <div class="login-group">
                <label>Описание</label>
                <textarea name="description" id="pollDescription" rows="5"></textarea>
            </div>

            <div class="login-group">
                <label>Дата начала</label>
                <input type="date" name="start_date" id="pollStartDate" required>
            </div>

            <div class="login-group">
                <label>Дата окончания</label>
                <input type="date" name="end_date" id="pollEndDate" required>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closePollModal()">Отмена</button>
                <button type="submit" class="login-button">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="pollViewModal">
    <div class="modal modal--lg">
        <div class="modal-header">
            <strong id="viewPollTitle"></strong>
            <button type="button" class="modal-close" onclick="closeViewPollModal()">×</button>
        </div>
        <div class="modal-body">
            <div class="kb-view-grid">
                <div><strong>ЖК:</strong> <span id="viewPollRc"></span></div>
                <div><strong>Период:</strong> <span id="viewPollPeriod"></span></div>
                <div><strong>Статус:</strong> <span id="viewPollStatus"></span></div>
            </div>
            <div class="kb-view-text">
                <strong>Описание:</strong>
                <pre id="viewPollDesc" style="white-space: pre-wrap;"></pre>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeViewPollModal()">Закрыть</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar-utilites');

        if (sidebar) {
            sidebar.classList.add('sidebar__group--open');
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('menu_voting');

        if (sidebar) {
            sidebar.classList.add('menu-selected-point');
        }
    });
</script>

</body>
</html>
