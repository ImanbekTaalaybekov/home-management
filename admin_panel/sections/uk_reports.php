<?php
require __DIR__ . '/../include/auth.php';
require __DIR__ . '/../include/config.php';

$apiBaseUrl = API_BASE_URL;
$token      = $_SESSION['auth_token'] ?? null;

$page      = max(1, (int)($_GET['page'] ?? 1));
$complexId = $_GET['residential_complex_id'] ?? '';

$reports        = [];
$totalPages     = 1;
$complexes      = [];
$errorMessage   = null;
$successMessage = null;

function apiRequestJson(string $method, string $url, string $token, ?array $data = null): array
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

function apiRequestMultipart(string $method, string $url, string $token, array $fields): array
{
    $ch = curl_init($url);

    $headers = [
        'Accept: application/json',
        'Authorization: Bearer ' . $token,
    ];

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_POSTFIELDS     => $fields,
    ]);

    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$status, json_decode($response, true)];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token) {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_report') {
        $fields = [
            'title'   => $_POST['title'] ?? '',
            'message' => $_POST['message'] ?? '',
        ];

        if (isset($_POST['residential_complex_id']) && $_POST['residential_complex_id'] !== '') {
            $fields['residential_complex_id'] = $_POST['residential_complex_id'];
        }

        if (!empty($_FILES['document']['tmp_name'])) {
            $fields['document'] = new CURLFile(
                $_FILES['document']['tmp_name'],
                $_FILES['document']['type'] ?? 'application/pdf',
                $_FILES['document']['name'] ?? 'report.pdf'
            );
        }

        [$status, $data] = apiRequestMultipart(
            'POST',
            $apiBaseUrl . '/company-report/store',
            $token,
            $fields
        );

        if ($status === 201) {
            $successMessage = $data['message'] ?? 'Отчёт сохранён';
        } else {
            $errorMessage = $data['message'] ?? 'Ошибка создания отчёта';
        }
    }

    if ($action === 'update_report') {
        $id = (int)($_POST['report_id'] ?? 0);

        if ($id) {
            $fields = [];
            $fields['title']   = $_POST['title'] ?? '';
            $fields['message'] = $_POST['message'] ?? '';

            if (isset($_POST['residential_complex_id'])) {
                $fields['residential_complex_id'] = ($_POST['residential_complex_id'] === '')
                    ? ''
                    : $_POST['residential_complex_id'];
            }

            if (!empty($_FILES['document']['tmp_name'])) {
                $fields['document'] = new CURLFile(
                    $_FILES['document']['tmp_name'],
                    $_FILES['document']['type'] ?? 'application/pdf',
                    $_FILES['document']['name'] ?? 'report.pdf'
                );
            }

            [$status, $data] = apiRequestMultipart(
                'PUT',
                $apiBaseUrl . '/company-report/update/' . $id,
                $token,
                $fields
            );

            if ($status === 200) {
                $successMessage = $data['message'] ?? 'Отчёт обновлён';
            } else {
                $errorMessage = $data['message'] ?? 'Ошибка обновления отчёта';
            }
        }
    }

    if ($action === 'delete_report') {
        $id = (int)($_POST['report_id'] ?? 0);

        if ($id) {
            [$status, $data] = apiRequestJson(
                'DELETE',
                $apiBaseUrl . '/company-report/remove/' . $id,
                $token
            );

            if ($status === 200) {
                $successMessage = $data['message'] ?? 'Отчёт удалён';
            } else {
                $errorMessage = $data['message'] ?? 'Ошибка удаления отчёта';
            }
        }
    }
}

if ($token) {
    [$cStatus, $cData] = apiRequestJson('GET', $apiBaseUrl . '/residential-complexes', $token);

    if ($cStatus === 200) {
        $complexes = $cData['data'] ?? [];
    }
}

