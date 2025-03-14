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
    <title>Polls</title>
</head>
<body>
<h1>Управление опросами</h1>

<section>
    <h2>Список опросов</h2>
    <form action="poll_request.php" method="post">
        <button type="submit" name="submit_show_polls">Показать все опросы</button>
    </form>
</section>

<hr>

<section>
    <h2>Конкретный опрос</h2>
    <form action="poll_request.php" method="post">
        <label for="poll_id_single">ID опроса:</label>
        <input type="text" name="poll_id_single" id="poll_id_single" placeholder="3" required>
        <button type="submit" name="submit_show_one_poll">Показать опрос</button>
    </form>
</section>

</body>
</html>