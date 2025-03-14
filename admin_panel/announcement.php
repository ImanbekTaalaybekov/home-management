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
    <title>Announcements</title>
</head>
<body>
<h1>Управление объявлениями</h1>
<section>
    <h2>Список объявлений</h2>
    <form action="announcement_request.php" method="post">
        <button type="submit" name="submit_show_announcements">Показать все объявления</button>
    </form>
</section>

<hr>

<section>
    <h2>Показать конкретное объявление</h2>
    <form action="announcement_request.php" method="post">
        <label for="announcement_id">ID объявления:</label>
        <input type="text" name="announcement_id" id="announcement_id" placeholder="5" required>
        <button type="submit" name="submit_show_one_announcement">Показать объявление</button>
    </form>
</section>
</body>
</html>