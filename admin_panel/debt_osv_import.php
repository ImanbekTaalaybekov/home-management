<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'upload') {
    $API_URL = 'https://212.112.105.242:443/api/analytics/upload-data-alseco';

    $month = isset($_POST['month']) ? (int)$_POST['month'] : null;
    $year  = isset($_POST['year'])  ? (int)$_POST['year']  : null;

    if ($month === null || $year === null || $month < 1 || $month > 12 || $year < 2000 || $year > 2100) {
        http_response_code(400);
        echo "<p style='color:red;'>Неверные параметры month/year.</p>";
        exit;
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo "<p style='color:red;'>Ошибка загрузки файла.</p>";
        exit;
    }

    $url = $API_URL . '?month=' . urlencode($month) . '&year=' . urlencode($year);

    $ch = curl_init($url);
    $postFields = [
            'file' => new CURLFile($_FILES['file']['tmp_name'], $_FILES['file']['type'], $_FILES['file']['name'])
    ];
    curl_setopt_array($ch, [
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => $postFields,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_SSL_VERIFYHOST  => false,
            CURLOPT_TIMEOUT         => 120,
    ]);

    $response = curl_exec($ch);
    $err      = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err) {
        http_response_code(500);
        echo "<p style='color:red;'>Ошибка CURL: {$err}</p>";
        exit;
    }

    if ($httpCode >= 200 && $httpCode < 300) {
        echo "<p style='color:green;'>Файл отправлен. Ответ сервера:</p>";
        echo "<pre style='white-space:pre-wrap;word-break:break-word;background:#f7f7f7;padding:8px;border-radius:6px;border:1px solid #ddd;'>"
                . htmlspecialchars($response, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . "</pre>";
    } else {
        echo "<p style='color:red;'>Сервер вернул код {$httpCode}.</p>";
        echo "<pre style='white-space:pre-wrap;word-break:break-word;background:#fff0f0;padding:8px;border-radius:6px;border:1px solid #f5c2c7;'>"
                . htmlspecialchars($response, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . "</pre>";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Wires Home — ОСВ импорт</title>
    <link rel="stylesheet" href="include/style.css">
</head>
<body>
<div class="debt-container">
    <h1 class="debt-title">Загрузка ОСВ (Alseco Analytics)</h1>
    <br>
    <a href="main.php">
        <button>← Вернуться в меню</button>
    </a>

    <section class="debt-upload-section" style="margin-top:16px;">
        <h2 class="debt-subtitle">Отправка файла в метод uploadAlseco</h2>

        <form id="osvForm" enctype="multipart/form-data" class="debt-form">
            <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                <div>
                    <label>Файл (.xlsx):</label><br>
                    <input type="file" name="file" class="debt-file-input" required>
                </div>
                <div>
                    <label>Месяц (1–12):</label><br>
                    <input type="number" name="month" id="month" min="1" max="12" required>
                </div>
                <div>
                    <label>Год:</label><br>
                    <input type="number" name="year" id="year" min="2000" max="2100" required>
                </div>
            </div>

            <button type="submit" class="debt-button" style="margin-top:12px;">Загрузить ОСВ</button>
        </form>

        <div id="osvResult" class="debt-result-box" style="margin-top:12px;"></div>
    </section>
</div>

<script>
    (function putDefaults(){
        const now = new Date();
        const m = now.getMonth() + 1;
        const y = now.getFullYear();
        document.getElementById('month').value = m;
        document.getElementById('year').value  = y;
    })();

    document.getElementById('osvForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const resultBox = document.getElementById('osvResult');
        resultBox.innerHTML = 'Загрузка...';

        const form = document.getElementById('osvForm');
        const formData = new FormData(form);

        fetch('debt_osv_import.php?action=upload', {
            method: 'POST',
            body: formData
        })
            .then(resp => resp.text())
            .then(html => {
                resultBox.innerHTML = html;
            })
            .catch(err => {
                resultBox.innerHTML = '<p style="color:red;">Ошибка: ' + err + '</p>';
            });
    });
</script>
</body>
</html>