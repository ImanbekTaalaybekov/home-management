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
    <link rel="stylesheet" href="include/style.css">
</head>
<body>
<div class="container">
    <h1>Загрузка долгов</h1>

    <div class="upload-section">
        <h2>1) Загрузка файла Alseco</h2>
        <form id="alsecoForm" enctype="multipart/form-data">
            <input type="file" name="file" required>
            <button type="submit">Загрузить Alseco</button>
        </form>
        <div id="alsecoResult" class="result-box"></div>
    </div>

    <hr>

    <div class="upload-section">
        <h2>2) Загрузка файла IVC</h2>
        <form id="ivcForm" enctype="multipart/form-data">
            <input type="file" name="file" required>
            <button type="submit">Загрузить IVC</button>
        </form>
        <div id="ivcResult" class="result-box"></div>
    </div>

    <hr>

    <div class="upload-section">
        <h2>3) Импорт долгов из моделей</h2>
        <button id="importBtn">Запустить импорт</button>
        <div id="importResult" class="result-box"></div>
    </div>
</div>

<script>
    function sendFile(formId, url, resultId) {
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

    document.getElementById('alsecoForm').addEventListener('submit', function(e){
        e.preventDefault();
        sendFile('alsecoForm', 'debt_request.php?type=alseco', 'alsecoResult');
    });

    document.getElementById('ivcForm').addEventListener('submit', function(e){
        e.preventDefault();
        sendFile('ivcForm', 'debt_request.php?type=ivc', 'ivcResult');
    });

    document.getElementById('importBtn').addEventListener('click', function(){
        const importResult = document.getElementById('importResult');
        importResult.innerHTML = 'Загрузка...';

        fetch('debt_request.php?type=import', { method: 'POST' })
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
