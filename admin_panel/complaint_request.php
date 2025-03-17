<?php
require_once 'include/database.php';

if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];

    if ($_GET['action'] === 'done') {
        $stmt = $pdo->prepare("UPDATE complaints SET status = 'done' WHERE id = ?");
        $stmt->execute([$id]);
        echo "Статус жалобы обновлен на 'done'!";
    }

    if ($_GET['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM complaints WHERE id = ?");
        $stmt->execute([$id]);
        echo "Жалоба удалена!";
    }
}