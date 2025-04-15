<?php
require_once 'include/database.php';

function safeField($value)
{
    return $value ? htmlspecialchars($value) : '—';
}

function safeDate($date)
{
    return $date ? date('d.m.Y', strtotime($date)) : '—';
}

if (isset($_GET['filter'])) {
    $where = [];
    $params = [];

    if (!empty($_GET['complex_id'])) {
        $where[] = "polls.residential_complex_id = ?";
        $params[] = $_GET['complex_id'];
    }

    $whereSql = '';
    if ($where) {
        $whereSql = 'WHERE ' . implode(' AND ', $where);
    }

    $stmt = $pdo->prepare("
        SELECT polls.*, residential_complexes.name AS complex_name,
               (SELECT COUNT(*) FROM poll_votes WHERE poll_id = polls.id AND vote = 'yes') AS votes_yes,
               (SELECT COUNT(*) FROM poll_votes WHERE poll_id = polls.id AND vote = 'no') AS votes_no,
               (SELECT COUNT(*) FROM poll_votes WHERE poll_id = polls.id AND vote = 'abstain') AS votes_abstain
        FROM polls 
        LEFT JOIN residential_complexes ON polls.residential_complex_id = residential_complexes.id 
        $whereSql
        ORDER BY polls.created_at DESC
    ");
    $stmt->execute($params);
    $polls = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($polls as $poll) {
        echo "<tr id='poll-{$poll['id']}'>
                <td>{$poll['id']}</td>
                <td>" . safeField($poll['title']) . "</td>
                <td>" . nl2br(safeField($poll['description'])) . "</td>
                <td>" . safeField($poll['complex_name']) . "</td>
                <td>" . safeDate($poll['start_date']) . "</td>
                <td>" . safeDate($poll['end_date']) . "</td>
                <td>" . safeDate($poll['created_at']) . "</td>
                <td>" . (int)$poll['votes_yes'] . "</td>
                <td>" . (int)$poll['votes_no'] . "</td>
                <td>" . (int)$poll['votes_abstain'] . "</td>
                <td>
                    <button onclick='deletePoll({$poll['id']})'>Удалить</button>
                    <button onclick='downloadProtocol({$poll['id']})'>Получить протокол</button>
                </td>
            </tr>";
    }
    exit;
}
?>


<?php
require_once 'include/database.php';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmtVotes = $pdo->prepare("DELETE FROM poll_votes WHERE poll_id = ?");
    $stmtVotes->execute([$id]);

    $stmtPoll = $pdo->prepare("DELETE FROM polls WHERE id = ?");
    $stmtPoll->execute([$id]);

    echo "Голосование успешно удалено!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO polls (title, description, residential_complex_id, start_date, end_date, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $_POST['title'],
            $_POST['description'],
            $_POST['residential_complex_id'] ?: null,
            $_POST['start_date'],
            $_POST['end_date']
        ]);

        echo "<p style='color:green;'>Голосование успешно создано!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка: " . $e->getMessage() . "</p>";
    }
}

