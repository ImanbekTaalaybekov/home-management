<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['submit_create'])) {
        $type                  = $_POST['type'] ?? '';
        $title                 = $_POST['title'] ?? '';
        $message               = $_POST['message'] ?? '';
        $residentialComplexId  = $_POST['residential_complex_id'] ?? '';
        $userId                = $_POST['user_id'] ?? '';

        $postFields = [
            'type'                  => $type,
            'title'                 => $title,
            'message'               => $message,
            'residential_complex_id'=> $residentialComplexId,
        ];

        if (!empty($_FILES['photos']['name'][0])) {
            foreach ($_FILES['photos']['tmp_name'] as $index => $tmpPath) {
                if ($_FILES['photos']['error'][$index] === UPLOAD_ERR_OK) {
                    $originalName = $_FILES['photos']['name'][$index];
                    $mimeType     = mime_content_type($tmpPath) ?: 'application/octet-stream';
                    $postFields['photos['.$index.']'] = curl_file_create($tmpPath, $mimeType, $originalName);
                }
            }
        }

        $ch = curl_init('http://212.112.105.242:8800/api/notifications');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            echo "<p style='color:red;'>Ошибка при создании уведомления: {$error}</p>";
        } else {
            echo "<p style='color:green;'>Ответ сервера (create notification): {$response}</p>";
        }
    }


    if (isset($_POST['submit_show_all'])) {
        $ch = curl_init('http://212.112.105.242:8800/api/notifications');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            echo "<p style='color:red;'>Ошибка при получении списка уведомлений: {$error}</p>";
        } else {
            echo "<p style='color:green;'>Список уведомлений (JSON):</p>";
            echo "<pre>".htmlspecialchars($response)."</pre>";
        }
    }

    if (isset($_POST['submit_show_one'])) {
        $notificationId = $_POST['notification_id'] ?? '';
        if (!$notificationId) {
            echo "<p style='color:red;'>Не указан ID уведомления.</p>";
        } else {
            $url = 'http://212.112.105.242:8800/api/notifications/' . urlencode($notificationId);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $error    = curl_error($ch);
            curl_close($ch);

            if ($error) {
                echo "<p style='color:red;'>Ошибка при получении уведомления #{$notificationId}: {$error}</p>";
            } else {
                echo "<p style='color:green;'>Уведомление #{$notificationId} (JSON):</p>";
                echo "<pre>".htmlspecialchars($response)."</pre>";
            }
        }
    }
}
?>