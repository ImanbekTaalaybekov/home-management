<?php
require_once 'include/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['update'])) {
    try {
        $title = $_POST['title'] ?? '';
        $message = $_POST['message'] ?? '';
        $rcId = $_POST['residential_complex_id'] ?? '';

        if ($title === '') {
            http_response_code(422);
            echo "<p style='color:red;'>Нужно указать заголовок.</p>";
            exit;
        }

        $postFields = [
            'title' => $title,
            'message' => $message,
            'residential_complex_id' => $rcId !== '' ? $rcId : null,
        ];

        if (!empty($_FILES['document']['tmp_name'])) {
            $postFields['document'] = curl_file_create(
                $_FILES['document']['tmp_name'],
                mime_content_type($_FILES['document']['tmp_name']),
                $_FILES['document']['name']
            );
        }

        $ch = curl_init('https://212.112.105.242/api/company-report/store');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            echo "<p style='color:red;'>Ошибка cURL: " . htmlspecialchars($error, ENT_QUOTES) . "</p>";
            exit;
        }
        if ($code >= 300) {
            echo "<p style='color:red;'>API вернуло статус {$code}. Ответ: " . htmlspecialchars((string)$response, ENT_QUOTES) . "</p>";
            exit;
        }

        echo "<p style='color:green;'>Отчёт создан!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка: " . htmlspecialchars($e->getMessage(), ENT_QUOTES) . "</p>";
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['update'])) {
    try {
        $id = (int)$_GET['update'];
        $title = $_POST['title'] ?? '';
        $message = $_POST['message'] ?? '';
        $rcId = $_POST['residential_complex_id'] ?? '';

        if ($title === '') {
            http_response_code(422);
            echo "<p style='color:red;'>Нужно указать заголовок.</p>";
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE company_reports
               SET title = ?, message = ?, residential_complex_id = ?
             WHERE id = ?
        ");
        $stmt->execute([
            $title,
            $message,
            ($rcId !== '' ? (int)$rcId : null),
            $id
        ]);

        echo "<p style='color:blue;'>Отчёт обновлён!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка: " . htmlspecialchars($e->getMessage(), ENT_QUOTES) . "</p>";
    }
    exit;
}

if (isset($_GET['delete'])) {
    try {
        $id = (int)$_GET['delete'];
        $pdo->prepare("DELETE FROM company_reports WHERE id = ?")->execute([$id]);
        echo "Отчёт удалён!";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка при удалении: " . htmlspecialchars($e->getMessage(), ENT_QUOTES) . "</p>";
    }
    exit;
}

http_response_code(400);
echo "<p style='color:red;'>Некорректный запрос.</p>";
