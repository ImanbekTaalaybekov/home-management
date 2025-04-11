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

function safeField($value)
{
    return $value ? htmlspecialchars($value) : '—';
}

function safeDate($date)
{
    return $date ? date('d.m.Y H:i', strtotime($date)) : '—';
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
<div id="imageModal" class="modal-overlay">
    <span class="close-modal">&times;</span>
    <div class="modal-content">
        <img id="modalImage" src="" alt="Увеличенное изображение">
    </div>
</div>
<div class="knowledge-base-container">
    <div class="knowledge-base-block-top">
        <div class="footer-margin"></div>
        <section class="knowledge-base-section-top">
            <h1 class="knowledge-base-h1">Управление базами знаний</h1>
            <a href="main.php">
                <button>← Вернуться в меню</button>
            </a>
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
                <br>
                <button type="submit">Сохранить</button>
                <button type="button" id="cancelEdit" style="display:none;">Отмена</button>
            </form>
            <div id="knowledgeResult"></div>
        </section>

        <section class="knowledge-base-top-section">
            <h2>Категории</h2>
            <form id="categoryForm">
                <input type="hidden" name="category_id" id="categoryId">
                <div>
                    <label>Название категории:</label>
                    <input type="text" name="category_name" id="categoryName" required>
                </div>
                <br>
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
                            <button onclick="editCategory(<?= $category['id'] ?>, '<?= htmlspecialchars($category['name']) ?>')">
                                Изменить
                            </button>
                            <button onclick="deleteCategory(<?= $category['id'] ?>)">Удалить</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>
    <section class="knowledge-base-section">
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
                            <img src="<?= htmlspecialchars('https://212.112.105.242:443/storage/' . $record['photo_path']) ?>"
                                 class="preview-img"
                                 alt="Фото"
                                 onclick="openModal(this)">
                        <?php else: ?>
                            Нет
                        <?php endif; ?>
                    </td>
                    <td>
                        <button onclick="editRecord(<?= $record['id'] ?>, '<?= htmlspecialchars($record['title']) ?>', '<?= htmlspecialchars($record['content']) ?>', <?= $record['category_id'] ?>)">
                            Изменить
                        </button>
                        <button onclick="deleteRecord(<?= $record['id'] ?>)">Удалить</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
    <div class="footer-margin"></div>
</div>

<script>
    document.getElementById('knowledgeForm').addEventListener('submit', function (e) {
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

    document.getElementById('categoryForm').addEventListener('submit', function (e) {
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

    function deleteRecord(id) {
        if (confirm('Удалить запись ID ' + id + '?')) {
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

    function deleteCategory(id) {
        if (confirm('Удалить категорию ID ' + id + '?')) {
            fetch('knowledge_base_request.php?delete_category=' + id)
                .then(response => response.text())
                .then(() => document.getElementById('category-' + id).remove());
        }
    }
</script>
<script>
    function openModal(imgElement) {
        const modal = document.getElementById("imageModal");
        const modalImg = document.getElementById("modalImage");

        modal.style.display = "flex";
        modalImg.src = imgElement.src;
    }

    document.querySelector(".close-modal").addEventListener("click", function () {
        document.getElementById("imageModal").style.display = "none";
    });

    document.getElementById("imageModal").addEventListener("click", function (event) {
        if (event.target === this) {
            this.style.display = "none";
        }
    });
</script>
<script>
    document.getElementById('knowledgeForm').addEventListener('submit', function (e) {
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

    document.getElementById('categoryForm').addEventListener('submit', function (e) {
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

    function editRecord(id, title, content, categoryId) {
        document.getElementById('recordId').value = id;
        document.getElementById('recordTitle').value = title;
        document.getElementById('recordContent').value = content;
        document.getElementById('recordCategory').value = categoryId;
        document.getElementById('cancelEdit').style.display = 'inline-block';
    }

    function deleteRecord(id) {
        if (confirm('Удалить запись ID ' + id + '?')) {
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

    function deleteCategory(id) {
        if (confirm('Удалить категорию ID ' + id + '?')) {
            fetch('knowledge_base_request.php?delete_category=' + id)
                .then(response => response.text())
                .then(() => document.getElementById('category-' + id).remove());
        }
    }


    document.getElementById('cancelEdit').addEventListener('click', function() {
        document.getElementById('knowledgeForm').reset();
        document.getElementById('recordId').value = '';
        this.style.display = 'none';
    });

    document.getElementById('cancelCategoryEdit').addEventListener('click', function() {
        document.getElementById('categoryForm').reset();
        document.getElementById('categoryId').value = '';
        this.style.display = 'none';
    });
</script>

</body>
</html>
