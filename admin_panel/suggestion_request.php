<?php
require_once 'include/database.php';

function safeField($value)
{
    return $value ? htmlspecialchars($value) : '—';
}

function safeDate($date)
{
    return $date ? date('d.m.Y H:i', strtotime($date)) : '—';
}

function humanStatus($status)
{
    return $status === 'done' ? 'Готово' : 'В обработке';
}

if (isset($_GET['filter'])) {
    $params = [];
    $where = '';

    if (!empty($_GET['status'])) {
        $where = "WHERE suggestions.status = ?";
        $params[] = $_GET['status'];
    }

    $stmt = $pdo->prepare("
        SELECT suggestions.*, users.name AS user_name,
               (SELECT path FROM photos WHERE photoable_type = 'App\\Models\\Suggestion' AND photoable_id = suggestions.id LIMIT 1) AS photo_path
        FROM suggestions
        LEFT JOIN users ON suggestions.user_id = users.id
        $where
        ORDER BY suggestions.created_at DESC
    ");
    $stmt->execute($params);
    $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($suggestions as $suggestion) {
        echo "<tr id='suggestion-{$suggestion['id']}'>
            <td>{$suggestion['id']}</td>
            <td>" . safeField($suggestion['user_name']) . "</td>
            <td>" . nl2br(safeField($suggestion['message'])) . "</td>
            <td id='status-{$suggestion['id']}'>" . humanStatus($suggestion['status']) . "</td>
            <td>" . safeDate($suggestion['created_at']) . "</td>
            <td>";
        if ($suggestion['photo_path']) {
            echo "<img src='https://212.112.105.242:443/storage/{$suggestion['photo_path']}' class='preview-img' alt='Фото' onclick='openModal(this)'>";
        } else {
            echo "Нет";
        }
        echo "</td>
            <td>";
        if ($suggestion['status'] !== 'done') {
            echo "<button onclick='markDone({$suggestion['id']})'>Готово</button>";
        }
        echo "<button onclick='deleteSuggestion({$suggestion['id']})'>Удалить</button>
            </td>
        </tr>";
    }
    exit;
}
?>


<?php
require_once 'include/database.php';

if (isset($_GET['action']) && $_GET['action'] === 'done' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE suggestions SET status = 'done' WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo "Статус изменен на 'done'";
    } catch (Exception $e) {
        echo "Ошибка: " . $e->getMessage();
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $pdo->beginTransaction();

        $id = (int)$_GET['id'];

        $stmtPhoto = $pdo->prepare("SELECT path FROM photos WHERE photoable_type = 'App\\Models\\Suggestion' AND photoable_id = ?");
        $stmtPhoto->execute([$id]);
        $photos = $stmtPhoto->fetchAll(PDO::FETCH_COLUMN);

        foreach ($photos as $photo) {
            if (file_exists($photo)) {
                unlink($photo);
            }
        }

        $stmtDeletePhoto = $pdo->prepare("DELETE FROM photos WHERE photoable_type = 'App\\Models\\Suggestion' AND photoable_id = ?");
        $stmtDeletePhoto->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM suggestions WHERE id = ?");
        $stmt->execute([$id]);

        $pdo->commit();
        echo "Предложение и его фото успешно удалены!";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<p style='color:red;'>Ошибка при удалении: " . $e->getMessage() . "</p>";
    }
}
