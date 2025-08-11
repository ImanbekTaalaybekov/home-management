<?php
require_once 'include/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['update'])) {
    try {
        $type = $_POST['type'] ?? '';
        $category = $_POST['category'] ?? '';
        $title = $_POST['title'] ?? '';
        $message = $_POST['message'] ?? '';
        $residential_complex_id = $_POST['residential_complex_id'] ?? '';
        $personal_account = trim($_POST['personal_account'] ?? '');

        if ($type === 'complex' && $residential_complex_id === '') {
            http_response_code(422);
            echo "<p style='color:red;'>Для типа 'Для комплекса' нужно выбрать ЖК.</p>";
            exit;
        }
        if ($type === 'personal' && $personal_account === '') {
            http_response_code(422);
            echo "<p style='color:red;'>Для типа 'Личное' требуется лицевой счёт пользователя.</p>";
            exit;
        }

        $postFields = [
            'type' => $type,
            'category' => $category,
            'title' => $title,
            'message' => $message,
        ];

        if ($type === 'complex') {
            $postFields['residential_complex_id'] = $residential_complex_id ?: null;
        } elseif ($type === 'personal') {
            $postFields['personal_account'] = $personal_account;
        }

        if (!empty($_FILES['photos']['name'][0])) {
            foreach ($_FILES['photos']['tmp_name'] as $i => $tmp) {
                if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
                    $postFields["photos[$i]"] = curl_file_create(
                        $tmp,
                        mime_content_type($tmp),
                        $_FILES['photos']['name'][$i]
                    );
                }
            }
        }

        if (!empty($_FILES['document']['tmp_name'])) {
            $postFields['document'] = curl_file_create(
                $_FILES['document']['tmp_name'],
                mime_content_type($_FILES['document']['tmp_name']),
                $_FILES['document']['name']
            );
        }

        $ch = curl_init('https://212.112.105.242/api/notifications');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            echo "<p style='color:red;'>Ошибка cURL: " . htmlspecialchars($error, ENT_QUOTES) . "</p>";
            exit;
        }
        if ($httpCode >= 300) {
            echo "<p style='color:red;'>API вернуло статус {$httpCode}. Ответ: " . htmlspecialchars((string)$response, ENT_QUOTES) . "</p>";
            exit;
        }

        echo "<p style='color:green;'>Уведомление создано!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка: " . htmlspecialchars($e->getMessage(), ENT_QUOTES) . "</p>";
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['update'])) {
    try {
        $id = (int)$_GET['update'];
        $type = $_POST['type'] ?? '';
        $category = $_POST['category'] ?? '';
        $title = $_POST['title'] ?? '';
        $message = $_POST['message'] ?? '';
        $residential_complex_id = $_POST['residential_complex_id'] ?? '';
        $personal_account = trim($_POST['personal_account'] ?? '');

        $user_id = null;
        $rc_id = null;

        if ($type === 'complex') {
            if ($residential_complex_id === '') {
                http_response_code(422);
                echo "<p style='color:red;'>Для типа 'Для комплекса' нужно выбрать ЖК.</p>";
                exit;
            }
            $rc_id = (int)$residential_complex_id;
        } elseif ($type === 'personal') {
            if ($personal_account === '') {
                http_response_code(422);
                echo "<p style='color:red;'>Для типа 'Личное' требуется лицевой счёт пользователя.</p>";
                exit;
            }
            $uStmt = $pdo->prepare("SELECT id FROM users WHERE personal_account = ? LIMIT 1");
            $uStmt->execute([$personal_account]);
            $foundUserId = $uStmt->fetchColumn();
            if (!$foundUserId) {
                http_response_code(422);
                echo "<p style='color:red;'>Пользователь с лицевым счётом не найден.</p>";
                exit;
            }
            $user_id = (int)$foundUserId;
        } else {
            $user_id = null;
            $rc_id = null;
        }

        $stmt = $pdo->prepare("
            UPDATE notifications
               SET type = ?,
                   category = ?,
                   title = ?,
                   message = ?,
                   residential_complex_id = ?,
                   user_id = ?
             WHERE id = ?
        ");
        $stmt->execute([
            $type,
            $category,
            $title,
            $message,
            $rc_id,
            $user_id,
            $id
        ]);

        echo "<p style='color:blue;'>Уведомление обновлено!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка: " . htmlspecialchars($e->getMessage(), ENT_QUOTES) . "</p>";
    }
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM notifications WHERE id = ?")->execute([$id]);
    echo "Уведомление удалено!";
    exit;
}

http_response_code(400);
echo "<p style='color:red;'>Некорректный запрос.</p>";
