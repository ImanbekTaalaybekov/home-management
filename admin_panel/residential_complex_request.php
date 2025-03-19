<?php
require_once 'include/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && !isset($_GET['update'])) {
    $stmt = $pdo->prepare("INSERT INTO residential_complexes (name, address, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([
        $_POST['name'],
        $_POST['address']
    ]);
    echo "<p style='color:green;'>Жилой комплекс успешно добавлен!</p>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['update'])) {
    $id = (int)$_GET['update'];

    $stmt = $pdo->prepare("UPDATE residential_complexes SET name = ?, address = ? WHERE id = ?");
    $stmt->execute([
        $_POST['name'],
        $_POST['address'],
        $id
    ]);

    echo "<p style='color:blue;'>Жилой комплекс успешно обновлен!</p>";
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM residential_complexes WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    echo "Жилой комплекс успешно удален!";
}
