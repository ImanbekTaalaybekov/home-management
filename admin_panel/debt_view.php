<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit();
}

require_once 'include/database.php';

$where = [];
$params = [];

if (!empty($_GET['search'])) {
    $where[] = "users.name ILIKE :search";
    $params[':search'] = '%' . $_GET['search'] . '%';
}

if (!empty($_GET['complex_id'])) {
    $where[] = "users.residential_complex_id = :complex_id";
    $params[':complex_id'] = (int)$_GET['complex_id'];
}

if (isset($_GET['overdue']) && $_GET['overdue'] == '1') {
    $where[] = "(
        (debts.type = 'Alseco' AND debts.due_date IS NOT NULL AND debts.due_date < CURRENT_DATE - INTERVAL '60 days')
        OR (debts.type = 'Ivc' AND debts.amount > 20000)
    )";
}

$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

$limit = 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

try {
    $stmt = $pdo->prepare("
        SELECT debts.*, 
               users.name AS user_name, 
               users.block_number, 
               users.apartment_number, 
               residential_complexes.name AS complex_name
        FROM debts 
        LEFT JOIN users ON debts.user_id = users.id 
        LEFT JOIN residential_complexes ON users.residential_complex_id = residential_complexes.id
        $whereSql
        ORDER BY debts.due_date DESC 
        LIMIT :limit OFFSET :offset
    ");
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $debts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $countStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM debts 
        LEFT JOIN users ON debts.user_id = users.id
        $whereSql
    ");
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalRows = $countStmt->fetchColumn();
    $totalPages = ceil($totalRows / $limit);
} catch (PDOException $e) {
    die("Ошибка запроса к базе данных: " . $e->getMessage());
}

function daysDifference($date)
{
    $now = new DateTime();
    $due = new DateTime($date);
    return $now->diff($due)->days;
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Просмотр долгов</title>
    <link rel="stylesheet" href="include/style.css">
</head>
<body>
<div class="debt-view-container">
    <h1 class="debt-view-title">Просмотр данных о коммунальных долгах</h1>
    <a href="main.php">
        <button>← Вернуться в меню</button>
    </a>
    <form method="get" style="margin: 20px 0;">
        <input type="text" name="search" placeholder="Поиск по имени"
               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <select name="complex_id" class="debt-view-select-complex">
            <option value="">Все ЖК</option>
            <?php
            $complexes = $pdo->query("SELECT id, name FROM residential_complexes ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($complexes as $complex):
                ?>
                <option value="<?= $complex['id'] ?>" <?= (isset($_GET['complex_id']) && $_GET['complex_id'] == $complex['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($complex['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label class="debt-view-checkbox-label">
            <input class="debt-view-checkbox" type="checkbox" name="overdue"
                   value="1" <?= isset($_GET['overdue']) && $_GET['overdue'] == '1' ? 'checked' : '' ?>>
            Только просроченные
        </label>
        <button type="submit">Фильтр</button>
        <a href="debt_view.php" style="margin-left:10px;">Сбросить</a>
    </form>

    <section class="debt-view-section">
        <table class="debt-view-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Житель</th>
                <th>Тип</th>
                <th>Название</th>
                <th>Сумма</th>
                <th>Дата оплаты</th>
                <th>Блок</th>
                <th>Квартира</th>
                <th>ЖК</th>
                <th>Создано</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($debts as $debt): ?>
                <?php
                $highlightClass = '';
                if (($debt['type'] == 'Alseco' && $debt['due_date'] && daysDifference($debt['due_date']) > 60) || ($debt['type'] == 'Ivc' && $debt['amount'] > 20000)) {
                    $highlightClass = 'debt-highlight';
                }
                ?>
                <tr class="<?= $highlightClass ?>">
                    <td><?= htmlspecialchars($debt['id']) ?></td>
                    <td><?= htmlspecialchars($debt['user_name']) ?></td>
                    <td><?= htmlspecialchars($debt['type']) ?></td>
                    <td><?= htmlspecialchars($debt['name']) ?></td>
                    <td><?= number_format($debt['amount'], 2) ?></td>
                    <td><?= $debt['due_date'] ? date('d.m.Y', strtotime($debt['due_date'])) : '-' ?></td>
                    <td><?= htmlspecialchars($debt['block_number']) ?></td>
                    <td><?= htmlspecialchars($debt['apartment_number']) ?></td>
                    <td><?= $debt['complex_name'] ? htmlspecialchars($debt['complex_name']) : '—' ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($debt['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="debt-pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                   class="<?= $i === $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <div class="footer-margin"></div>
    </section>
</div>
</body>
</html>
