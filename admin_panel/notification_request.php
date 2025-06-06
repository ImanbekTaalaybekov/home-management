<?php
require_once 'include/database.php';

function safeField($value)
{
    return $value ? htmlspecialchars($value) : '—';
}

function safeDate($date)
{
    return $date ? date('d.m.Y H:i', strtotime($date)) : '—';
}

if (isset($_GET['filter'])) {
    $conditions = [];
    $params = [];

    if (!empty($_GET['type'])) {
        $conditions[] = "notifications.type = ?";
        $params[] = $_GET['type'];
    }

    if (!empty($_GET['complex'])) {
        $conditions[] = "notifications.residential_complex_id = ?";
        $params[] = (int)$_GET['complex'];
    }

    $whereClause = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $stmt = $pdo->prepare("
        SELECT notifications.*, residential_complexes.name AS complex_name, users.name AS user_name
        FROM notifications
        LEFT JOIN residential_complexes ON notifications.residential_complex_id = residential_complexes.id
        LEFT JOIN users ON notifications.user_id = users.id
        $whereClause
        ORDER BY notifications.created_at DESC
    ");
    $stmt->execute($params);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($notifications as $notification) {
        echo "<tr id='notification-{$notification['id']}'>
            <td>{$notification['id']}</td>
            <td>" . safeField($notification['type']) . "</td>
            <td>" . safeField($notification['title']) . "</td>
            <td>" . safeField($notification['message']) . "</td>
            <td>" . safeField($notification['complex_name']) . "</td>
            <td>" . safeField($notification['user_name']) . "</td>
            <td>" . safeDate($notification['created_at']) . "</td>
            <td>
                <button onclick='showNotification({$notification['id']})'>Просмотр</button>
                <button onclick='deleteNotification({$notification['id']})'>Удалить</button>
            </td>
        </tr>";
    }

    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title']) && !isset($_GET['update'])) {
    try {
        $postFields = [
            'type' => $_POST['type'],
            'title' => $_POST['title'],
            'message' => $_POST['message'],
            'residential_complex_id' => $_POST['residential_complex_id'] ?: null,
            'user_id' => $_POST['user_id'] ?: null
        ];

        if (!empty($_FILES['photos']['name'][0])) {
            foreach ($_FILES['photos']['tmp_name'] as $index => $tmpPath) {
                if ($_FILES['photos']['error'][$index] === UPLOAD_ERR_OK) {
                    $originalName = $_FILES['photos']['name'][$index];
                    $mimeType = mime_content_type($tmpPath) ?: 'application/octet-stream';
                    $postFields['photos[' . $index . ']'] = curl_file_create($tmpPath, $mimeType, $originalName);
                }
            }
        }

        if (!empty($_FILES['document']['tmp_name']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
            $docTmpPath = $_FILES['document']['tmp_name'];
            $docOriginalName = $_FILES['document']['name'];
            $docMimeType = mime_content_type($docTmpPath) ?: 'application/pdf';
            $postFields['document'] = curl_file_create($docTmpPath, $docMimeType, $docOriginalName);
        }

        $ch = curl_init('https://212.112.105.242:443/api/notifications');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            echo "<p style='color:red;'>Ошибка при создании уведомления: {$error}</p>";
        } else {
            echo "<p style='color:green;'>Уведомление успешно создано!</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка: " . $e->getMessage() . "</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['update'])) {
    try {
        $id = (int)$_GET['update'];
        $stmt = $pdo->prepare("UPDATE notifications SET type = ?, title = ?, message = ?, residential_complex_id = ?, user_id = ? WHERE id = ?");
        $stmt->execute([
            $_POST['type'],
            $_POST['title'],
            $_POST['message'],
            $_POST['residential_complex_id'] ?: null,
            $_POST['user_id'] ?: null,
            $id
        ]);

        echo "<p style='color:blue;'>Уведомление успешно обновлено!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка: " . $e->getMessage() . "</p>";
    }
}

if (isset($_GET['delete'])) {
    try {
        $pdo->beginTransaction();

        $id = (int)$_GET['delete'];

        $stmtPhoto = $pdo->prepare("SELECT path FROM photos WHERE photoable_type = 'App\\Models\\Notification' AND photoable_id = ?");
        $stmtPhoto->execute([$id]);
        $photos = $stmtPhoto->fetchAll(PDO::FETCH_COLUMN);

        foreach ($photos as $photo) {
            if (file_exists($photo)) {
                unlink($photo);
            }
        }

        $stmtDeletePhoto = $pdo->prepare("DELETE FROM photos WHERE photoable_type = 'App\\Models\\Notification' AND photoable_id = ?");
        $stmtDeletePhoto->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
        $stmt->execute([$id]);

        $pdo->commit();
        echo "Уведомление и его фото успешно удалены!";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<p style='color:red;'>Ошибка при удалении: " . $e->getMessage() . "</p>";
    }
}

if (isset($_GET['show'])) {
    $stmt = $pdo->prepare("SELECT notifications.*, rc.name AS complex_name, u.name AS user_name FROM notifications
        LEFT JOIN residential_complexes rc ON notifications.residential_complex_id = rc.id
        LEFT JOIN users u ON notifications.user_id = u.id WHERE notifications.id=?");
    $stmt->execute([$_GET['show']]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmtPhoto = $pdo->prepare("SELECT path FROM photos WHERE photoable_type='App\\Models\\Notification' AND photoable_id=?");
    $stmtPhoto->execute([$_GET['show']]);
    $photos = $stmtPhoto->fetchAll(PDO::FETCH_COLUMN);

    $data['photos'] = $photos;
    echo json_encode($data);
}
?>
