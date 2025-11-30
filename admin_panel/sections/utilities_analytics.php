<?php
require __DIR__ . '/../include/auth.php';
require __DIR__ . '/../include/config.php';

$apiBaseUrl = API_BASE_URL;
$token      = $_SESSION['auth_token'] ?? null;

$residentialComplexId = $_GET['residential_complex_id'] ?? '';
$serviceFilter        = $_GET['service'] ?? '';

$complexes        = [];
$services         = [];
$accrualSummary   = [];
$balanceSummary   = [];
$accrualDynamics  = [];
$errorMessage     = null;

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
    [$cStatus, $cResult] = apiGet($apiBaseUrl . '/residential-complexes', $token);
    if ($cStatus === 200) {
        $complexes = $cResult['data'] ?? [];
    } else {
        $errorMessage = $cResult['message'] ?? ('Ошибка загрузки ЖК (' . $cStatus . ')');
    }

    $svcUrl = $apiBaseUrl . '/analytics/services';
    $svcParams = [];
    if ($residentialComplexId !== '') {
        $svcParams[] = 'residential_complex_id=' . urlencode($residentialComplexId);
    }
    if (!empty($svcParams)) {
        $svcUrl .= '?' . implode('&', $svcParams);
    }

    [$sStatus, $sResult] = apiGet($svcUrl, $token);
    if ($sStatus === 200) {
        $services = $sResult['data'] ?? [];
    } elseif (!$errorMessage) {
        $errorMessage = $sResult['message'] ?? ('Ошибка загрузки услуг (' . $sStatus . ')');
    }

    $params = [];
    if ($residentialComplexId !== '') {
        $params[] = 'residential_complex_id=' . urlencode($residentialComplexId);
    }
    if ($serviceFilter !== '') {
        $params[] = 'service=' . urlencode($serviceFilter);
    }
    $qs = '';
    if (!empty($params)) {
        $qs = '?' . implode('&', $params);
    }

    [$aStatus, $aResult] = apiGet($apiBaseUrl . '/analytics/accrual-summary' . $qs, $token);
    if ($aStatus === 200) {
        $accrualSummary = $aResult['data'] ?? [];
    } elseif (!$errorMessage) {
        $errorMessage = $aResult['message'] ?? ('Ошибка аналитики начислений (' . $aStatus . ')');
    }

    [$bStatus, $bResult] = apiGet($apiBaseUrl . '/analytics/balance-summary' . $qs, $token);
    if ($bStatus === 200) {
        $balanceSummary = $bResult['data'] ?? [];
    } elseif (!$errorMessage) {
        $errorMessage = $bResult['message'] ?? ('Ошибка аналитики сальдо (' . $bStatus . ')');
    }

    [$adStatus, $adResult] = apiGet($apiBaseUrl . '/analytics/accrual-dynamics' . $qs, $token);
    if ($adStatus === 200) {
        $accrualDynamics = $adResult['data'] ?? [];
    } elseif (!$errorMessage) {
        $errorMessage = $adResult['message'] ?? ('Ошибка динамики начислений (' . $adStatus . ')');
    }
} else {
    $errorMessage = 'Нет токена авторизации';
}

function fmtMoney($value): string
{
    return number_format((float)($value ?? 0), 2, ',', ' ');
}

function fmtInt($value): int
{
    return (int)($value ?? 0);
}

$currentServiceLabel = $serviceFilter !== '' ? $serviceFilter : 'Все услуги';
$currentRcLabel = 'Все ЖК';
if ($residentialComplexId !== '') {
    foreach ($complexes as $complex) {
        if ((string)($complex['id'] ?? '') === (string)$residentialComplexId) {
            $currentRcLabel = $complex['name'] ?? $currentRcLabel;
            break;
        }
    }
}

