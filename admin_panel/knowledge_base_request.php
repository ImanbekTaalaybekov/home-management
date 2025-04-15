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
    $params = [];
    $where = '';

    if (!empty($_GET['category_id'])) {
        $where = "WHERE knowledge_bases.category_id = ?";
        $params[] = (int)$_GET['category_id'];
    }

    $stmt = $pdo->prepare("
        SELECT knowledge_bases.*, knowledge_base_categories.name AS category_name,
               (SELECT path FROM photos WHERE photoable_type = 'App\\Models\\KnowledgeBase' AND photoable_id = knowledge_bases.id LIMIT 1) AS photo_path
        FROM knowledge_bases 
        LEFT JOIN knowledge_base_categories ON knowledge_bases.category_id = knowledge_base_categories.id
        $where
        ORDER BY knowledge_bases.created_at DESC
    ");
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($records as $record) {
        echo "<tr id='record-{$record['id']}'>
            <td>{$record['id']}</td>
            <td>" . safeField($record['title']) . "</td>
            <td>" . safeField($record['category_name']) . "</td>
            <td>" . nl2br(safeField($record['content'])) . "</td>
            <td>" . safeDate($record['created_at']) . "</td>
            <td>";
        if ($record['photo_path']) {
            echo "<img src='https://212.112.105.242:443/storage/{$record['photo_path']}' class='preview-img' alt='Фото' onclick='openModal(this)'>";
        } else {
            echo "Нет";
        }
        echo "</td>
            <td>
                <button onclick='editRecord({$record['id']}, `" . safeField($record['title']) . "`, `" . safeField($record['content']) . "`, {$record['category_id']})'>Изменить</button>
                <button onclick='deleteRecord({$record['id']})'>Удалить</button>
            </td>
        </tr>";
    }
    exit;
}
?>


<?php
require_once 'include/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title']) && !isset($_GET['update'])) {
    try {
        $postFields = [
            'title' => $_POST['title'],
            'content' => $_POST['content'],
            'category_id' => $_POST['category_id']
        ];

        if (!empty($_FILES['photos']['name'][0])) {
            foreach ($_FILES['photos']['tmp_name'] as $index => $tmpPath) {
                if ($_FILES['photos']['error'][$index] === UPLOAD_ERR_OK) {
                    $originalName = $_FILES['photos']['name'][$index];
                    $mimeType = mime_content_type($tmpPath) ?: 'application/octet-stream';
                    $postFields['photos[' . $index . ']'] = curl_file_create($tmpPath, $mimeType, $originalName);
                }
            }
        }

        $ch = curl_init('https://212.112.105.242:443/api/knowledge-base/articles');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            echo "<p style='color:red;'>Ошибка при создании записи: {$error}</p>";
        } else {
            echo "<p style='color:green;'>Запись успешно создана!</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка: " . $e->getMessage() . "</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['update'])) {
    try {
        $id = (int)$_GET['update'];
        $stmt = $pdo->prepare("UPDATE knowledge_bases SET title = ?, content = ?, category_id = ? WHERE id = ?");
        $stmt->execute([
            $_POST['title'],
            $_POST['content'],
            $_POST['category_id'],
            $id
        ]);
        echo "<p style='color:blue;'>Запись успешно обновлена!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка: " . $e->getMessage() . "</p>";
    }
}

if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM knowledge_bases WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        echo "Запись успешно удалена!";
    } catch (Exception $e) {
        echo "Ошибка при удалении: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_name']) && !isset($_GET['update_category'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO knowledge_base_categories (name) VALUES (?)");
        $stmt->execute([$_POST['category_name']]);
        echo "<p style='color:green;'>Категория успешно добавлена!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка: " . $e->getMessage() . "</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['update_category'])) {
    try {
        $id = (int)$_GET['update_category'];
        $stmt = $pdo->prepare("UPDATE knowledge_base_categories SET name = ? WHERE id = ?");
        $stmt->execute([
            $_POST['category_name'],
            $id
        ]);
        echo "<p style='color:blue;'>Категория успешно обновлена!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Ошибка: " . $e->getMessage() . "</p>";
    }
}

if (isset($_GET['delete_category'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM knowledge_base_categories WHERE id = ?");
        $stmt->execute([$_GET['delete_category']]);
        echo "Категория успешно удалена!";
    } catch (Exception $e) {
        echo "Ошибка при удалении категории: " . $e->getMessage();
    }
}