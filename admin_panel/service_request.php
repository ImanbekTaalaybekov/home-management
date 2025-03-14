<?php
$bearerToken = 'ВАШ_BEARER_ТОКЕН';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['submit_show_requests'])) {

        $url = 'http://212.112.105.242:8800/api/service-requests';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $bearerToken
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            echo "<p style='color:red;'>Ошибка при получении списка заявок: {$error}</p>";
        } else {
            echo "<p style='color:green;'>Список заявок (JSON):</p>";
            echo '<pre>' . htmlspecialchars($response) . '</pre>';
        }
    }
}
?>