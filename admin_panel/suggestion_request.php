<?php
require_once 'include/database.php';

if (isset($_GET['action']) && $_GET['action'] === 'done' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE suggestions SET status = 'done' WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo "Статус изменен на 'done'";
    } catch (Exception $e) {
        echo "Ошибка: " . $e->getMessage();
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $pdo->beginTransaction();

        $id = (int)$_GET['id'];

        $stmtPhoto = $pdo->prepare("SELECT path FROM photos WHERE photoable_type = 'App\\Models\\Suggestion' AND photoable_id = ?");
        $stmtPhoto->execute([$id]);
        $photos = $stmtPhoto->fetchAll(PDO::FETCH_COLUMN);

        foreach ($photos as $photo) {
            if (file_exists($photo)) {
                unlink($photo);
            }
        }

        $stmtDeletePhoto = $pdo->prepare("DELETE FROM photos WHERE photoable_type = 'App\\Models\\Suggestion' AND photoable_id = ?");
        $stmtDeletePhoto->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM suggestions WHERE id = ?");
        $stmt->execute([$id]);

        $pdo->commit();
        echo "Предложение и его фото успешно удалены!";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<p style='color:red;'>Ошибка при удалении: " . $e->getMessage() . "</p>";
    }
}
