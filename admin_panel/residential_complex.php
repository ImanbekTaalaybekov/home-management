<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'include/database.php';

$stmt = $pdo->query("SELECT * FROM residential_complexes ORDER BY created_at DESC");
$complexes = $stmt->fetchAll(PDO::FETCH_ASSOC);

function safeDate($date){
    return $date ? date('d.m.Y H:i', strtotime($date)) : '—';
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Жилые Комплексы</title>
    <link rel="stylesheet" href="include/style.css">
</head>
<body>
<div class="container">
    <section class="complex-form-section">
        <h1 class="complex-h1">Жилые Комплексы</h1>
        <h2><span id="formTitle">Добавить ЖК</span></h2>
        <form id="complexForm">
            <input type="hidden" name="id" id="complexId">
            <div class="form-group">
                <label>Название ЖК:</label>
                <input type="text" name="name" id="complexName" required>
            </div>
            <div class="form-group">
                <label>Адрес ЖК:</label>
                <input type="text" name="address" id="complexAddress" required>
            </div>
            <button type="submit">Сохранить</button>
            <button type="button" id="cancelEdit" style="display:none;">Отмена</button>
        </form>
        <div id="complexResult"></div>
    </section>

    <section class="complex-list-section">
        <a href="main.php"><button>← Вернуться в меню</button></a>
        <h2>Существующие ЖК</h2>
        <table class="complex-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Адрес</th>
                <th>Дата создания</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody id="complexList">
            <?php foreach ($complexes as $complex): ?>
                <tr id="complex-<?= $complex['id'] ?>">
                    <td><?= $complex['id'] ?></td>
                    <td><?= htmlspecialchars($complex['name']) ?></td>
                    <td><?= htmlspecialchars($complex['address']) ?></td>
                    <td><?= safeDate($complex['created_at']) ?></td>
                    <td>
                        <button onclick="editComplex(<?= $complex['id'] ?>, '<?= htmlspecialchars($complex['name']) ?>', '<?= htmlspecialchars($complex['address']) ?>')">Изменить</button>
                        <button class="delete-btn" onclick="deleteComplex(<?= $complex['id'] ?>)">Удалить</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>

<script>
    document.getElementById('complexForm').addEventListener('submit', function(e){
        e.preventDefault();
        let formData = new FormData(this);
        let complexId = document.getElementById('complexId').value;
        let url = complexId ? 'residential_complex_request.php?update=' + complexId : 'residential_complex_request.php';

        fetch(url, {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(data => {
                document.getElementById('complexResult').innerHTML = data;
                setTimeout(() => location.reload(), 1000);
            })
            .catch(error => {
                document.getElementById('complexResult').innerHTML = '<p style="color:red;">Ошибка: ' + error + '</p>';
            });
    });

    function deleteComplex(id){
        if(confirm('Удалить ЖК ID ' + id + '?')){
            fetch('residential_complex_request.php?delete=' + id)
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    document.getElementById('complex-' + id).remove();
                })
                .catch(error => alert('Ошибка: ' + error));
        }
    }

    function editComplex(id, name, address) {
        document.getElementById('complexId').value = id;
        document.getElementById('complexName').value = name;
        document.getElementById('complexAddress').value = address;

        document.getElementById('formTitle').textContent = "Изменить ЖК";
        document.getElementById('cancelEdit').style.display = 'inline-block';
    }

    document.getElementById('cancelEdit').addEventListener('click', function(){
        document.getElementById('complexForm').reset();
        document.getElementById('complexId').value = '';
        document.getElementById('formTitle').textContent = "Добавить ЖК";
        this.style.display = 'none';
    });
</script>
</body>
</html>