$accrualDynamicsJson = json_encode($accrualDynamics, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Аналитика коммунальных данных</title>
    <link rel="stylesheet" href="/include/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .utilities-view-filters { display: flex; flex-direction: column; gap: 16px; }
        .utilities-view-row { display: grid; grid-template-columns: minmax(220px, 1.4fr) minmax(200px, 1.2fr); gap: 12px; align-items: center; }
        .utilities-view-row--bottom { display: flex; flex-wrap: wrap; gap: 16px; align-items: center; justify-content: space-between; }
        .utilities-view-field select { width: 100%; }
        .utilities-view-actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .analytics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-top: 20px; }
        .analytics-card { padding: 16px 18px; border-radius: 12px; background: #ffffff; border: 1px solid #e5e7eb; }
        .analytics-card__title { font-size: 15px; font-weight: 600; margin-bottom: 8px; }
        .analytics-card__subtitle { font-size: 13px; color: #6b7280; margin-bottom: 14px; }
        .analytics-metrics { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; font-size: 13px; }
        .analytics-metric__label { color: #6b7280; }
        .analytics-metric__value { font-weight: 600; }
        .analytics-metric__value--danger { color: #b91c1c; }
        .chart-container { margin-top: 24px; padding: 16px 18px; border-radius: 12px; background: #ffffff; border: 1px solid #e5e7eb; }
        .chart-title { font-size: 15px; font-weight: 600; margin-bottom: 8px; }
        .chart-subtitle { font-size: 13px; color: #6b7280; margin-bottom: 10px; }
        .chart-wrapper { position: relative; width: 100%; height: 340px; }
        @media (max-width: 900px) {
            .utilities-view-row { grid-template-columns: 1fr; }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="layout">
    <?php include __DIR__ . '/../include/header.php'; ?>
    <aside class="sidebar"><?php include __DIR__ . '/../include/sidebar.php'; ?></aside>

    <main class="content">
        <h1 class="content__title">Аналитика коммунальных данных (Alseco)</h1>

        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="get" class="utilities-view-filters">
                <div class="utilities-view-row">
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
                    <div class="utilities-view-actions">
                        <button type="submit" class="filter-button">Применить</button>
                        <a href="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') ?>" class="button-secondary">
                            <button type="button" class="filter-button" style="background-color:red">Сбросить</button>
                        </a>
                    </div>
                    <div style="font-size: 12px; color: #6b7280;">
                        <div>ЖК: <strong><?= htmlspecialchars($currentRcLabel, ENT_QUOTES, 'UTF-8') ?></strong></div>
                        <div>Услуга: <strong><?= htmlspecialchars($currentServiceLabel, ENT_QUOTES, 'UTF-8') ?></strong></div>
                    </div>
                </div>
            </form>
        </div>

        <div class="analytics-grid">
            <div class="analytics-card">
                <div class="analytics-card__title">Начисления и платежи (последний период)</div>
                <div class="analytics-card__subtitle">Сравнение начислений и оплат.</div>
                <div class="analytics-metrics">
                    <div>
                        <div class="analytics-metric__label">Итого начислено</div>
                        <div class="analytics-metric__value"><?= fmtMoney($accrualSummary['accrual_total_sum'] ?? 0) ?></div>
                    </div>
                    <div>
                        <div class="analytics-metric__label">Оплачено</div>
                        <div class="analytics-metric__value"><?= fmtMoney($accrualSummary['payment_sum'] ?? 0) ?></div>
                    </div>
                    <div>
                        <div class="analytics-metric__label">Разница (начислено - оплачено)</div>
                        <div class="analytics-metric__value"><?= fmtMoney($accrualSummary['diff_sum'] ?? 0) ?></div>
                    </div>
                    <div>
                        <div class="analytics-metric__label">Просроченные начисления</div>
                        <div class="analytics-metric__value analytics-metric__value--danger">
                            <?= fmtMoney($accrualSummary['overdue_accrual_sum'] ?? 0) ?>
                        </div>
                    </div>
                    <div>
                        <div class="analytics-metric__label">Количество выставленных счетов на оплату</div>
                        <div class="analytics-metric__value"><?= fmtInt($accrualSummary['rows_count'] ?? 0) ?></div>
                    </div>
                    <div>
                        <div class="analytics-metric__label">Количество просроченных платежей</div>
                        <div class="analytics-metric__value analytics-metric__value--danger">
                            <?= fmtInt($accrualSummary['overdue_count'] ?? 0) ?></div>
                    </div>
                </div>
            </div>

            <div class="analytics-card">
                <div class="analytics-card__title">Конечное сальдо (последний период)</div>
                <div class="analytics-card__subtitle">Итоговая задолженность/переплата.</div>
                <div class="analytics-metrics">
                    <div>
                        <div class="analytics-metric__label">Суммарное сальдо</div>
                        <div class="analytics-metric__value"><?= fmtMoney($balanceSummary['balance_total_sum'] ?? 0) ?></div>
                    </div>
                    <div>
                        <div class="analytics-metric__label">Просроченное сальдо</div>
                        <div class="analytics-metric__value analytics-metric__value--danger">
                            <?= fmtMoney($balanceSummary['overdue_balance_sum'] ?? 0) ?></div>
                    </div>
                    <div>
                        <div class="analytics-metric__label">Количество выставленных счетов на оплату</div>
                        <div class="analytics-metric__value"><?= fmtInt($balanceSummary['rows_count'] ?? 0) ?></div>
                    </div>
                    <div>
                        <div class="analytics-metric__label">Количество просроченных платежей</div>
                        <div class="analytics-metric__value analytics-metric__value--danger">
                            <?= fmtInt($balanceSummary['overdue_count'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="chart-container">
            <div class="chart-title">Динамика начислений и платежей по месяцам</div>
            <div class="chart-subtitle">Итого начислено, оплачено и разница по месяцам.</div>
            <div class="chart-wrapper">
                <canvas id="accrualChart"></canvas>
            </div>
        </div>

    </main>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var sidebar = document.getElementById('sidebar-finance');
        if (sidebar) sidebar.classList.add('sidebar__group--open');
        var item = document.getElementById('menu_utilities_view');
        if (item) item.classList.add('menu-selected-point');

        var accrualData = <?= $accrualDynamicsJson ?: '[]' ?>;

        function buildLabelsAndValues(rows) {
            var labels = [];
            var total = [];
            var paid = [];
            var diff = [];

            rows.forEach(function (row) {
                var year = (row.year || row.year === 0) ? row.year : null;
                var month = (row.month || row.month === 0) ? row.month : null;
                if (year === null || month === null) return;

                var label = (month < 10 ? '0' + month : month) + '.' + year;
                labels.push(label);

                var balanceStart  = parseFloat(row.balance_start || 0);
                var accrualSum    = parseFloat(row.accrual_total_sum || 0);
                var paymentRaw    = parseFloat(row.payment_sum || 0);

                var totalVal = Math.abs(balanceStart + accrualSum);
                var paidVal  = Math.abs(paymentRaw);
                var diffVal  = totalVal - paidVal;

                total.push(totalVal);
                paid.push(paidVal);
                diff.push(diffVal);
            });

            return { labels: labels, total: total, paid: paid, diff: diff };
        }

        var prepared = buildLabelsAndValues(accrualData);

        var accrualCtx = document.getElementById('accrualChart').getContext('2d');
        new Chart(accrualCtx, {
            type: 'bar',
            data: {
                labels: prepared.labels,
                datasets: [
                    {
                        label: 'Итого начислено',
                        data: prepared.total
                    },
                    {
                        label: 'Оплачено',
                        data: prepared.paid
                    },
                    {
                        label: 'Разница',
                        data: prepared.diff
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    x: { stacked: false },
                    y: { stacked: false, beginAtZero: true }
                }
            }
        });
    });
</script>
</body>
</html>
