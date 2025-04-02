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

