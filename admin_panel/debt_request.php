<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_alseco'])) {
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {

            $fileTmpPath = $_FILES['file']['tmp_name'];
            $fileName    = $_FILES['file']['name'];

            $postFields = [
                'file' => curl_file_create($fileTmpPath, mime_content_type($fileTmpPath), $fileName)
            ];

            $ch = curl_init('http://212.112.105.242:8800/api/upload-debt-data-alseco');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $error    = curl_error($ch);
            curl_close($ch);

            if ($error) {
                echo "<p style='color:red;'>Ошибка при загрузке Alseco: {$error}</p>";
            } else {
                echo "<p style='color:green;'>Ответ сервера (Alseco): {$response}</p>";
            }
        } else {
            echo "<p style='color:red;'>Ошибка загрузки файла (Alseco): " . $_FILES['file']['error'] . "</p>";
        }
    }

    if (isset($_POST['submit_ivc'])) {
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {

            $fileTmpPath = $_FILES['file']['tmp_name'];
            $fileName    = $_FILES['file']['name'];

            $postFields = [
                'file' => curl_file_create($fileTmpPath, mime_content_type($fileTmpPath), $fileName)
            ];

            $ch = curl_init('http://212.112.105.242:8800/api/upload-debt-data-ivc');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $error    = curl_error($ch);
            curl_close($ch);

            if ($error) {
                echo "<p style='color:red;'>Ошибка при загрузке IVC: {$error}</p>";
            } else {
                echo "<p style='color:green;'>Ответ сервера (IVC): {$response}</p>";
            }
        } else {
            echo "<p style='color:red;'>Ошибка загрузки файла (IVC): " . $_FILES['file']['error'] . "</p>";
        }
    }

    if (isset($_POST['submit_import'])) {
        $ch = curl_init('http://212.112.105.242:8800/api/debt-import');
        curl_setopt($ch, CURLOPT_POST, true);
        $postFields = [];
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            echo "<p style='color:red;'>Ошибка при импорте долгов из моделей: {$error}</p>";
        } else {
            echo "<p style='color:green;'>Ответ сервера (Import from models): {$response}</p>";
        }
    }
}
?>