<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    function sendApiRequest($url, $postFields) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        return $error ? "<p style='color:red;'>Ошибка: $error</p>" : "<p style='color:green;'>Ответ сервера: $response</p>";
    }

    if ($_GET['type'] === 'alseco' && isset($_FILES['file'])) {
        $file = $_FILES['file'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            echo sendApiRequest('http://212.112.105.242:8800/api/upload-debt-data-alseco', ['file' => new CURLFile($file['tmp_name'])]);
        } else {
            echo "<p style='color:red;'>Ошибка загрузки файла Alseco.</p>";
        }
    }

    elseif ($_GET['type'] === 'ivc' && isset($_FILES['file'])) {
        $file = $_FILES['file'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            echo sendApiRequest('http://212.112.105.242:8800/api/upload-debt-data-ivc', ['file' => new CURLFile($file['tmp_name'])]);
        } else {
            echo "<p style='color:red;'>Ошибка загрузки файла (IVC): " . $file['error'] . "</p>";
        }
    }

    if ($_GET['type'] === 'import') {
        echo sendApiRequest('http://212.112.105.242:8800/api/import-debt-data', []);
    }
}
?>
