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
    <title>Debts View</title>
</head>
<body>
<h1>Просмотр долгов</h1>

<section>
    <h2>Список всех долгов</h2>
    <form action="debt_view_request.php" method="post">
        <button type="submit" name="submit_show_all_debts">Показать все долги</button>
    </form>
</section>

<hr>

<section>
    <h2>Конкретный долг по ID</h2>
    <form action="debt_view_request.php" method="post">
        <label for="debt_id">ID долга:</label>
        <input type="text" name="debt_id" id="debt_id" placeholder="1" required>
        <button type="submit" name="submit_show_one_debt">Показать долг</button>
    </form>
</section>
</body>
</html>