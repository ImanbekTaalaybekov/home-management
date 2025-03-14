<?php
$bearerToken = 'ВАШ_BEARER_ТОКЕН_ЕСЛИ_НУЖЕН';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_show_all_debts'])) {

        $url = 'http://212.112.105.242:8800/api/debts';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $bearerToken
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);

        curl_close($ch);

        if ($error) {
            echo "<p style='color:red;'>Ошибка при получении списка долгов: {$error}</p>";
        } else {
            echo "<p style='color:green;'>Список долгов (JSON):</p>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
        }
    }

    if (isset($_POST['submit_show_one_debt'])) {
        $debtId = $_POST['debt_id'] ?? '';
        if ($debtId === '') {
            echo "<p style='color:red;'>Не указан ID долга!</p>";
        } else {
            $url = 'http://212.112.105.242:8800/api/debts/' . urlencode($debtId);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $error    = curl_error($ch);
            curl_close($ch);

            if ($error) {
                echo "<p style='color:red;'>Ошибка при получении долга #{$debtId}: {$error}</p>";
            } else {
                echo "<p style='color:green;'>Информация о долге #{$debtId} (JSON):</p>";
                echo "<pre>" . htmlspecialchars($response) . "</pre>";
            }
        }
    }
}
?>