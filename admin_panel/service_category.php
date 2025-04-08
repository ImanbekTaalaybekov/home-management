<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'include/database.php';

$categories = $pdo->query("SELECT * FROM service_request_categories ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Wires Home</title>
    <link rel="stylesheet" href="include/style.css">
</head>
<body>
<div class="service-category-container">
    <h1>Вызова мастера - Категории</h1>

    <a href="main.php"><button>← Вернуться в меню</button></a>

    <section>
        <h2 id="formTitle">Создать новую категорию</h2>
        <form id="categoryForm">
            <input type="hidden" name="id" id="categoryId">
            <div>
                <label>Техническое название:</label>
                <input type="text" name="name" id="categoryName" required class="service-category-input">
            </div>
            <div>
                <label>Русское название:</label>
                <input type="text" name="name_rus" id="categoryNameRus" required class="service-category-input">
            </div>
            <br>
            <button type="submit">Сохранить</button>
            <button type="button" id="cancelEdit" style="display:none;">Отмена</button>
        </form>
        <div id="result"></div>
    </section>

    <section>
        <h2>Существующие категории</h2>
        <table class="category-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Техническое название</th>
                <th>Русское название</th>
                <th>Дата создания</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $cat): ?>
                <tr id="category-<?= $cat['id'] ?>">
                    <td><?= $cat['id'] ?></td>
                    <td><?= htmlspecialchars($cat['name']) ?></td>
                    <td><?= htmlspecialchars($cat['name_rus']) ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($cat['created_at'])) ?></td>
                    <td>
                        <button onclick="editCategory(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['name']) ?>', '<?= htmlspecialchars($cat['name_rus']) ?>')">Изменить</button>
                        <button onclick="deleteCategory(<?= $cat['id'] ?>)">Удалить</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
    <div class="footer-margin"></div>
</div>

<script>
    document.getElementById('categoryForm').addEventListener('submit', function(e){
        e.preventDefault();
        let formData = new FormData(this);
        let id = document.getElementById('categoryId').value;
        let url = id ? 'service_category_request.php?update=' + id : 'service_category_request.php';

        fetch(url, {
            method: 'POST',
            body: formData
        })
            .then(res => res.text())
            .then(data => {
                document.getElementById('result').innerHTML = data;
                setTimeout(() => location.reload(), 1000);
            });
    });

    function editCategory(id, name, nameRus) {
        document.getElementById('categoryId').value = id;
        document.getElementById('categoryName').value = name;
        document.getElementById('categoryNameRus').value = nameRus;
        document.getElementById('cancelEdit').style.display = 'inline-block';
    }

    function deleteCategory(id){
        if(confirm('Удалить категорию ID ' + id + '?')){
            fetch('service_category_request.php?delete=' + id)
                .then(res => res.text())
                .then(response => {
                    alert(response);
                    document.getElementById('category-' + id).remove();
                });
        }
    }

    document.getElementById('cancelEdit').addEventListener('click', function(){
        document.getElementById('categoryForm').reset();
        document.getElementById('categoryId').value = '';
        this.style.display = 'none';
    });
</script>
</body>
</html>