if ($token) {
    $query = $apiBaseUrl . '/company-report/show?page=' . $page;
    if ($complexId !== '') {
        $query .= '&residential_complex_id=' . urlencode($complexId);
    }

    [$rStatus, $rData] = apiRequestJson('GET', $query, $token);

    if ($rStatus === 200) {
        $reports    = $rData['data'] ?? [];
        $totalPages = $rData['last_page'] ?? 1;
    } else {
        $errorMessage = $errorMessage ?: ($rData['message'] ?? 'Ошибка загрузки отчётов');
    }
} else {
    $errorMessage = 'Нет токена авторизации';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отчёты УК</title>
    <link rel="stylesheet" href="/include/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<div class="layout">
    <?php include __DIR__ . '/../include/header.php'; ?>
    <aside class="sidebar"><?php include __DIR__ . '/../include/sidebar.php'; ?></aside>

    <main class="content">
        <h1 class="content__title">Отчёты УК</h1>

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
            </form>

            <button type="button"
                    class="button-primary button-xs uk_reports_button"
                    onclick="openCreateReportModal()">
                + Новый отчёт
            </button>
        </div>

        <div class="table-wrapper">
            <table class="admins-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Дата</th>
                    <th>ЖК</th>
                    <th>Заголовок</th>
                    <th>Документ</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($reports)): ?>
                    <tr>
                        <td colspan="6">Отчёты не найдены</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reports as $r): ?>
                        <?php
                        $id        = $r['id'] ?? '';
                        $createdAt = $r['created_at'] ?? '';
                        $title     = $r['title'] ?? '';
                        $message   = $r['message'] ?? '';
                        $docUrl    = $r['document_url'] ?? null;

                        $rc          = $r['residential_complex'] ?? $r['residentialComplex'] ?? null;
                        $rcId        = $rc['id'] ?? null;
                        $rcName      = $rc['name'] ?? ($rcId ? ('ЖК #' . $rcId) : 'Общий отчёт');
                        ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$createdAt, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$rcName, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$title, ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if ($docUrl): ?>
                                    <a href="<?= htmlspecialchars($docUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank">
                                        Открыть PDF
                                    </a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="admins-actions">
                                    <button
                                        type="button"
                                        class="btn-small btn-edit"
                                        onclick="openEditReportModal(this)"
                                        data-id="<?= htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8') ?>"
                                        data-title="<?= htmlspecialchars((string)$title, ENT_QUOTES, 'UTF-8') ?>"
                                        data-message="<?= htmlspecialchars((string)$message, ENT_QUOTES, 'UTF-8') ?>"
                                        data-rc-id="<?= htmlspecialchars((string)($rcId ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                        data-document-url="<?= htmlspecialchars((string)($docUrl ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                    >Редактировать</button>

                                    <form method="post" style="display:inline"
                                          onsubmit="return confirm('Удалить отчёт?');">
                                        <input type="hidden" name="action" value="delete_report">
                                        <input type="hidden" name="report_id"
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
    function reportEls() {
        return {
            modal:        document.getElementById('reportModal'),
            formAction:   document.getElementById('reportFormAction'),
            reportId:     document.getElementById('reportId'),
            title:        document.getElementById('reportTitle'),
            message:      document.getElementById('reportMessage'),
            complex:      document.getElementById('reportComplex'),
            docNote:      document.getElementById('reportDocNote'),
            headerTitle:  document.getElementById('reportModalHeaderTitle')
        };
    }

    function openCreateReportModal() {
        const e = reportEls();
        e.modal.classList.add('modal-open');
        e.headerTitle.textContent = 'Новый отчёт';
        e.formAction.value = 'create_report';
        e.reportId.value   = '';
        e.title.value      = '';
        e.message.value    = '';
        e.complex.value    = '';

        if (e.docNote) {
            e.docNote.innerHTML = '';
        }
    }

    function openEditReportModal(btn) {
        const e = reportEls();
        e.modal.classList.add('modal-open');
        e.headerTitle.textContent = 'Редактировать отчёт';
        e.formAction.value = 'update_report';
        e.reportId.value   = btn.dataset.id || '';
        e.title.value      = btn.dataset.title || '';
        e.message.value    = btn.dataset.message || '';

        const rcId = btn.dataset.rcId || '';
        e.complex.value = rcId;

        if (e.docNote) {
            const url = btn.dataset.documentUrl || '';
            if (url) {
                e.docNote.innerHTML = '<a href=\"' + url + '\" target=\"_blank\">Текущий документ</a>';
            } else {
                e.docNote.innerHTML = 'Документ не прикреплён';
            }
        }
    }

    function closeReportModal() {
        reportEls().modal.classList.remove('modal-open');
    }
</script>

<div class="modal-backdrop" id="reportModal">
    <div class="modal modal--lg">
        <div class="modal-header">
            <strong id="reportModalHeaderTitle">Отчёт</strong>
            <button type="button" class="modal-close" onclick="closeReportModal()">×</button>
        </div>
        <form method="post" enctype="multipart/form-data" class="login-form">
            <input type="hidden" name="action" id="reportFormAction">
            <input type="hidden" name="report_id" id="reportId">

            <div class="login-group">
                <label>Жилой комплекс</label>
                <select name="residential_complex_id" id="reportComplex">
                    <option value="">Общий отчёт (для всех ЖК)</option>
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
                <input type="text" name="title" id="reportTitle" required>
            </div>

            <div class="login-group">
                <label>Сообщение</label>
                <textarea name="message" id="reportMessage" rows="6" required></textarea>
            </div>

            <div class="login-group">
                <label>PDF-документ</label>
                <input type="file" name="document" accept="application/pdf">
                <div id="reportDocNote" class="field-hint"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeReportModal()">Отмена</button>
                <button type="submit" class="login-button">Сохранить</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
