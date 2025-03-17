<?php
require_once 'database.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $stmt = $pdo->prepare("UPDATE service_requests SET status = 'done' WHERE id = ?");
    $stmt->execute([$id]);

    echo "Статус заявки обновлен на 'done'!";
}
