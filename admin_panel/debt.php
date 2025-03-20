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
    <title>Wires Home</title>
    <link rel="stylesheet" href="include/style.css">
</head>
<body>
<div class="debt-container">
    <h1 class="debt-title">Загрузка долгов</h1>
    <br>
    <a href="main.php">
        <button>← Вернуться в меню</button>
    </a>
    <br>
    <section class="debt-upload-section">
        <h2 class="debt-subtitle">1) Загрузка файла Alseco</h2>
        <form id="debtAlsecoForm" enctype="multipart/form-data" class="debt-form">
            <input type="file" name="file" class="debt-file-input" required>
            <button type="submit" class="debt-button">Загрузить Alseco</button>
        </form>
        <div id="debtAlsecoResult" class="debt-result-box"></div>
    </section>

    <hr class="debt-divider">

    <section class="debt-upload-section">
        <h2 class="debt-subtitle">2) Загрузка файла IVC</h2>
        <form id="debtIvcForm" enctype="multipart/form-data" class="debt-form">
            <input type="file" name="file" class="debt-file-input" required>
            <button type="submit" class="debt-button">Загрузить IVC</button>
        </form>
        <div id="debtIvcResult" class="debt-result-box"></div>
    </section>

    <hr class="debt-divider">

    <section class="debt-upload-section">
        <h2 class="debt-subtitle">3) Импорт долгов из моделей</h2>
        <button id="debtImportBtn" class="debt-import-button">Запустить импорт</button>
        <div id="debtImportResult" class="debt-result-box"></div>
    </section>
</div>

<script>
    function sendDebtFile(formId, url, resultId) {
        const form = document.getElementById(formId);
        const resultBox = document.getElementById(resultId);

        const formData = new FormData(form);
        resultBox.innerHTML = 'Загрузка...';

        fetch(url, {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(data => {
                resultBox.innerHTML = data;
            })
            .catch(err => {
                resultBox.innerHTML = '<p style="color:red;">Ошибка: ' + err + '</p>';
            });
    }

    document.getElementById('debtAlsecoForm').addEventListener('submit', function (e) {
        e.preventDefault();
        sendDebtFile('debtAlsecoForm', 'debt_request.php?type=alseco', 'debtAlsecoResult');
    });

    document.getElementById('debtIvcForm').addEventListener('submit', function (e) {
        e.preventDefault();
        sendDebtFile('debtIvcForm', 'debt_request.php?type=ivc', 'debtIvcResult');
    });

    document.getElementById('debtImportBtn').addEventListener('click', function () {
        const importResult = document.getElementById('debtImportResult');
        importResult.innerHTML = 'Загрузка...';

        fetch('debt_request.php?type=import', {method: 'POST'})
            .then(response => response.text())
            .then(data => {
                importResult.innerHTML = data;
            })
            .catch(err => {
                importResult.innerHTML = '<p style="color:red;">Ошибка: ' + err + '</p>';
            });
    });
</script>

</body>
</html>