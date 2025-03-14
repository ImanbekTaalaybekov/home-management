<?php
$bearerToken = 'ВАШ_BEARER_ТОКЕН';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_show_suggestions'])) {
        $url = 'http://212.112.105.242:8800/api/suggestions';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            echo "<p style='color:red;'>Ошибка при получении списка предложений: {$error}</p>";
        } else {
            echo "<p style='color:green;'>Список предложений (JSON):</p>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
        }
    }
}
?>