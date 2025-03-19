<?php
require_once 'include/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && !isset($_GET['update'])) {
    $stmt = $pdo->prepare("
        INSERT INTO users (name, personal_account, phone_number, password, residential_complex_id, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $_POST['name'],
        $_POST['personal_account'] ?: null,
        $_POST['phone_number'] ?: null,
        $_POST['password'],
        $_POST['residential_complex_id'] ?: null,
    ]);

    echo "<p style='color:green;'>Пользователь успешно создан!</p>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['update'])) {
    $id = (int)$_GET['update'];

    $stmt = $pdo->prepare("
        UPDATE users SET name = ?, personal_account = ?, phone_number = ?, residential_complex_id = ? WHERE id = ?
    ");
    $stmt->execute([
        $_POST['name'],
        $_POST['personal_account'] ?: null,
        $_POST['phone_number'] ?: null,
        $_POST['residential_complex_id'] ?: null,
        $id
    ]);

    echo "<p style='color:blue;'>Пользователь успешно обновлен!</p>";
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    echo "Пользователь успешно удален!";
}