<?php require_once 'include/auth.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход в админку</title>
    <link rel="stylesheet" href="include/style.css">
</head>
<body>
<div class="login-container">
    <div class="login-container-logo">
        <img src="include/logo.png" alt="Логотип" class="login-logo">
    </div>
    <h2 class="login-title">Вход в админку</h2>
    <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Логин" required>
        <input type="password" name="password" placeholder="Пароль" required class="login-input">
        <button type="submit" class="login-button">Войти</button>
    </form>
</div>
</body>
</html>