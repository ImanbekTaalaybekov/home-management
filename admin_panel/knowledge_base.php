<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'database.php';

$categories = $pdo->query("SELECT * FROM knowledge_base_categories")->fetchAll(PDO::FETCH_ASSOC);

$recordsStmt = $pdo->query("SELECT knowledge_bases.*, knowledge_base_categories.name AS category_name FROM knowledge_bases LEFT JOIN knowledge_base_categories ON knowledge_bases.category_id = knowledge_base_categories.id ORDER BY knowledge_bases.created_at DESC");
$records = $recordsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление базами знаний</title>
    <link rel="stylesheet" href="include/style.css">
</head>
<body>
<div class="container">
    <h1>Управление базами знаний</h1>

    <section>
        <h2>Создать запись</h2>
        <form id="knowledgeForm">
            <div>
                <label>Заголовок:</label>
                <input type="text" name="title" required>
            </div>
            <div>
                <label>Категория:</label>
                <select name="category_id" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Содержание:</label>
                <textarea name="content" rows="4" required></textarea>
            </div>
            <button type="submit">Добавить запись</button>
        </form>
        <div id="knowledgeResult"></div>
    </section>

    <section>
        <h2>Записи базы знаний</h2>
        <table class="knowledge-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Заголовок</th>
                <th>Категория</th>
                <th>Содержание</th>
                <th>Создано</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody id="knowledgeList">
            <?php foreach ($records as $record): ?>
                <tr id="record-<?= $record['id'] ?>">
                    <td><?= $record['id'] ?></td>
                    <td><?= htmlspecialchars($record['title']) ?></td>
                    <td><?= htmlspecialchars($record['category_name']) ?></td>
                    <td><?= nl2br(htmlspecialchars($record['content'])) ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($record['created_at'])) ?></td>
                    <td><button onclick="deleteRecord(<?= $record['id'] ?>)">Удалить</button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <a href="main.php">← Вернуться в меню</a>
</div>

<script>
    document.getElementById('knowledgeForm').addEventListener('submit', function(e){
        e.preventDefault();
        let formData = new FormData(this);

        fetch('knowledge_base_request.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(data => {
                document.getElementById('knowledgeResult').innerHTML = data;
                setTimeout(() => location.reload(), 1000);
            })
            .catch(err => {
                document.getElementById('knowledgeResult').innerHTML = '<p style="color:red;">Ошибка: ' + err + '</p>';
            });
    });

    function deleteRecord(id){
        if(confirm('Удалить запись ID ' + id + '?')){
            fetch('knowledge_base_request.php?delete=' + id)
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    document.getElementById('record-' + id).remove();
                })
                .catch(err => alert('Ошибка: ' + err));
        }
    }
</script>
</body>
</html>
