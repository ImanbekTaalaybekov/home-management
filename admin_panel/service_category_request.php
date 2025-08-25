<?php
require_once 'include/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['create_category']) && isset($_POST['name'], $_POST['name_rus'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO service_request_categories (name, name_rus, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([trim($_POST['name']), trim($_POST['name_rus'])]);
        echo "<p style='color:green;'>Категория успешно создана!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['update'])) {
    try {
        $id = (int)$_GET['update'];
        $stmt = $pdo->prepare("UPDATE service_request_categories SET name = ?, name_rus = ? WHERE id = ?");
        $stmt->execute([trim($_POST['name']), trim($_POST['name_rus']), $id]);
        echo "<p style='color:blue;'>Категория успешно обновлена!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM service_request_categories WHERE id = ?");
        $stmt->execute([(int)$_GET['delete']]);
        echo "Категория успешно удалена!";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка при удалении: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['create_master'])) {
    try {
        $name = isset($_POST['master_name']) ? trim($_POST['master_name']) : '';
        $catId = isset($_POST['master_category_id']) ? (int)$_POST['master_category_id'] : 0;

        if ($name === '' || $catId <= 0) {
            throw new Exception('Не заполнены обязательные поля: имя мастера и категория.');
        }

        $exists = $pdo->prepare("SELECT 1 FROM service_request_categories WHERE id = ?");
        $exists->execute([$catId]);
        if (!$exists->fetchColumn()) {
            throw new Exception('Указанная категория не найдена.');
        }

        $stmt = $pdo->prepare("
            INSERT INTO service_request_masters (name, service_request_category_id, created_at)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$name, $catId]);

        echo "<p style='color:green;'>Мастер успешно добавлен!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['update_master'])) {
    try {
        $id = (int)$_GET['update_master'];
        $name = isset($_POST['master_name']) ? trim($_POST['master_name']) : '';
        $catId = isset($_POST['master_category_id']) ? (int)$_POST['master_category_id'] : 0;

        if ($id <= 0 || $name === '' || $catId <= 0) {
            throw new Exception('Не заполнены обязательные поля.');
        }

        $exists = $pdo->prepare("SELECT 1 FROM service_request_categories WHERE id = ?");
        $exists->execute([$catId]);
        if (!$exists->fetchColumn()) {
            throw new Exception('Указанная категория не найдена.');
        }

        $stmt = $pdo->prepare("
            UPDATE service_request_masters
            SET name = ?, service_request_category_id = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $catId, $id]);

        echo "<p style='color:blue;'>Мастер успешно обновлён!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

if (isset($_GET['delete_master'])) {
    try {
        $id = (int)$_GET['delete_master'];
        $stmt = $pdo->prepare("DELETE FROM service_request_masters WHERE id = ?");
        $stmt->execute([$id]);
        echo "Мастер успешно удалён!";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка при удалении: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
