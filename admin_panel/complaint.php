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
    <title>Complaints View</title>
</head>
<body>
<h1>Просмотр жалоб</h1>

<section>
    <h2>Список жалоб</h2>
    <form action="complaint_request.php" method="post">
        <button type="submit" name="submit_show_all_complaints">Показать все жалобы</button>
    </form>
</section>

<hr>

<section>
    <h2>Конкретная жалоба по ID</h2>
    <form action="complaint_request.php" method="post">
        <label for="complaint_id">ID жалобы:</label>
        <input type="text" name="complaint_id" id="complaint_id" placeholder="32" required>
        <button type="submit" name="submit_show_one_complaint">Показать жалобу</button>
    </form>
</section>
</body>
</html>