<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'include/database.php';

$complexes = $pdo->query("SELECT * FROM residential_complexes")->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT users.*, residential_complexes.name AS complex_name FROM users 
LEFT JOIN residential_complexes ON users.residential_complex_id::bigint = residential_complexes.id 
ORDER BY users.created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Wires Home</title>
    <link rel="stylesheet" href="include/style.css">
</head>
<body>
<div class="container">
    <section class="user-form-section">
        <h1 class="user-h1">Управление пользователями</h1>
        <h2>Создать нового пользователя</h2>
        <form id="userForm">
            <input type="hidden" name="id" id="userId">
            <div class="form-group">
                <label>Имя:</label>
                <input type="text" name="name" id="userName" required>
            </div>
            <div class="form-group">
                <label>Лицевой счет:</label>
                <input type="text" name="personal_account" id="userAccount">
            </div>
            <div class="form-group">
                <label>Телефон:</label>
                <input type="text" name="phone_number" id="userPhone">
            </div>
            <div class="form-group">
                <label>Пароль:</label>
                <input type="password" name="password" id="userPassword" required>
            </div>
            <div class="form-group">
                <label>Жилой комплекс:</label>
                <select name="residential_complex_id" id="userComplex">
                    <option value="">- Выберите ЖК -</option>
                    <?php foreach ($complexes as $complex): ?>
                        <option value="<?= $complex['id'] ?>"><?= htmlspecialchars($complex['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Сохранить</button>
            <button type="button" id="cancelEdit" style="display:none;">Отмена</button>
        </form>
        <div id="userResult"></div>
    </section>

    <section class="user-list-section">
        <a href="main.php"><button>← Вернуться в меню</button></a>
        <h2>Список пользователей</h2>
        <table class="users-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Имя</th>
                <th>Лицевой счет</th>
                <th>Телефон</th>
                <th>Жилой комплекс</th>
                <th>Дата создания</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr id="user-<?= $user['id'] ?>">
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['personal_account']) ?></td>
                    <td><?= htmlspecialchars($user['phone_number']) ?></td>
                    <td><?= htmlspecialchars($user['complex_name'] ?: '-') ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                    <td>
                        <button onclick="editUser(<?= $user['id'] ?>, '<?= $user['name'] ?>', '<?= $user['personal_account'] ?>', '<?= $user['phone_number'] ?>', '<?= $user['residential_complex_id'] ?>')">Изменить</button>
                        <button class="delete-btn" onclick="deleteUser(<?= $user['id'] ?>)">Удалить</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>

<script>
    document.getElementById('userForm').addEventListener('submit', function(e){
        e.preventDefault();
        let formData = new FormData(this);
        let userId = document.getElementById('userId').value;
        let url = userId ? 'user_request.php?update=' + userId : 'user_request.php';

        fetch(url, {
            method: 'POST',
            body: formData
        })
            .then(res => res.text())
            .then(response => {
                document.getElementById('userResult').innerHTML = response;
                setTimeout(() => location.reload(), 1000);
            })
            .catch(err => {
                document.getElementById('userResult').innerHTML = '<p style="color:red;">Ошибка: ' + err + '</p>';
            });
    });

    function deleteUser(id){
        if(confirm('Удалить пользователя ID ' + id + '?')){
            fetch('user_request.php?delete=' + id)
                .then(res => res.text())
                .then(response => {
                    alert(response);
                    document.getElementById('user-' + id).remove();
                })
                .catch(err => alert('Ошибка: ' + err));
        }
    }

    function editUser(id, name, account, phone, complex) {
        document.getElementById('userId').value = id;
        document.getElementById('userName').value = name;
        document.getElementById('userAccount').value = account;
        document.getElementById('userPhone').value = phone;
        document.getElementById('userComplex').value = complex || '';

        document.getElementById('cancelEdit').style.display = 'inline-block';
    }

    document.getElementById('cancelEdit').addEventListener('click', function(){
        document.getElementById('userForm').reset();
        document.getElementById('userId').value = '';
        this.style.display = 'none';
    });
</script>
</body>
</html>