<?php
$bearerToken = 'ВАШ_BEARER_ТОКЕН';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_show_announcements'])) {
        $url = 'http://212.112.105.242:8800/api/announcements';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $bearerToken
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            echo "<p style='color:red;'>Ошибка при получении списка объявлений: {$error}</p>";
        } else {
            echo "<p style='color:green;'>Список объявлений (JSON):</p>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
        }
    }

    if (isset($_POST['submit_show_one_announcement'])) {
        $announcementId = $_POST['announcement_id'] ?? '';
        if ($announcementId === '') {
            echo "<p style='color:red;'>Не указан ID объявления!</p>";
        } else {
            $url = 'http://212.112.105.242:8800/api/announcements/' . urlencode($announcementId);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $bearerToken
            ]);

            $response = curl_exec($ch);
            $error    = curl_error($ch);
            curl_close($ch);

            if ($error) {
                echo "<p style='color:red;'>Ошибка при получении объявления #{$announcementId}: {$error}</p>";
            } else {
                echo "<p style='color:green;'>Информация об объявлении #{$announcementId} (JSON):</p>";
                echo "<pre>" . htmlspecialchars($response) . "</pre>";
            }
        }
    }
}
?>