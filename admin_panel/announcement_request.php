<?php
require_once 'include/database.php';

if (isset($_GET['delete'])) {
    try {
        $pdo->beginTransaction();

        $id = (int)$_GET['delete'];

        $stmtPhoto = $pdo->prepare("SELECT path FROM photos WHERE photoable_type = 'App\\Models\\Announcement' AND photoable_id = ?");
        $stmtPhoto->execute([$id]);
        $photos = $stmtPhoto->fetchAll(PDO::FETCH_COLUMN);

        foreach ($photos as $photo) {
            if (file_exists($photo)) {
                unlink($photo);
            }
        }

        $stmtDeletePhoto = $pdo->prepare("DELETE FROM photos WHERE photoable_type = 'App\\Models\\Announcement' AND photoable_id = ?");
        $stmtDeletePhoto->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->execute([$id]);

        $pdo->commit();
        echo "Объявление и его фото успешно удалены!";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<p style='color:red;'>Ошибка при удалении: " . $e->getMessage() . "</p>";
    }
}
