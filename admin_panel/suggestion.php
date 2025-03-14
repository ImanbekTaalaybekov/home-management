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
    <title>Suggestions</title>
</head>
<body>
<h1>Работа с предложениями</h1>
<section>
    <h2>Список всех предложений</h2>
    <form action="suggestion_request.php" method="post">
        <button type="submit" name="submit_show_suggestions">Показать все предложения</button>
    </form>
</section>
</body>
</html>