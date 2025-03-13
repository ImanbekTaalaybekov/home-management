<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админка</title>
    <link rel="stylesheet" href="include/style.css">
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <h2>Меню</h2>
        <ul>
            <li><a href="debt_upload.php">Загрузка задолженностей</a></li>
            <li><a href="notifications.php">Уведомления</a></li>
            <li><a href="knowledge_base.php">База знаний</a></li>
            <li><a href="polls.php">Голосования</a></li>
            <li><a href="complaints.php">Жалобы</a></li>
            <li><a href="logout.php">Выход</a></li>
        </ul>
    </aside>
    <main class="content">
        <h1>Добро пожаловать в админку</h1>
        <p>Выберите действие в меню слева.</p>
    </main>
</div>
</body>
</html>