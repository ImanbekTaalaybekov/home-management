<?php
require __DIR__ . '/../include/auth.php';
require __DIR__ . '/../include/config.php';

$apiBaseUrl = API_BASE_URL;
$token      = $_SESSION['auth_token'] ?? null;

$search               = $_GET['search'] ?? '';
$residentialComplexId = $_GET['residential_complex_id'] ?? '';
$serviceFilter        = $_GET['service'] ?? '';
$allParam             = $_GET['all'] ?? 'false';
$onlyDebtorsParam     = $_GET['only_debtors'] ?? '';
$page                 = max(1, (int)($_GET['page'] ?? 1));

$rows           = [];
$complexes      = [];
$services       = [];
$errorMessage   = null;
$successMessage = null;
$totalPages     = 1;

function apiGet(string $url, string $token): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                    'Accept: application/json',
                    'Authorization: Bearer ' . $token,
            ],
    ]);
    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$status, json_decode($response, true)];
}

if ($token) {
    $query = $apiBaseUrl . '/debt-data?page=' . $page;

    if ($search !== '') {
        $query .= '&search=' . urlencode($search);
    }
    if ($residentialComplexId !== '') {
        $query .= '&residential_complex_id=' . urlencode($residentialComplexId);
    }
    if ($serviceFilter !== '') {
        $query .= '&service=' . urlencode($serviceFilter);
    }
    if ($allParam !== '') {
        $query .= '&all=' . urlencode($allParam);
    }
    if ($onlyDebtorsParam !== '') {
        $query .= '&only_debtors=' . urlencode($onlyDebtorsParam);
    }

    [$status, $result] = apiGet($query, $token);

    if ($status === 200) {
        $rows       = $result['data']      ?? [];
        $totalPages = $result['last_page'] ?? 1;
    } else {
        $errorMessage = $result['message'] ?? ('Ошибка загрузки данных (' . $status . ')');
    }

    [$cStatus, $cResult] = apiGet($apiBaseUrl . '/residential-complexes', $token);
    if ($cStatus === 200) {
        $complexes = $cResult['data'] ?? [];
    }

    $svcUrl    = $apiBaseUrl . '/analytics/services';
    $svcParams = [];
    if ($residentialComplexId !== '') {
        $svcParams[] = 'residential_complex_id=' . urlencode($residentialComplexId);
    }
    if (!empty($svcParams)) {
        $svcUrl .= '?' . implode('&', $svcParams);
    }
    [$svcStatus, $svcResult] = apiGet($svcUrl, $token);
    if ($svcStatus === 200) {
        $services = $svcResult['data'] ?? [];
    }
} else {
    $errorMessage = 'Нет токена авторизации';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Просмотр коммунальных данных</title>
    <link rel="stylesheet" href="/include/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .utilities-view-filters { display: flex; flex-direction: column; gap: 16px; }
        .utilities-view-row { display: grid; grid-template-columns: minmax(220px, 1.4fr) minmax(200px, 1.1fr) minmax(200px, 1.1fr); gap: 12px; align-items: center; }
        .utilities-view-row--bottom { display: flex; flex-wrap: wrap; gap: 16px; align-items: center; justify-content: space-between; }
        .utilities-view-field { width: 100%; }
        .utilities-view-field input, .utilities-view-field select { width: 100%; }
        .utilities-view-checkbox { display: flex; align-items: center; gap: 8px; font-size: 14px; }
        .utilities-view-actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .utilities-view-subtitle { margin-bottom: 16px; color: #6b7280; font-size: 14px; }
        .debt-row { background-color: #fee2e2; }
        @media (max-width: 720px) {
            .utilities-view-row { grid-template-columns: 1fr; }
            .utilities-view-row--bottom { align-items: flex-start; flex-direction: column; }
            .utilities-view-actions { width: 100%; }
            .utilities-view-actions .filter-button, .utilities-view-actions .button-secondary { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
<div class="layout">
    <?php include __DIR__ . '/../include/header.php'; ?>
    <aside class="sidebar"><?php include __DIR__ . '/../include/sidebar.php'; ?></aside>

    <main class="content">
        <h1 class="content__title">Просмотр коммунальных данных (Alseco)</h1>
        <p class="utilities-view-subtitle">Здесь можно посмотреть загруженные данные Alseco и отфильтровать их по ЖК, лицевому счёту, ФИО и услуге.</p>

        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="get" class="filter-form utilities-view-filters">
                <div class="utilities-view-row">
                    <div class="utilities-view-field">
                        <input type="text" name="search" placeholder="Поиск по ЛС, ФИО"
                               value="<?= htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="utilities-view-field">
                        <select name="residential_complex_id">
                            <option value="">Все ЖК</option>
                            <?php foreach ($complexes as $complex): ?>
                                <?php
                                $cid      = $complex['id'] ?? '';
                                $cname    = $complex['name'] ?? ('ЖК #' . $cid);
                                $selected = ($residentialComplexId !== '' && (string)$residentialComplexId === (string)$cid) ? 'selected' : '';
                                ?>
                                <option value="<?= htmlspecialchars($cid, ENT_QUOTES, 'UTF-8') ?>" <?= $selected ?>>
                                    <?= htmlspecialchars($cname, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="utilities-view-field">
                        <select name="service">
                            <option value="">Все услуги</option>
                            <?php foreach ($services as $svc): ?>
                                <?php $selected = ($serviceFilter === $svc) ? 'selected' : ''; ?>
                                <option value="<?= htmlspecialchars($svc, ENT_QUOTES, 'UTF-8') ?>" <?= $selected ?>>
                                    <?= htmlspecialchars($svc, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="utilities-view-row--bottom">
                    <label class="utilities-view-checkbox">
                        <input type="checkbox" name="all" value="true"
                                <?= ($allParam === 'true' || $allParam === '1') ? 'checked' : '' ?>>
                        Показать все периоды
                    </label>

                    <label class="utilities-view-checkbox">
                        <input type="checkbox" name="only_debtors" value="true"
                                <?= ($onlyDebtorsParam === 'true' || $onlyDebtorsParam === '1') ? 'checked' : '' ?>>
                        Только должники
                    </label>

                    <div class="utilities-view-actions">
                        <button type="submit" class="filter-button">Применить</button>
                        <a href="/sections/utilities_view.php" class="button-secondary">
                            <button type="submit" class="filter-button" style="background-color: red">Сбросить</button>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-wrapper" style="margin-top: 20px;">
            <table class="admins-table">
                <thead>
                <tr>
                    <th>Период</th>
                    <th>Лицевой счёт</th>
                    <th>Житель</th>
                    <th>ЖК</th>
                    <th>Услуга</th>
                    <th>Нач. сальдо</th>
                    <th>Начислено</th>
                    <th>Оплата</th>
                    <th>Дата оплаты</th>
                    <th>Кон. сальдо</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="10">Данные не найдены</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <?php
                        $year          = $row['year']   ?? null;
                        $month         = $row['month']  ?? null;
                        $period        = ($year && $month) ? sprintf('%02d.%d', (int)$month, (int)$year) : '';
                        $accountNumber = $row['account_number'] ?? '';
                        $fullName      = $row['full_name']      ?? '';
                        $residentName  = $row['resident_name']  ?? '';
                        $rcName        = $row['residential_complex_name'] ?? '';
                        $service       = $row['service'] ?? '';
                        $balanceStart  = $row['balance_start'] ?? null;
                        $initialAccr   = $row['initial_accrual'] ?? null;
                        $accrChange    = $row['accrual_change'] ?? null;
                        $payment       = $row['payment'] ?? null;
                        $paymentDate   = $row['payment_date'] ?? '';
                        $balanceEnd    = $row['balance_end'] ?? null;
                        $accrTotal     = ($initialAccr ?? 0) + ($accrChange ?? 0);
                        $isOverdue     = !empty($row['overdue']);
                        $trClass       = $isOverdue ? ' class="debt-row"' : '';
                        ?>
                        <tr<?= $trClass ?>>
                            <td><?= htmlspecialchars($period) ?></td>
                            <td><?= htmlspecialchars($accountNumber) ?></td>
                            <td>
                                <?php if ($residentName): ?>
                                    <?= htmlspecialchars($residentName) ?>
                                    <?php if ($fullName && $fullName !== $residentName): ?>
                                        <div class="table-subtext">Alseco: <?= htmlspecialchars($fullName) ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($fullName) ?>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($rcName) ?></td>
                            <td><?= htmlspecialchars($service) ?></td>
                            <td><?= number_format((float)$balanceStart, 2, ',', ' ') ?></td>
                            <td><?= number_format((float)$accrTotal, 2, ',', ' ') ?></td>
                            <td><?= number_format((float)$payment, 2, ',', ' ') ?></td>
                            <td><?= htmlspecialchars($paymentDate) ?></td>
                            <td><?= number_format((float)$balanceEnd, 2, ',', ' ') ?></td>
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
                    if ($search !== '') $link .= '&search=' . urlencode($search);
                    if ($residentialComplexId !== '') $link .= '&residential_complex_id=' . urlencode($residentialComplexId);
                    if ($serviceFilter !== '') $link .= '&service=' . urlencode($serviceFilter);
                    if ($allParam !== '') $link .= '&all=' . urlencode($allParam);
                    if ($onlyDebtorsParam !== '') $link .= '&only_debtors=' . urlencode($onlyDebtorsParam);
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
        const sidebar = document.getElementById('sidebar-finance');
        if (sidebar) sidebar.classList.add('sidebar__group--open');
    });
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('menu_utilities_view');
        if (sidebar) sidebar.classList.add('menu-selected-point');
    });
</script>
</body>
</html>