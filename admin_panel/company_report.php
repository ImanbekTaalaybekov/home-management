<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'include/database.php';

$complexes = $pdo->query("SELECT id, name FROM residential_complexes ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$perPage = 20;
$total = (int)$pdo->query("SELECT COUNT(*) FROM company_reports")->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$currentPage = isset($_GET['page']) ? max(1, min($totalPages, (int)$_GET['page'])) : 1;
$offset = ($currentPage - 1) * $perPage;

$stmt = $pdo->prepare("
    SELECT cr.*, rc.name AS complex_name
    FROM company_reports cr
    LEFT JOIN residential_complexes rc ON rc.id = cr.residential_complex_id
    ORDER BY cr.created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

function safe($v){ return $v !== null && $v !== '' ? htmlspecialchars($v, ENT_QUOTES, 'UTF-8') : '—'; }
function sdate($d){ return $d ? date('d.m.Y H:i', strtotime($d)) : '—'; }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Company Reports</title>
    <link rel="stylesheet" href="include/style.css">
</head>
<body>
<div class="notification-container">
    <h1>Company Reports</h1>
    <a href="main.php"><button>← Вернуться в меню</button></a>

    <section class="notification-section">
        <h2><span id="formTitle">Создать отчёт</span></h2>
        <form id="reportForm" enctype="multipart/form-data">
            <input type="hidden" name="id" id="reportId">

            <div>
                <label>Заголовок:</label>
                <input type="text" name="title" id="title" required>
            </div>

            <div>
                <label>Сообщение:</label>
                <input type="text" name="message" id="message">
            </div>

            <div>
                <label>Жилой комплекс:</label>
                <select name="residential_complex_id" id="rcId">
                    <option value="">— Общее (для всех) —</option>
                    <?php foreach ($complexes as $c): ?>
                        <option value="<?= safe($c['id']) ?>"><?= safe($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label>PDF-документ:</label>
                <input type="file" name="document" id="document" accept="application/pdf">
            </div>

            <button type="submit">Сохранить</button>
            <button type="button" id="cancelEdit" style="display:none;">Отмена</button>
        </form>

        <div id="result"></div>
    </section>

    <section class="notification-section">
        <h2>Список отчётов</h2>
        <table class="notification-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Заголовок</th>
                <th>Сообщение</th>
                <th>ЖК</th>
                <th>Документ</th>
                <th>Создан</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody id="reportList">
            <?php foreach ($reports as $r): ?>
                <tr id="report-<?= (int)$r['id'] ?>">
                    <td><?= (int)$r['id'] ?></td>
                    <td><?= safe($r['title']) ?></td>
                    <td><?= safe($r['message']) ?></td>
                    <td><?= safe($r['complex_name']) ?></td>
                    <td>
                        <?php if (!empty($r['document'])): ?>
                            <a href="<?= 'https://home-folder.wires.kz/storage/' . safe($r['document']) ?>" target="_blank">Скачать</a>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td><?= sdate($r['created_at']) ?></td>
                    <td>
                        <button onclick="editReport(
                        <?= (int)$r['id'] ?>,
                                '<?= safe($r['title']) ?>',
                                '<?= safe($r['message']) ?>',
                                '<?= safe($r['residential_complex_id'] ?? '') ?>'
                                )">Изменить</button>
                        <button onclick="deleteReport(<?= (int)$r['id'] ?>)">Удалить</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top: 16px;">
            <?php if ($totalPages > 1): ?>
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <?php if ($p == $currentPage): ?>
                        <strong>[<?= $p ?>]</strong>
                    <?php else: ?>
                        <a href="?page=<?= $p ?>">[<?= $p ?>]</a>
                    <?php endif; ?>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
    function editReport(id, title, message, rcId){
        document.getElementById('reportId').value = id;
        document.getElementById('title').value = title;
        document.getElementById('message').value = message === '—' ? '' : message;
        document.getElementById('rcId').value = rcId === '—' ? '' : rcId;
        document.getElementById('formTitle').innerText = 'Редактировать отчёт';
        document.getElementById('cancelEdit').style.display = 'inline-block';
    }

    document.getElementById('cancelEdit').addEventListener('click', function(){
        document.getElementById('reportForm').reset();
        document.getElementById('reportId').value = '';
        document.getElementById('formTitle').innerText = 'Создать отчёт';
        this.style.display = 'none';
        document.getElementById('result').innerHTML = '';
    });

        document.getElementById('reportForm').addEventListener('submit', function(e){
        e.preventDefault();
        const formData = new FormData(this);
        const id = document.getElementById('reportId').value;
        const url = id ? 'company_report_request.php?update=' + encodeURIComponent(id)
        : 'company_report_request.php';

        fetch(url, { method: 'POST', body: formData })
        .then(async r => {
        const text = await r.text();
        document.getElementById('result').innerHTML = text;

        if (r.ok && /Отчёт (создан|обновлён)!/i.test(text)) {
        const btn = document.createElement('button');
        btn.textContent = 'Обновить список';
        btn.onclick = () => location.reload();
        document.getElementById('result').appendChild(document.createElement('br'));
        document.getElementById('result').appendChild(btn);
    }
        console.log('Ответ сервера:', text);
    })
        .catch(err => {
        document.getElementById('result').innerHTML = '<pre style="color:red;">' + err + '</pre>';
        console.error(err);
    });
    });



    function deleteReport(id){
        if (!confirm('Удалить отчёт ID ' + id + '?')) return;
        fetch('company_report_request.php?delete=' + encodeURIComponent(id))
            .then(r => r.text())
            .then(msg => {
                alert(msg);
                const row = document.getElementById('report-' + id);
                if (row) row.remove();
            })
            .catch(err => alert('Ошибка: ' + err));
    }
</script>
</body>
</html>