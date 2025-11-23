<?php
session_start();

if (!empty($_SESSION['auth_token'])) {
    header('Location: /index.php');
    exit;
}

$apiBaseUrl = 'https://home-folder.wires.kz/api/admin';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Введите логин и пароль';
    } else {

        $payload = json_encode([
                'username' => $username,
                'password' => $password,
                'device'   => 'web-admin'
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init($apiBaseUrl . '/auth');
        curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'Accept: application/json'
                ],
                CURLOPT_POSTFIELDS => $payload
        ]);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($status === 200 && isset($data['auth_token'])) {

            $_SESSION['auth_token'] = $data['auth_token'];
            $_SESSION['admin_id'] = $data['user']['id'];
            $_SESSION['admin_username'] = $data['user']['username'];
            $_SESSION['admin_name'] = $data['user']['name'];
            $_SESSION['admin_role'] = $data['user']['role'];
            $_SESSION['admin_accesses'] = $data['user']['accesses'];
            $_SESSION['admin_client_id'] = $data['user']['client_id'];

            header('Location: /index.php');
            exit;

        } else {
            $error = $data['message'] ?? 'Ошибка авторизации';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>WIRES HOME — вход</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/include/style.css">
</head>
<body class="login-body">

<div class="login-wrapper">
    <div class="login-card">

        <div class="login-logo">WIRES HOME</div>
        <div class="login-subtitle">Административная панель</div>

        <?php if ($error): ?>
            <div class="login-error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="post" class="login-form">

            <div class="login-group">
                <label>Логин</label>
                <input type="text" name="username" required placeholder="Введите логин">
            </div>

            <div class="login-group">
                <label>Пароль</label>
                <input type="password" name="password" required placeholder="Введите пароль">
            </div>

            <button type="submit" class="login-button">
                Войти в панель
            </button>

        </form>

    </div>
</div>

</body>
</html>
