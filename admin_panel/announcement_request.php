<?php
require_once 'database.php';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    echo "Объявление успешно удалено!";
}
