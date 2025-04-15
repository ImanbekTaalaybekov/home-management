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
        $where = "WHERE complaints.status = ?";
        $params[] = $_GET['status'];
    }

    $stmt = $pdo->prepare("
        SELECT complaints.*, users.name AS user_name,
               (SELECT path FROM photos WHERE photoable_type = 'App\\Models\\Complaint' AND photoable_id = complaints.id LIMIT 1) AS photo_path
        FROM complaints
        LEFT JOIN users ON complaints.user_id = users.id
        $where
        ORDER BY complaints.created_at DESC
    ");
    $stmt->execute($params);
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($complaints as $complaint) {
        echo "<tr id='complaint-{$complaint['id']}'>
            <td>{$complaint['id']}</td>
            <td>" . safeField($complaint['user_name']) . "</td>
            <td>" . nl2br(safeField($complaint['message'])) . "</td>
            <td id='status-{$complaint['id']}'>" . humanStatus($complaint['status']) . "</td>
            <td>" . safeDate($complaint['created_at']) . "</td>
            <td>";
        if ($complaint['photo_path']) {
            echo "<img src='https://212.112.105.242:443/storage/{$complaint['photo_path']}' class='preview-img' alt='Фото' onclick='openModal(this)'>";
        } else {
            echo "Нет";
        }
        echo "</td>
            <td>";
        if ($complaint['status'] !== 'done') {
            echo "<button onclick='markDone({$complaint['id']})'>Готово</button>";
        }
        echo "<button onclick='deleteComplaint({$complaint['id']})'>Удалить</button>
            </td>
        </tr>";
    }
    exit;
}
?>

<?php
require_once 'include/database.php';

if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];

    if ($_GET['action'] === 'done') {
        $stmt = $pdo->prepare("UPDATE complaints SET status = 'done' WHERE id = ?");
        $stmt->execute([$id]);
        echo "Статус жалобы обновлен на 'done'!";
    }

    if ($_GET['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM complaints WHERE id = ?");
        $stmt->execute([$id]);
        echo "Жалоба удалена!";
    }
}