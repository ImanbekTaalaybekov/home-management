<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'include/database.php';

$categories = $pdo->query("SELECT * FROM service_request_categories ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

$masters = $pdo->query("
    SELECT m.id, m.name, m.service_request_category_id, m.created_at, c.name_rus AS category_name_rus, c.name AS category_name
    FROM service_request_masters m
    LEFT JOIN service_request_categories c ON c.id = m.service_request_category_id
    ORDER BY m.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
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
                <tr id="category-<?= (int)$cat['id'] ?>">
                    <td><?= (int)$cat['id'] ?></td>
                    <td><?= htmlspecialchars($cat['name']) ?></td>
                    <td><?= htmlspecialchars($cat['name_rus']) ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($cat['created_at'])) ?></td>
                    <td>
                        <button onclick="editCategory(<?= (int)$cat['id'] ?>, '<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($cat['name_rus'], ENT_QUOTES) ?>')">Изменить</button>
                        <button onclick="deleteCategory(<?= (int)$cat['id'] ?>)">Удалить</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section style="margin-top:40px;">
        <h2 id="masterFormTitle">Добавить мастера</h2>
        <form id="masterForm">
            <input type="hidden" name="master_id" id="masterId">
            <div>
                <label>Имя мастера:</label>
                <input type="text" name="master_name" id="masterName" required class="service-category-input">
            </div>
            <div>
                <label>Категория:</label>
                <select name="master_category_id" id="masterCategoryId" required class="service-category-input">
                    <option value="">— Выберите категорию —</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>">
                            <?= htmlspecialchars($cat['name_rus'] ?: $cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <br>
            <button type="submit">Сохранить</button>
            <button type="button" id="masterCancelEdit" style="display:none;">Отмена</button>
        </form>
        <div id="masterResult"></div>
    </section>

    <section>
        <h2>Список мастеров</h2>
        <table class="category-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Имя</th>
                <th>Категория</th>
                <th>Дата создания</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody id="mastersTbody">
            <?php foreach ($masters as $m): ?>
                <tr id="master-<?= (int)$m['id'] ?>">
                    <td><?= (int)$m['id'] ?></td>
                    <td><?= htmlspecialchars($m['name']) ?></td>
                    <td>
                        <?= htmlspecialchars($m['category_name_rus'] ?: $m['category_name'] ?: '—') ?>
                    </td>
                    <td><?= date('d.m.Y H:i', strtotime($m['created_at'])) ?></td>
                    <td>
                        <button onclick="editMaster(
                        <?= (int)$m['id'] ?>,
                                '<?= htmlspecialchars($m['name'], ENT_QUOTES) ?>',
                        <?= (int)($m['service_request_category_id'] ?: 0) ?>
                                )">Изменить</button>
                        <button onclick="deleteMaster(<?= (int)$m['id'] ?>)">Удалить</button>
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
        let url = id ? 'service_category_request.php?update=' + encodeURIComponent(id)
            : 'service_category_request.php?create_category=1';

        fetch(url, { method: 'POST', body: formData })
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
        document.getElementById('formTitle').innerText = 'Редактировать категорию';
    }

    function deleteCategory(id){
        if(confirm('Удалить категорию ID ' + id + '?')){
            fetch('service_category_request.php?delete=' + encodeURIComponent(id))
                .then(res => res.text())
                .then(response => {
                    alert(response);
                    const row = document.getElementById('category-' + id);
                    if (row) row.remove();
                });
        }
    }

    document.getElementById('cancelEdit').addEventListener('click', function(){
        document.getElementById('categoryForm').reset();
        document.getElementById('categoryId').value = '';
        this.style.display = 'none';
        document.getElementById('formTitle').innerText = 'Создать новую категорию';
    });

    document.getElementById('masterForm').addEventListener('submit', function(e){
        e.preventDefault();
        const formData = new FormData(this);
        const id = document.getElementById('masterId').value;
        const url = id ? 'service_category_request.php?update_master=' + encodeURIComponent(id)
            : 'service_category_request.php?create_master=1';

        fetch(url, { method: 'POST', body: formData })
            .then(res => res.text())
            .then(data => {
                document.getElementById('masterResult').innerHTML = data;
                setTimeout(() => location.reload(), 1000);
            });
    });

    function editMaster(id, name, categoryId) {
        document.getElementById('masterId').value = id;
        document.getElementById('masterName').value = name;
        document.getElementById('masterCategoryId').value = categoryId || '';
        document.getElementById('masterCancelEdit').style.display = 'inline-block';
        document.getElementById('masterFormTitle').innerText = 'Редактировать мастера';
    }

    function deleteMaster(id) {
        if (confirm('Удалить мастера ID ' + id + '?')) {
            fetch('service_category_request.php?delete_master=' + encodeURIComponent(id))
                .then(res => res.text())
                .then(response => {
                    alert(response);
                    const row = document.getElementById('master-' + id);
                    if (row) row.remove();
                });
        }
    }

    document.getElementById('masterCancelEdit').addEventListener('click', function(){
        document.getElementById('masterForm').reset();
        document.getElementById('masterId').value = '';
        this.style.display = 'none';
        document.getElementById('masterFormTitle').innerText = 'Добавить мастера';
    });
</script>
</body>
</html>