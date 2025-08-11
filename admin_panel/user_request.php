<?php
require_once 'include/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && !isset($_GET['update'])) {
    $name = trim($_POST['name']);
    if ($name === '' || empty($_POST['password'])) {
        http_response_code(400);
        echo "<p style='color:red;'>Имя и пароль обязательны.</p>";
        exit;
    }

    $hashedPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("
        INSERT INTO users 
            (name, personal_account, phone_number, password, block_number, apartment_number, residential_complex_id, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute([
        $name,
        $_POST['personal_account'] !== '' ? $_POST['personal_account'] : null,
        $_POST['phone_number'] !== '' ? $_POST['phone_number'] : null,
        $hashedPassword,
        $_POST['block_number'] !== '' ? $_POST['block_number'] : null,
        $_POST['apartment_number'] !== '' ? $_POST['apartment_number'] : null,
        $_POST['residential_complex_id'] !== '' ? (int)$_POST['residential_complex_id'] : null,
    ]);

    echo "<p style='color:green;'>Пользователь успешно создан!</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['update'])) {
    $id = (int)$_GET['update'];

    if (!empty($_POST['password'])) {
        $hashedPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            UPDATE users 
            SET name = ?, personal_account = ?, phone_number = ?, block_number = ?, apartment_number = ?, residential_complex_id = ?, password = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $_POST['name'],
            $_POST['personal_account'] !== '' ? $_POST['personal_account'] : null,
            $_POST['phone_number'] !== '' ? $_POST['phone_number'] : null,
            $_POST['block_number'] !== '' ? $_POST['block_number'] : null,
            $_POST['apartment_number'] !== '' ? $_POST['apartment_number'] : null,
            $_POST['residential_complex_id'] !== '' ? (int)$_POST['residential_complex_id'] : null,
            $hashedPassword,
            $id
        ]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET name = ?, personal_account = ?, phone_number = ?, block_number = ?, apartment_number = ?, residential_complex_id = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $_POST['name'],
            $_POST['personal_account'] !== '' ? $_POST['personal_account'] : null,
            $_POST['phone_number'] !== '' ? $_POST['phone_number'] : null,
            $_POST['block_number'] !== '' ? $_POST['block_number'] : null,
            $_POST['apartment_number'] !== '' ? $_POST['apartment_number'] : null,
            $_POST['residential_complex_id'] !== '' ? (int)$_POST['residential_complex_id'] : null,
            $id
        ]);
    }

    echo "<p style='color:blue;'>Пользователь успешно обновлён!</p>";
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    echo "Пользователь успешно удалён!";
    exit;
}

http_response_code(400);
echo "<p style='color:red;'>Некорректный запрос.</p>";
