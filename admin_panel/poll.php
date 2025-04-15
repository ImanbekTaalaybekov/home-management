<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'include/database.php';

$complexes = $pdo->query("SELECT id, name FROM residential_complexes ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("
    SELECT polls.*, residential_complexes.name AS complex_name,
           (SELECT COUNT(*) FROM poll_votes WHERE poll_id = polls.id AND vote = 'yes') AS votes_yes,
           (SELECT COUNT(*) FROM poll_votes WHERE poll_id = polls.id AND vote = 'no') AS votes_no,
           (SELECT COUNT(*) FROM poll_votes WHERE poll_id = polls.id AND vote = 'abstain') AS votes_abstain
    FROM polls 
    LEFT JOIN residential_complexes ON polls.residential_complex_id = residential_complexes.id 
    ORDER BY polls.created_at DESC
");
$polls = $stmt->fetchAll(PDO::FETCH_ASSOC);

function safeField($value)
{
    return $value ? htmlspecialchars($value) : '—';
}

function safeDate($date)
{
    return $date ? date('d.m.Y', strtotime($date)) : '—';
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление голосованиями</title>
    <link rel="stylesheet" href="include/style.css">
</head>
<body>

<div class="poll-container">
    <h1>Голосования</h1>
    <a href="main.php">
        <button>← Вернуться в меню</button>
    </a>

    <div style="margin: 20px 0;">
        <label for="complexFilter">Фильтр по жилому комплексу:</label>
        <select id="complexFilter">
            <option value="">Все ЖК</option>
            <?php foreach ($complexes as $complex): ?>
                <option value="<?= $complex['id'] ?>"><?= safeField($complex['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <section>
        <h2>Создать новое голосование</h2>
        <form id="pollForm">
            <div>
                <label>Заголовок:</label>
                <input type="text" name="title" required>
            </div>
            <div>
                <label>Описание:</label>
                <textarea name="description" rows="3" required></textarea>
            </div>
            <div>
                <label>Жилой комплекс:</label>
                <select name="residential_complex_id">
                    <option value="">— Не выбрано —</option>
                    <?php foreach ($complexes as $complex): ?>
                        <option value="<?= $complex['id'] ?>"><?= safeField($complex['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Дата начала:</label>
                <input type="date" name="start_date" required>
            </div>
            <div>
                <label>Дата окончания:</label>
                <input type="date" name="end_date" required>
            </div>
            <br>
            <button type="submit">Создать</button>
        </form>
        <div id="pollResult"></div>
    </section>

    <table class="polls-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Заголовок</th>
            <th>Описание</th>
            <th>Жилой комплекс</th>
            <th>Дата начала</th>
            <th>Дата окончания</th>
            <th>Создано</th>
            <th>Голоса "Да"</th>
            <th>Голоса "Нет"</th>
            <th>Воздержались</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody id="pollsList">
        <?php foreach ($polls as $poll): ?>
            <tr id="poll-<?= $poll['id'] ?>">
                <td><?= $poll['id'] ?></td>
                <td><?= safeField($poll['title']) ?></td>
                <td><?= nl2br(safeField($poll['description'])) ?></td>
                <td><?= safeField($poll['complex_name']) ?></td>
                <td><?= safeDate($poll['start_date']) ?></td>
                <td><?= safeDate($poll['end_date']) ?></td>
                <td><?= safeDate($poll['created_at']) ?></td>
                <td><?= (int)$poll['votes_yes'] ?></td>
                <td><?= (int)$poll['votes_no'] ?></td>
                <td><?= (int)$poll['votes_abstain'] ?></td>
                <td>
                    <button onclick="deletePoll(<?= $poll['id'] ?>)">Удалить</button>
                    <button onclick="downloadProtocol(<?= $poll['id'] ?>)">Получить протокол</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer-margin"></div>
</div>

<script>
    function deletePoll(id) {
        if (confirm('Удалить голосование ID ' + id + '?')) {
            fetch('poll_request.php?delete=' + id)
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    document.getElementById('poll-' + id).remove();
                })
                .catch(err => alert('Ошибка: ' + err));
        }
    }

    function downloadProtocol(id) {
        const url = `https://212.112.105.242:443/api/polls/protocol/${id}`;
        const link = document.createElement('a');
        link.href = url;
        link.download = `poll_protocol_${id}.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    document.getElementById('pollForm').addEventListener('submit', function (e) {
        e.preventDefault();
        let formData = new FormData(this);

        fetch('poll_request.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.text())
            .then(data => {
                document.getElementById('pollResult').innerHTML = data;
                setTimeout(() => location.reload(), 1000);
            })
            .catch(err => {
                document.getElementById('pollResult').innerHTML = '<p style="color:red;">Ошибка: ' + err + '</p>';
            });
    });

    document.getElementById('complexFilter').addEventListener('change', function () {
        let complexId = this.value;
        let url = 'poll_request.php?filter=1';
        if (complexId) {
            url += '&complex_id=' + complexId;
        }
        fetch(url)
            .then(response => response.text())
            .then(html => {
                document.getElementById('pollsList').innerHTML = html;
            })
            .catch(err => console.error('Ошибка фильтрации:', err));
    });
</script>

</body>
</html>
