<?php
require_once 'include/database.php';

function safeField($value){ return $value ? htmlspecialchars($value) : '—'; }
function safeDate($date){ return $date ? date('d.m.Y H:i', strtotime($date)) : '—'; }
function humanStatus($status){ return $status === 'done' ? 'Готово' : 'В обработке'; }

function renderMasterOptions(PDO $pdo, $categoryId, $selectedId = null){
    $stmt = $pdo->prepare("
        SELECT id, name 
        FROM service_request_masters 
        WHERE service_request_category_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([(int)$categoryId]);
    $masters = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $html = '<option value="">— Выберите мастера —</option>';
    foreach ($masters as $m) {
        $sel = ($selectedId && (int)$selectedId === (int)$m['id']) ? ' selected' : '';
        $html .= '<option value="'.(int)$m['id'].'"'.$sel.'>'.htmlspecialchars($m['name']).'</option>';
    }
    return $html;
}

if (isset($_GET['filter'])) {
    $where = [];
    $params = [];

    if (!empty($_GET['status'])) {
        $where[] = "sr.status = ?";
        $params[] = $_GET['status'];
    }
    if (!empty($_GET['type'])) {
        $where[] = "c.name_rus = ?";
        $params[] = $_GET['type'];
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $stmt = $pdo->prepare("
        SELECT sr.*, 
               u.name AS user_name, 
               c.id AS category_id,
               c.name AS type_tech,
               c.name_rus AS type_rus,
               m.name AS master_name,
               (SELECT path FROM photos 
                WHERE photoable_type = 'App\\Models\\ServiceRequest' 
                  AND photoable_id = sr.id LIMIT 1) AS photo_path
        FROM service_requests sr
        LEFT JOIN users u ON sr.user_id = u.id
        LEFT JOIN service_request_categories c ON sr.type = c.name
        LEFT JOIN service_request_masters m ON m.id = sr.master_id
        $whereSql
        ORDER BY sr.created_at DESC
    ");
    $stmt->execute($params);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($requests as $r) {
        $id = (int)$r['id'];
        $statusHtml = humanStatus($r['status']);
        $photoTd = $r['photo_path']
            ? "<img src='https://212.112.105.242:443/storage/".htmlspecialchars($r['photo_path'])."' class='preview-img' alt='Фото' onclick='openModal(this)'>"
            : "Нет";

        $selectHtml = '';
        if (!empty($r['category_id'])) {
            $options = renderMasterOptions($pdo, $r['category_id'], $r['master_id'] ?? null);
            $catLabel = htmlspecialchars($r['type_rus'] ?: $r['type_tech'] ?: '—');
            $selectHtml = "
                <div class='assign-wrap'>
                    <select id='assign-select-{$id}'>
                        {$options}
                    </select>
                    <button onclick='assignMaster({$id})'>Назначить</button>
                </div>
                <div class='note'>Категория: {$catLabel}</div>
            ";
        } else {
            $selectHtml = "<span class='note'>категория не определена</span>";
        }

        echo "<tr id='request-{$id}'>
            <td>{$id}</td>
            <td>".safeField($r['user_name'])."</td>
            <td>".safeField($r['type_rus'])."</td>
            <td>".nl2br(safeField($r['description']))."</td>
            <td id='status-{$id}'>{$statusHtml}</td>
            <td id='current-master-{$id}'>".($r['master_name'] ? htmlspecialchars($r['master_name']) : "<span class='note'>не назначен</span>")."</td>
            <td>{$selectHtml}</td>
            <td>".safeDate($r['created_at'])."</td>
            <td>{$photoTd}</td>
            <td>".
            ($r['status'] !== 'done' ? "<button onclick='markDone({$id})'>Готово</button>" : "").
            "<button onclick='deleteRequest({$id})'>Удалить</button>
            </td>
        </tr>";
    }
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'done' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE service_requests SET status = 'done' WHERE id = ?");
        $stmt->execute([(int)$_GET['id']]);
        echo "Статус заявки обновлен на 'done'!";
    } catch (Exception $e) {
        echo "Ошибка: " . $e->getMessage();
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'assign_master' && isset($_GET['id'], $_GET['master_id'])) {
    try {
        $requestId = (int)$_GET['id'];
        $masterId  = (int)$_GET['master_id'];

        $rq = $pdo->prepare("
            SELECT sr.id, sr.type, c.id AS category_id
            FROM service_requests sr
            LEFT JOIN service_request_categories c ON c.name = sr.type
            WHERE sr.id = ?
        ");
        $rq->execute([$requestId]);
        $requestRow = $rq->fetch(PDO::FETCH_ASSOC);

        if (!$requestRow) {
            throw new Exception('Заявка не найдена.');
        }
        if (empty($requestRow['category_id'])) {
            throw new Exception('У заявки не определена категория (type).');
        }

        $ms = $pdo->prepare("SELECT id, service_request_category_id, name FROM service_request_masters WHERE id = ?");
        $ms->execute([$masterId]);
        $masterRow = $ms->fetch(PDO::FETCH_ASSOC);

        if (!$masterRow) {
            throw new Exception('Мастер не найден.');
        }

        if ((int)$masterRow['service_request_category_id'] !== (int)$requestRow['category_id']) {
            throw new Exception('Мастер не принадлежит категории заявки.');
        }

        $upd = $pdo->prepare("UPDATE service_requests SET master_id = ? WHERE id = ?");
        $upd->execute([$masterId, $requestId]);

        echo "Мастер «".safeField($masterRow['name'])."» назначен на заявку ID {$requestId}.";
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
            if ($photo && file_exists($photo)) {
                @unlink($photo);
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