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
    <title>Notifications Admin Panel</title>
</head>
<body>
<h1>Управление уведомлениями</h1>

<section>
    <h2>Создать уведомление</h2>
    <form action="notification_request.php" method="post" enctype="multipart/form-data">
        <div>
            <label for="type">Тип уведомления:</label>
            <input type="text" name="type" id="type" value="complex" required>
        </div>
        <div>
            <label for="title">Заголовок:</label>
            <input type="text" name="title" id="title" placeholder="Например: Ремонт дорог" required>
        </div>
        <div>
            <label for="message">Сообщение:</label>
            <textarea name="message" id="message" rows="3" placeholder="Будет перекрыт главный вход" required></textarea>
        </div>
        <div>
            <label for="residential_complex_id">ID ЖК (если нужно):</label>
            <input type="text" name="residential_complex_id" id="residential_complex_id" value="2">
        </div>
        <div>
            <label for="user_id">ID пользователя (если нужно адресно):</label>
            <input type="text" name="user_id" id="user_id">
        </div>
        <div>
            <label for="photos">Фотографии (необязательно, можно несколько):</label>
            <input type="file" name="photos[]" id="photos" multiple>
        </div>
        <button type="submit" name="submit_create">Создать уведомление</button>
    </form>
</section>

<hr>

<section>
    <h2>Список уведомлений</h2>
    <form action="notification_request.php" method="post">
        <button type="submit" name="submit_show_all">Показать все уведомления</button>
    </form>
</section>

<hr>

<section>
    <h2>Получить уведомление по ID</h2>
    <form action="notification_request.php" method="post">
        <label for="notification_id">ID уведомления:</label>
        <input type="text" name="notification_id" id="notification_id" required>
        <button type="submit" name="submit_show_one">Показать уведомление</button>
    </form>
</section>
</body>
</html>