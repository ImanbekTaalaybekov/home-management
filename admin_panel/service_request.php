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
    $where = [];
    $params = [];

    if (!empty($_GET['status'])) {
        $where[] = "service_requests.status = ?";
        $params[] = $_GET['status'];
    }
    if (!empty($_GET['type'])) {
        $where[] = "categories.name_rus = ?";
        $params[] = $_GET['type'];
    }

    $whereSql = '';
    if ($where) {
        $whereSql = 'WHERE ' . implode(' AND ', $where);
    }

    $stmt = $pdo->prepare("
        SELECT service_requests.*, users.name AS user_name, 
               categories.name_rus AS type_rus,
               (SELECT path FROM photos WHERE photoable_type = 'App\\Models\\ServiceRequest' 
                AND photoable_id = service_requests.id LIMIT 1) AS photo_path
        FROM service_requests
        LEFT JOIN users ON service_requests.user_id = users.id
        LEFT JOIN service_request_categories AS categories ON service_requests.type = categories.name
        $whereSql
        ORDER BY service_requests.created_at DESC
    ");
    $stmt->execute($params);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($requests as $request) {
        echo "<tr id='request-{$request['id']}'>
            <td>{$request['id']}</td>
            <td>" . safeField($request['user_name']) . "</td>
            <td>" . safeField($request['type_rus']) . "</td>
            <td>" . nl2br(safeField($request['description'])) . "</td>
            <td id='status-{$request['id']}'>" . humanStatus($request['status']) . "</td>
            <td>" . safeDate($request['created_at']) . "</td>
            <td>";
        if ($request['photo_path']) {
            echo "<img src='https://212.112.105.242:443/storage/{$request['photo_path']}' class='preview-img' alt='Фото' onclick='openModal(this)'>";
        } else {
            echo "Нет";
        }
        echo "</td>
            <td>";
        if ($request['status'] !== 'done') {
            echo "<button onclick='markDone({$request['id']})'>Готово</button>";
        }
        echo "<button onclick='deleteRequest({$request['id']})'>Удалить</button>
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
        $stmt = $pdo->prepare("UPDATE service_requests SET status = 'done' WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo "Статус заявки обновлен на 'done'!";
    } catch (Exception $e) {
        echo "Ошибка: " . $e->getMessage();
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $pdo->beginTransaction();

        $id = (int)$_GET['id'];

        $stmtPhoto = $pdo->prepare("SELECT path FROM photos WHERE photoable_type = 'App\\Models\\ServiceRequest' AND photoable_id = ?");
        $stmtPhoto->execute([$id]);
        $photos = $stmtPhoto->fetchAll(PDO::FETCH_COLUMN);

        foreach ($photos as $photo) {
            if (file_exists($photo)) {
                unlink($photo);
            }
        }

        $stmtDeletePhoto = $pdo->prepare("DELETE FROM photos WHERE photoable_type = 'App\\Models\\ServiceRequest' AND photoable_id = ?");
        $stmtDeletePhoto->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM service_requests WHERE id = ?");
        $stmt->execute([$id]);

        $pdo->commit();
        echo "Заявка и ее фото успешно удалены!";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<p style='color:red;'>Ошибка при удалении: " . $e->getMessage() . "</p>";
    }
}
