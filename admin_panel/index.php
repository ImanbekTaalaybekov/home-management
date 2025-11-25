<?php
require __DIR__ . '/include/auth.php';
require __DIR__ . '/include/config.php';

$apiBaseUrl = API_BASE_URL;
$token = $_SESSION['auth_token'] ?? null;

if (!$token) {
    header('Location: /login.php');
    exit;
}

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
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
    ]);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$status, json_decode($response, true)];
}

$complexes = [];

[$statusRc, $resultRc] = apiRequest('GET', $apiBaseUrl . '/residential-complexes?page=1', $token);
if ($statusRc === 200 && is_array($resultRc)) {
    $complexes = $resultRc['data'] ?? [];
}

$selectedRcId = isset($_GET['residential_complex_id']) && $_GET['residential_complex_id'] !== ''
        ? (int)$_GET['residential_complex_id']
        : null;

$dashboardUrl = $apiBaseUrl . '/dashboard';
if ($selectedRcId) {
    $dashboardUrl .= '?residential_complex_id=' . $selectedRcId;
}

$data = [
        'polls' => [
                'total' => 0,
                'finished' => 0,
        ],
        'residents' => [
                'total' => 0,
                'blocks_total' => 0,
        ],
        'residential_complexes_total' => 0,
        'complaints' => [
                'total' => 0,
                'done' => 0,
        ],
        'suggestions' => [
                'total' => 0,
                'done' => 0,
        ],
        'service_requests' => [
                'total' => 0,
                'done' => 0,
        ],
        'analytics_last_period' => [
                'year' => null,
                'month' => null,
        ],
];

[$statusDash, $resultDash] = apiRequest('GET', $dashboardUrl, $token);
if ($statusDash === 200 && is_array($resultDash)) {
    $data = array_merge($data, $resultDash);
}

$pollsTotal = (int)($data['polls']['total'] ?? 0);
$pollsFinished = (int)($data['polls']['finished'] ?? 0);
$pollsActive = max(0, $pollsTotal - $pollsFinished);
$residentsTotal = (int)($data['residents']['total'] ?? 0);
$blocksTotal = (int)($data['residents']['blocks_total'] ?? 0);
$rcTotal = (int)($data['residential_complexes_total'] ?? 0);
$complaintsTotal = (int)($data['complaints']['total'] ?? 0);
$complaintsDone = (int)($data['complaints']['done'] ?? 0);
$suggestionsTotal = (int)($data['suggestions']['total'] ?? 0);
$suggestionsDone = (int)($data['suggestions']['done'] ?? 0);
$suggestionsInWork = max(0, $suggestionsTotal - $suggestionsDone);
$suggestionsProgress = $suggestionsTotal > 0
        ? round($suggestionsDone / $suggestionsTotal * 100)
        : 0;

$serviceTotal = (int)($data['service_requests']['total'] ?? 0);
$serviceDone = (int)($data['service_requests']['done'] ?? 0);
$serviceProgress = $serviceTotal > 0
        ? round($serviceDone / $serviceTotal * 100)
        : 0;

$lastYear = $data['analytics_last_period']['year'] ?? null;
$lastMonth = $data['analytics_last_period']['month'] ?? null;
$lastPeriodText = ($lastYear && $lastMonth)
        ? sprintf('%02d.%d', $lastMonth, $lastYear)
        : 'нет данных';

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>WIRES HOME — Главная</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/include/style.css">
</head>
<body>

