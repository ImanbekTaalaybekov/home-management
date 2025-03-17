<?php
require_once 'include/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $stmt = $pdo->prepare("INSERT INTO notifications (type, title, message, residential_complex_id, user_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['type'],
        $_POST['title'],
        $_POST['message'],
        $_POST['residential_complex_id'] ?: null,
        $_POST['user_id'] ?: null
    ]);
    echo "<p style='color:green;'>Уведомление успешно создано!</p>";
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    echo "Уведомление успешно удалено!";
}
