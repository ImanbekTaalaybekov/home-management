<?php
require_once 'include/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['update'])) {
    try {
        $postFields = [
            'type' => $_POST['type'],
            'category' => $_POST['category'],
            'title' => $_POST['title'],
            'message' => $_POST['message'],
            'residential_complex_id' => $_POST['residential_complex_id'] ?: null,
            'user_id' => $_POST['user_id'] ?: null
        ];

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
        curl_close($ch);

        echo $error ? "<p style='color:red;'>Ошибка: $error</p>" : "<p style='color:green;'>Уведомление создано!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка: {$e->getMessage()}</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['update'])) {
    try {
        $id = (int)$_GET['update'];
        $stmt = $pdo->prepare("UPDATE notifications SET type=?, category=?, title=?, message=?, residential_complex_id=?, user_id=? WHERE id=?");
        $stmt->execute([
            $_POST['type'],
            $_POST['category'],
            $_POST['title'],
            $_POST['message'],
            $_POST['residential_complex_id'] ?: null,
            $_POST['user_id'] ?: null,
            $id
        ]);
        echo "<p style='color:blue;'>Уведомление обновлено!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка: {$e->getMessage()}</p>";
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM notifications WHERE id=?")->execute([$id]);
    echo "Уведомление удалено!";
}
