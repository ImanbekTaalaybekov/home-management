<?php
require_once 'include/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['name_rus']) && !isset($_GET['update'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO service_request_categories (name, name_rus, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([
            $_POST['name'],
            $_POST['name_rus']
        ]);
        echo "<p style='color:green;'>Категория успешно создана!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка: " . $e->getMessage() . "</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['update'])) {
    try {
        $id = (int)$_GET['update'];
        $stmt = $pdo->prepare("UPDATE service_request_categories SET name = ?, name_rus = ? WHERE id = ?");
        $stmt->execute([
            $_POST['name'],
            $_POST['name_rus'],
            $id
        ]);
        echo "<p style='color:blue;'>Категория успешно обновлена!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка: " . $e->getMessage() . "</p>";
    }
}

if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM service_request_categories WHERE id = ?");
        $stmt->execute([(int)$_GET['delete']]);
        echo "Категория успешно удалена!";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка при удалении: " . $e->getMessage() . "</p>";
    }
}
