<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'include/database.php';

$categories = $pdo->query("SELECT * FROM knowledge_base_categories ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

$recordsStmt = $pdo->query("
    SELECT knowledge_bases.*, knowledge_base_categories.name AS category_name,
           (SELECT path FROM photos WHERE photoable_type = 'App\\Models\\KnowledgeBase' AND photoable_id = knowledge_bases.id LIMIT 1) AS photo_path
    FROM knowledge_bases 
    LEFT JOIN knowledge_base_categories ON knowledge_bases.category_id = knowledge_base_categories.id 
    ORDER BY knowledge_bases.created_at DESC");
$records = $recordsStmt->fetchAll(PDO::FETCH_ASSOC);

function safeField($value){
    return $value ? htmlspecialchars($value) : '—';
}

function safeDate($date){
    return $date ? date('d.m.Y H:i', strtotime($date)) : '—';
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление базами знаний</title>
    <link rel="stylesheet" href="include/style.css">
    <style>
        .preview-img {
            max-width: 50px;
            height: auto;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Управление базами знаний</h1>

    <section>
        <h2><span id="formTitle">Создать запись</span></h2>
        <form id="knowledgeForm" enctype="multipart/form-data">
            <input type="hidden" name="id" id="recordId">
            <div>
                <label>Заголовок:</label>
                <input type="text" name="title" id="recordTitle" required>
            </div>
            <div>
                <label>Категория:</label>
                <select name="category_id" id="recordCategory" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Содержание:</label>
                <textarea name="content" id="recordContent" rows="4" required></textarea>
            </div>
            <div>
                <label>Фотографии (необязательно, можно несколько):</label>
                <input type="file" name="photos[]" multiple>
            </div>
            <button type="submit">Сохранить</button>
            <button type="button" id="cancelEdit" style="display:none;">Отмена</button>
        </form>
        <div id="knowledgeResult"></div>
    </section>

    <section>
        <h2>Категории</h2>
        <form id="categoryForm">
            <input type="hidden" name="category_id" id="categoryId">
            <div>
                <label>Название категории:</label>
                <input type="text" name="category_name" id="categoryName" required>
            </div>
            <button type="submit">Сохранить</button>
            <button type="button" id="cancelCategoryEdit" style="display:none;">Отмена</button>
        </form>
        <div id="categoryResult"></div>

        <table class="category-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $category): ?>
                <tr id="category-<?= $category['id'] ?>">
                    <td><?= $category['id'] ?></td>
                    <td><?= htmlspecialchars($category['name']) ?></td>
                    <td>
                        <button onclick="editCategory(<?= $category['id'] ?>, '<?= htmlspecialchars($category['name']) ?>')">Изменить</button>
                        <button onclick="deleteCategory(<?= $category['id'] ?>)">Удалить</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
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
                <th>Фото</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($records as $record): ?>
                <tr id="record-<?= $record['id'] ?>">
                    <td><?= $record['id'] ?></td>
                    <td><?= safeField($record['title']) ?></td>
                    <td><?= safeField($record['category_name']) ?></td>
                    <td><?= nl2br(safeField($record['content'])) ?></td>
                    <td><?= safeDate($record['created_at']) ?></td>
                    <td>
                        <?php if ($record['photo_path']): ?>
                            <img src="<?= htmlspecialchars('http://212.112.105.242:8800/storage/' . $record['photo_path']) ?>" class="preview-img" alt="Фото">
                        <?php else: ?>
                            Нет
                        <?php endif; ?>
                    </td>
                    <td>
                        <button onclick="editRecord(<?= $record['id'] ?>, '<?= htmlspecialchars($record['title']) ?>', '<?= htmlspecialchars($record['content']) ?>', <?= $record['category_id'] ?>)">Изменить</button>
                        <button onclick="deleteRecord(<?= $record['id'] ?>)">Удалить</button>
                    </td>
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
        let recordId = document.getElementById('recordId').value;
        let url = recordId ? 'knowledge_base_request.php?update=' + recordId : 'knowledge_base_request.php';

        fetch(url, {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(data => {
                document.getElementById('knowledgeResult').innerHTML = data;
                setTimeout(() => location.reload(), 1000);
            });
    });

    document.getElementById('categoryForm').addEventListener('submit', function(e){
        e.preventDefault();
        let formData = new FormData(this);
        let categoryId = document.getElementById('categoryId').value;
        let url = categoryId ? 'knowledge_base_request.php?update_category=' + categoryId : 'knowledge_base_request.php';

        fetch(url, {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(data => {
                document.getElementById('categoryResult').innerHTML = data;
                setTimeout(() => location.reload(), 1000);
            });
    });

    function deleteRecord(id){
        if(confirm('Удалить запись ID ' + id + '?')){
            fetch('knowledge_base_request.php?delete=' + id)
                .then(response => response.text())
                .then(() => document.getElementById('record-' + id).remove());
        }
    }

    function editCategory(id, name) {
        document.getElementById('categoryId').value = id;
        document.getElementById('categoryName').value = name;
        document.getElementById('cancelCategoryEdit').style.display = 'inline-block';
    }

    function deleteCategory(id){
        if(confirm('Удалить категорию ID ' + id + '?')){
            fetch('knowledge_base_request.php?delete_category=' + id)
                .then(response => response.text())
                .then(() => document.getElementById('category-' + id).remove());
        }
    }
</script>
</body>
</html>