<div class="layout">

    <?php include __DIR__ . '/include/header.php'; ?>

    <aside class="sidebar">
        <?php include __DIR__ . '/include/sidebar.php'; ?>
    </aside>

    <main class="content">

        <div class="card card--dashboard">
            <div class="card__header">
                <div>
                    <h2 class="card__title">Добро пожаловать в WIRES HOME</h2>
                </div>

                <form class="card__filter" method="get">
                    <label for="dashboard-rc">ЖК</label>
                    <select id="dashboard-rc" name="residential_complex_id" onchange="this.form.submit()">
                        <option value="">Все ЖК</option>
                        <?php foreach ($complexes as $complex): ?>
                            <?php
                            $id = $complex['id'] ?? null;
                            $name = $complex['name'] ?? '';
                            $selected = ($selectedRcId !== null && (int)$selectedRcId === (int)$id);
                            ?>
                            <option value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>"
                                    <?= $selected ? 'selected' : '' ?>>
                                <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <div class="dashboard-grid dashboard-grid--enhanced">

                <a href="/sections/utilities_view.php" class="stat-card-link">
                    <div class="stat-card stat-card--teal">
                        <div class="stat-card__icon">💳</div>
                        <div class="stat-card__content">
                            <div class="stat-title">Начисления Alseco</div>
                            <div class="stat-value">
                                <?= htmlspecialchars($lastPeriodText, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <div class="stat-note">Последний загруженный период</div>
                        </div>
                    </div>
                </a>

                <a href="/sections/complexes.php" class="stat-card-link">
                    <div class="stat-card stat-card--blue">
                        <div class="stat-card__icon">🏢</div>
                        <div class="stat-card__content">
                            <div class="stat-title">Жилые комплексы</div>
                            <div class="stat-value">
                                <?= number_format($rcTotal, 0, '.', ' ') ?>
                            </div>
                            <div class="stat-note">Все ЖК в обслуживании</div>
                        </div>
                    </div>
                </a>

                <a href="/sections/complexes.php" class="stat-card-link">
                    <div class="stat-card stat-card--indigo">
                        <div class="stat-card__icon">🏠</div>
                        <div class="stat-card__content">
                            <div class="stat-title">Дома</div>
                            <div class="stat-value">
                                <?= number_format($blocksTotal, 0, '.', ' ') ?>
                            </div>
                            <div class="stat-note">Уникальные корпуса / блоки</div>
                        </div>
                    </div>
                </a>

                <a href="/sections/residents.php" class="stat-card-link">
                    <div class="stat-card stat-card--green">
                        <div class="stat-card__icon">👥</div>
                        <div class="stat-card__content">
                            <div class="stat-title">Жители</div>
                            <div class="stat-value">
                                <?= number_format($residentsTotal, 0, '.', ' ') ?>
                            </div>
                            <div class="stat-note">Активные аккаунты жителей</div>
                        </div>
                    </div>
                </a>

                <a href="/sections/complaints.php" class="stat-card-link">
                    <div class="stat-card stat-card--red">
                        <div class="stat-card__icon">⚠️</div>
                        <div class="stat-card__content">
                            <div class="stat-title">Жалобы</div>
                            <div class="stat-value">
                                <?= number_format($complaintsTotal, 0, '.', ' ') ?>
                            </div>
                            <div class="stat-note">
                                В работе:
                                <?= number_format(max(0, $complaintsTotal - $complaintsDone), 0, '.', ' ') ?>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="/sections/voting.php" class="stat-card-link">
                    <div class="stat-card stat-card--teal">
                        <div class="stat-card__icon">📊</div>
                        <div class="stat-card__content">
                            <div class="stat-title">Голосования</div>
                            <div class="stat-value">
                                <?= number_format($pollsActive, 0, '.', ' ') ?>
                            </div>
                            <div class="stat-note">
                                Всего: <?= number_format($pollsTotal, 0, '.', ' ') ?>,
                                завершено: <?= number_format($pollsFinished, 0, '.', ' ') ?>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="/sections/suggestions.php" class="stat-card-link">
                    <div class="stat-card stat-card--wide stat-card--orange">
                        <div class="stat-card__top">
                            <div class="stat-card__icon">💡</div>
                            <div class="stat-card__content">
                                <div class="stat-title">Предложения</div>
                                <div class="stat-value">
                                    <?= number_format($suggestionsTotal, 0, '.', ' ') ?>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card__meta">
                            <span class="stat-pill stat-pill--success">
                                Обработано:
                                <?= number_format($suggestionsDone, 0, '.', ' ') ?>
                            </span>
                            <span class="stat-pill stat-pill--muted">
                                В работе:
                                <?= number_format($suggestionsInWork, 0, '.', ' ') ?>
                            </span>
                        </div>
                        <div class="stat-progress">
                            <div class="stat-progress__bar"
                                 style="width: <?= $suggestionsProgress ?>%;"></div>
                        </div>
                    </div>
                </a>

                <a href="/sections/master_call.php" class="stat-card-link">
                    <div class="stat-card stat-card--wide stat-card--purple">
                        <div class="stat-card__top">
                            <div class="stat-card__icon">🧰</div>
                            <div class="stat-card__content">
                                <div class="stat-title">Заявки на мастера</div>
                                <div class="stat-value">
                                    <?= number_format($serviceTotal, 0, '.', ' ') ?>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card__meta">
                            <span class="stat-pill stat-pill--success">
                                Выполнено:
                                <?= number_format($serviceDone, 0, '.', ' ') ?>
                            </span>
                            <span class="stat-pill stat-pill--muted">
                                В очереди:
                                <?= number_format(max(0, $serviceTotal - $serviceDone), 0, '.', ' ') ?>
                            </span>
                        </div>
                        <div class="stat-progress">
                            <div class="stat-progress__bar"
                                 style="width: <?= $serviceProgress ?>%;"></div>
                        </div>
                    </div>
                </a>


            </div>
        </div>

    </main>

</div>

<?php include __DIR__ . '/include/footer.php'; ?>
</body>
</html>