<?php
require_once 'include/database.php';


if (isset($_GET['create_sub'])) {
    $ownerId = (int)$_GET['create_sub'];
    $name    = trim($_POST['name'] ?? '');
    $suffix  = trim($_POST['suffix'] ?? '');
    $role    = strtolower(trim($_POST['role'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($ownerId <= 0) {
        http_response_code(400);
        echo "Некорректный владелец.";
        exit;
    }
    if ($name === '') {
        http_response_code(400);
        echo "Имя обязательно.";
        exit;
    }
    if ($suffix === '' || !preg_match('/^[a-zA-Z0-9_\-\.]+$/', $suffix)) {
        http_response_code(400);
        echo "Некорректный суффикс логина. Разрешены буквы/цифры/._-";
        exit;
    }
    if (!in_array($role, ['family','tenant'], true)) {
        http_response_code(400);
        echo "Некорректная роль. Разрешены: family, tenant.";
        exit;
    }
    if ($password === '') {
        http_response_code(400);
        echo "Пароль обязателен.";
        exit;
    }

    $ownerStmt = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
    $ownerStmt->execute([':id' => $ownerId]);
    $owner = $ownerStmt->fetch(PDO::FETCH_ASSOC);
    if (!$owner) {
        http_response_code(404);
        echo "Владелец не найден.";
        exit;
    }
    if (($owner['role'] ?? '') !== 'owner') {
        http_response_code(400);
        echo "Под-пользователя можно создавать только от владельца (role=owner).";
        exit;
    }

    $baseLogin = (string)($owner['login'] ?? '');
    if ($baseLogin === '') {
        http_response_code(400);
        echo "У владельца отсутствует логин, невозможно сформировать логин под-пользователя.";
        exit;
    }
    $newLogin = $baseLogin . '_' . $suffix;

    $check = $pdo->prepare("SELECT 1 FROM users WHERE login = :login LIMIT 1");
    $check->execute([':login' => $newLogin]);
    if ($check->fetchColumn()) {
        http_response_code(409);
        echo "Логин уже занят: " . htmlspecialchars($newLogin, ENT_QUOTES, 'UTF-8');
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $childName = $name;
    if ($childName === '') {
        $childName = trim((string)($owner['name'] ?? ''));
        if ($childName === '') $childName = $baseLogin;
    }

    $stmt = $pdo->prepare("
        INSERT INTO users 
            (name, login, role, personal_account, phone_number, fcm_token, password, block_number, apartment_number, residential_complex_id, created_at, updated_at)
        VALUES
            (:name, :login, :role, :pa, :phone, :fcm, :pass, :block, :apt, :rc_id, NOW(), NOW())
    ");

    $stmt->execute([
        ':name'  => $childName,
        ':login' => $newLogin,
        ':role'  => $role,
        ':pa'    => null,
        ':phone' => null,
        ':fcm'   => null,
        ':pass'  => $hashedPassword,
        ':block' => $owner['block_number'],
        ':apt'   => $owner['apartment_number'],
        ':rc_id' => $owner['residential_complex_id'],
    ]);

    echo "Под-пользователь успешно создан: {$newLogin}";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && !isset($_GET['update'])) {
    $name  = trim($_POST['name'] ?? '');
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $login === '' || $password === '') {
        http_response_code(400);
        echo "<p style='color:red;'>Имя, логин и пароль обязательны.</p>";
        exit;
    }

    $check = $pdo->prepare("SELECT 1 FROM users WHERE login = :login LIMIT 1");
    $check->execute([':login' => $login]);
    if ($check->fetchColumn()) {
        http_response_code(409);
        echo "<p style='color:red;'>Логин уже занят. Укажите другой.</p>";
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("
        INSERT INTO users 
            (name, login, role, personal_account, password, block_number, apartment_number, residential_complex_id, created_at, updated_at) 
        VALUES (?, ?, 'owner', ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute([
        $name,
        $login,
        $_POST['personal_account'] !== '' ? $_POST['personal_account'] : null,
        $hashedPassword,
        $_POST['block_number'] !== '' ? $_POST['block_number'] : null,
        $_POST['apartment_number'] !== '' ? $_POST['apartment_number'] : null,
        $_POST['residential_complex_id'] !== '' ? (int)$_POST['residential_complex_id'] : null,
    ]);

    echo "<p style='color:green;'>Пользователь (owner) успешно создан!</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['update'])) {
    $id = (int)$_GET['update'];

    if (!empty($_POST['password'])) {
        $hashedPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            UPDATE users 
            SET name = ?, personal_account = ?, block_number = ?, apartment_number = ?, residential_complex_id = ?, password = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $_POST['name'],
            $_POST['personal_account'] !== '' ? $_POST['personal_account'] : null,
            $_POST['block_number'] !== '' ? $_POST['block_number'] : null,
            $_POST['apartment_number'] !== '' ? $_POST['apartment_number'] : null,
            $_POST['residential_complex_id'] !== '' ? (int)$_POST['residential_complex_id'] : null,
            $hashedPassword,
            $id
        ]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET name = ?, personal_account = ?, block_number = ?, apartment_number = ?, residential_complex_id = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $_POST['name'],
            $_POST['personal_account'] !== '' ? $_POST['personal_account'] : null,
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
