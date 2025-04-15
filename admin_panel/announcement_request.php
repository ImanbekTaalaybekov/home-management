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

if (isset($_GET['filter'])) {
    $where = [];
    $params = [];

    if (!empty($_GET['complex_id'])) {
        $where[] = "announcements.residential_complex_id = ?";
        $params[] = $_GET['complex_id'];
    }

    $whereSql = '';
    if ($where) {
        $whereSql = 'WHERE ' . implode(' AND ', $where);
    }

    $stmt = $pdo->prepare("
        SELECT announcements.*, residential_complexes.name AS complex_name, 
               (SELECT path FROM photos WHERE photoable_type = 'App\\Models\\Announcement' 
                AND photoable_id = announcements.id LIMIT 1) AS photo_path
        FROM announcements
        LEFT JOIN residential_complexes ON announcements.residential_complex_id = residential_complexes.id
        $whereSql
        ORDER BY announcements.created_at DESC
    ");
    $stmt->execute($params);
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($announcements as $announcement) {
        echo "<tr id='announcement-{$announcement['id']}'>
                <td>{$announcement['id']}</td>
                <td>" . safeField($announcement['title']) . "</td>
                <td>" . nl2br(safeField($announcement['content'])) . "</td>
                <td>" . safeField($announcement['complex_name']) . "</td>
                <td>" . safeDate($announcement['created_at']) . "</td>
                <td>";
        if ($announcement['photo_path']) {
            echo "<img src='https://212.112.105.242:443/storage/{$announcement['photo_path']}' class='preview-img' alt='Фото' onclick='openModal(this)'>";
        } else {
            echo "Нет";
        }
        echo "</td>
                <td>
                    <button onclick='deleteAnnouncement({$announcement['id']})'>Удалить</button>
                </td>
            </tr>";
    }
    exit;
}
?>

<?php
require_once 'include/database.php';

if (isset($_GET['delete'])) {
    try {
        $pdo->beginTransaction();

        $id = (int)$_GET['delete'];

        $stmtPhoto = $pdo->prepare("SELECT path FROM photos WHERE photoable_type = 'App\\Models\\Announcement' AND photoable_id = ?");
        $stmtPhoto->execute([$id]);
        $photos = $stmtPhoto->fetchAll(PDO::FETCH_COLUMN);

        foreach ($photos as $photo) {
            if (file_exists($photo)) {
                unlink($photo);
            }
        }

        $stmtDeletePhoto = $pdo->prepare("DELETE FROM photos WHERE photoable_type = 'App\\Models\\Announcement' AND photoable_id = ?");
        $stmtDeletePhoto->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->execute([$id]);

        $pdo->commit();
        echo "Объявление и его фото успешно удалены!";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<p style='color:red;'>Ошибка при удалении: " . $e->getMessage() . "</p>";
    }
}
