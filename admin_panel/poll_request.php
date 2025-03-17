<?php
require_once 'database.php';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmtVotes = $pdo->prepare("DELETE FROM poll_votes WHERE poll_id = ?");
    $stmtVotes->execute([$id]);

    $stmtPoll = $pdo->prepare("DELETE FROM polls WHERE id = ?");
    $stmtPoll->execute([$id]);

    echo "Голосование успешно удалено!";
}
