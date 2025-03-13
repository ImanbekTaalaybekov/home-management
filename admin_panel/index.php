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
    <h2>Вход в админку</h2>
    <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Логин" required>
        <input type="password" name="password" placeholder="Пароль" required>
        <button type="submit">Войти</button>
    </form>
</div>
<script src="include/scripts.js"></script>
</body>
</html>