<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'database.php';

function translateType($type){
    $translations = [
        'electrician' => 'Электрик',
        'plumber' => 'Сантехник',
        'lift-operator' => 'Лифтер'
    ];
    return $translations[$type] ?? htmlspecialchars($type);
}

$stmt = $pdo->query("SELECT service_requests.*, users.name AS user_name FROM service_requests LEFT JOIN users ON service_requests.user_id = users.id ORDER BY service_requests.created_at DESC");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление заявками на вызов мастера</title>
    <link rel="stylesheet" href="include/style.css">
</head>
<body>
<div class="container">
    <h1>Заявки на вызов мастера</h1>

    <table class="service-requests-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Пользователь</th>
            <th>Тип мастера</th>
            <th>Описание проблемы</th>
            <th>Статус</th>
            <th>Дата заявки</th>
            <th>Действие</th>
        </tr>
        </thead>
        <tbody id="serviceList">
        <?php foreach ($requests as $request): ?>
            <tr id="request-<?= $request['id'] ?>">
                <td><?= $request['id'] ?></td>
                <td><?= htmlspecialchars($request['user_name']) ?></td>
                <td><?= translateType($request['type']) ?></td>
                <td><?= nl2br(htmlspecialchars($request['description'])) ?></td>
                <td id="status-<?= $request['id'] ?>"><?= htmlspecialchars($request['status']) ?></td>
                <td><?= date('d.m.Y H:i', strtotime($request['created_at'])) ?></td>
                <td>
                    <?php if ($request['status'] !== 'done'): ?>
                        <button onclick="markDone(<?= $request['id'] ?>)">Готово</button>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <a href="main.php">← Вернуться в меню</a>
</div>

<script>
    function markAsDone(id) {
        fetch('service_request.php?id=' + id)
            .then(res => res.text())
            .then(response => {
                document.getElementById('status-' + id).innerText = 'done';
                alert(response);
            })
            .catch(err => alert('Ошибка: ' + err));
    }

    document.querySelectorAll('button.mark-done').forEach(button => {
        button.addEventListener('click', function () {
            let id = this.getAttribute('data-id');
            markAsDone(id);
        });
    });
</script>
</body>
</html>
