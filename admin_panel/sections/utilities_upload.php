<?php
require __DIR__ . '/../include/auth.php';
require __DIR__ . '/../include/config.php';

$apiBaseUrl = API_BASE_URL;
$token = $_SESSION['auth_token'] ?? null;

$successMessage = null;
$errorMessage   = null;

function apiFileRequest($url, $token, $filePath, $query = '')
{
    $ch = curl_init($url . $query);

    $postData = [
            'file' => new CURLFile($filePath)
    ];

    curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $token,
                    'Accept: application/json'
            ],
    ]);

    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return [$status, json_decode($response, true)];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token) {
    if (isset($_POST['upload_alseco']) && isset($_FILES['file'])) {
        $month = $_POST['month'] ?? '';
        $year  = $_POST['year']  ?? '';

        [$status, $data] = apiFileRequest(
                $apiBaseUrl . '/analytics/upload-data-alseco',
                $token,
                $_FILES['file']['tmp_name'],
                '?month=' . urlencode($month) . '&year=' . urlencode($year)
        );

        if ($status === 200) {
            $successMessage = $data['message'] ?? 'Файл загружен';
        } else {
            $errorMessage = $data['message'] ?? 'Ошибка при загрузке файла';
        }
    }

    if (isset($_POST['import_debt'])) {
        $ch = curl_init($apiBaseUrl . '/debt-import');
        curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => [
                        'Authorization: Bearer ' . $token,
                        'Accept: application/json'
                ],
        ]);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $data     = json_decode($response, true) ?: [];

        if ($status === 200) {
            $successMessage = $data['message'] ?? 'Импорт завершён';
        } else {
            $errorMessage = $data['message'] ?? 'Ошибка импорта задолженностей';
        }

        curl_close($ch);
    }

    $qs = $_SERVER['QUERY_STRING'] ? ('?' . $_SERVER['QUERY_STRING']) : '';
    header('Location: ' . $_SERVER['PHP_SELF'] . $qs);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Загрузка коммунальных данных</title>
    <link rel="stylesheet" href="/include/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .utilities-section-title {
            margin-bottom: 16px;
        }
        .utilities-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
            max-width: 420px;
        }
        .utilities-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .utilities-field {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .utilities-field label {
            font-size: 14px;
            font-weight: 500;
        }
        .utilities-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }
        .utilities-note {
            font-size: 12px;
            color: #6b7280;
        }
        @media (max-width: 600px) {
            .utilities-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="layout">
    <?php include __DIR__ . '/../include/header.php'; ?>
    <aside class="sidebar"><?php include __DIR__ . '/../include/sidebar.php'; ?></aside>

    <main class="content">
        <h1 class="content__title">Загрузка коммунальных услуг</h1>

        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-error"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="card">
            <h2 class="utilities-section-title">Загрузка файла Alseco</h2>
            <form method="post" enctype="multipart/form-data" class="utilities-form">
                <div class="utilities-row">
                    <div class="utilities-field">
                        <label for="month">Месяц</label>
                        <input type="number" id="month" name="month" placeholder="1–12" min="1" max="12" required>
                    </div>
                    <div class="utilities-field">
                        <label for="year">Год</label>
                        <input type="number" id="year" name="year" placeholder="2025" required>
                    </div>
                </div>

                <div class="utilities-field">
                    <label for="file">Файл выгрузки Alseco (.xls / .xlsx)</label>
                    <input type="file" id="file" name="file" accept=".xls,.xlsx" required>
                    <div class="utilities-note">Размер файла может быть большим, загрузка займёт время.</div>
                </div>

                <div class="utilities-actions">
                    <button type="submit" name="upload_alseco" class="button-primary">
                        Загрузить файл
                    </button>
                </div>
            </form>
        </div>

        <div class="card" style="margin-top:32px">
            <h2 class="utilities-section-title">Импорт долгов</h2>
            <p>Импортирует долги из последних загруженных данных Alseco в таблицу задолженности.</p>
            <form method="post" class="utilities-actions">
                <button type="submit" name="import_debt" class="button-danger">
                    Импортировать задолженности
                </button>
            </form>
        </div>

    </main>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar-finance');

        if (sidebar) {
            sidebar.classList.add('sidebar__group--open');
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('menu_utilities_upload');

        if (sidebar) {
            sidebar.classList.add('menu-selected-point');
        }
    });
</script>
</body>
</html>
