<?php
$bearerToken = 'ВАШ_BEARER_ТОКЕН';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['submit_show_polls'])) {
        $url = 'http://212.112.105.242:8800/api/polls';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $bearerToken
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            echo "<p style='color:red;'>Ошибка при получении списка опросов: {$error}</p>";
        } else {
            echo "<p style='color:green;'>Список опросов (JSON):</p>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
        }
    }

    if (isset($_POST['submit_show_one_poll'])) {
        $pollId = $_POST['poll_id_single'] ?? '';
        if (!$pollId) {
            echo "<p style='color:red;'>Не указан ID опроса.</p>";
        } else {
            $url = 'http://212.112.105.242:8800/api/polls/' . urlencode($pollId);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $bearerToken
            ]);

            $response = curl_exec($ch);
            $error    = curl_error($ch);
            curl_close($ch);

            if ($error) {
                echo "<p style='color:red;'>Ошибка при получении опроса #{$pollId}: {$error}</p>";
            } else {
                echo "<p style='color:green;'>Опрос #{$pollId} (JSON):</p>";
                echo "<pre>" . htmlspecialchars($response) . "</pre>";
            }
        }
    }
}
?>