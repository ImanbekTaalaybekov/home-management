<?php
$bearerToken = 'ВАШ_BEARER_ТОКЕН';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['submit_show_all_complaints'])) {
        $url = 'http://212.112.105.242:8800/api/complaints';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $bearerToken
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);

        curl_close($ch);

        if ($error) {
            echo "<p style='color:red;'>Ошибка при получении списка жалоб: {$error}</p>";
        } else {
            echo "<p style='color:green;'>Список жалоб (JSON):</p>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
        }
    }

    if (isset($_POST['submit_show_one_complaint'])) {
        $complaintId = $_POST['complaint_id'] ?? '';
        if ($complaintId === '') {
            echo "<p style='color:red;'>Не указан ID жалобы!</p>";
        } else {
            $url = 'http://212.112.105.242:8800/api/complaints/' . urlencode($complaintId);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $error    = curl_error($ch);
            curl_close($ch);

            if ($error) {
                echo "<p style='color:red;'>Ошибка при получении жалобы #{$complaintId}: {$error}</p>";
            } else {
                echo "<p style='color:green;'>Информация о жалобе #{$complaintId} (JSON):</p>";
                echo "<pre>" . htmlspecialchars($response) . "</pre>";
            }
        }
    }
}
?>