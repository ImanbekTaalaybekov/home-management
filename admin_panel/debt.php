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
    <title>Debts Admin Panel</title>
</head>
<body>
<h1>Загрузка долгов</h1>

<h2>1) Upload Alseco Debt File</h2>
<form action="debt_request.php" method="post" enctype="multipart/form-data">
    <input type="file" name="file" required>
    <button type="submit" name="submit_alseco">Загрузить Alseco</button>
</form>

<hr>

<h2>2) Upload IVC Debt File</h2>
<form action="debt_request.php" method="post" enctype="multipart/form-data">
    <input type="file" name="file" required>
    <button type="submit" name="submit_ivc">Загрузить IVC</button>
</form>

<hr>

<h2>3) Debt Import from models</h2>
<form action="debt_request.php" method="post" enctype="multipart/form-data">
    <button type="submit" name="submit_import">Импортировать</button>
</form>
</body>
</html>