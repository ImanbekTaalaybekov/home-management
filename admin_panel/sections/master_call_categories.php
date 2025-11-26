<?php
require __DIR__ . '/../include/auth.php';
require __DIR__ . '/../include/config.php';

$apiBaseUrl = API_BASE_URL;
$token      = $_SESSION['auth_token'] ?? null;

$categories = [];
$masters    = [];
$errorMessage = null;
$successMessage = null;

function apiReq($method, $url, $token, $data = null)
{
    $ch = curl_init($url);
    $headers = [
        'Accept: application/json',
        'Authorization: Bearer ' . $token,
    ];

    if ($data !== null) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers
    ]);

    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$status, json_decode($response, true)];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token) {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_category') {
        [$code, $data] = apiReq('POST', $apiBaseUrl . '/service-requests/categories', $token, [
            'name' => $_POST['name'] ?? '',
            'name_rus' => $_POST['name_rus'] ?? ''
        ]);
        $successMessage = $code === 201 ? 'Категория создана' : ($data['message'] ?? 'Ошибка');
    }

    if ($action === 'update_category') {
        $id = $_POST['id'];
        [$code, $data] = apiReq('PUT', $apiBaseUrl . '/service-requests/categories/'.$id, $token, [
            'name' => $_POST['name'] ?? '',
            'name_rus' => $_POST['name_rus'] ?? ''
        ]);
        $successMessage = $code === 200 ? 'Категория обновлена' : ($data['message'] ?? 'Ошибка');
    }

    if ($action === 'delete_category') {
        $id = $_POST['id'];
        [$code, $data] = apiReq('DELETE', $apiBaseUrl . '/service-requests/categories/'.$id, $token);
        $successMessage = $code === 200 ? 'Категория удалена' : ($data['message'] ?? 'Ошибка');
    }

    if ($action === 'create_master') {
        [$code, $data] = apiReq('POST', $apiBaseUrl . '/service-requests/masters', $token, [
            'name' => $_POST['name'] ?? '',
            'service_request_category_id' => $_POST['category_id'] ?? null
        ]);
        $successMessage = $code === 201 ? 'Мастер добавлен' : ($data['message'] ?? 'Ошибка');
    }

    if ($action === 'update_master') {
        $id = $_POST['id'];
        [$code, $data] = apiReq('PUT', $apiBaseUrl . '/service-requests/masters/'.$id, $token, [
            'name' => $_POST['name'] ?? '',
            'service_request_category_id' => $_POST['category_id'] ?? null
        ]);
        $successMessage = $code === 200 ? 'Мастер обновлён' : ($data['message'] ?? 'Ошибка');
    }

    if ($action === 'delete_master') {
        $id = $_POST['id'];
        [$code, $data] = apiReq('DELETE', $apiBaseUrl . '/service-requests/masters/'.$id, $token);
        $successMessage = $code === 200 ? 'Мастер удалён' : ($data['message'] ?? 'Ошибка');
    }

    $qs = $_SERVER['QUERY_STRING'] ? ('?' . $_SERVER['QUERY_STRING']) : '';
    header('Location: ' . $_SERVER['PHP_SELF'] . $qs);
    exit;
}

if ($token) {
    [$cCode, $cData] = apiReq('GET', $apiBaseUrl . '/service-requests/categories', $token);
    [$mCode, $mData] = apiReq('GET', $apiBaseUrl . '/service-requests/masters', $token);

    $categories = $cData ?? [];
    $masters    = $mData ?? [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мастера и категории</title>
    <link rel="stylesheet" href="/include/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<div class="layout">
    <?php include __DIR__ . '/../include/header.php'; ?>
    <aside class="sidebar"><?php include __DIR__ . '/../include/sidebar.php'; ?></aside>
    <main class="content">

        <h1 class="content__title">Категории вызова мастера</h1>
        <button class="button-primary" onclick="openCategoryModal()">+ Категория</button>

        <ul class="simple-list">
            <?php foreach($categories as $cat): ?>
                <li>
                    <strong><?=htmlspecialchars($cat['name_rus'])?></strong>
                    <small>(<?=htmlspecialchars($cat['name'])?>)</small>
                    <button onclick='editCategory(<?=json_encode($cat)?>)'>✏</button>
                    <form method="post" style="display:inline">
                        <input type="hidden" name="action" value="delete_category">
                        <input type="hidden" name="id" value="<?=$cat['id']?>">
                        <button>✖</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>

        <h1 class="content__title">Мастера</h1>
        <button class="button-primary" onclick="openMasterModal()">+ Мастер</button>

        <table class="admins-table">
            <thead><tr><th>ID</th><th>Имя</th><th>Категория</th><th>Действия</th></tr></thead>
            <tbody>
            <?php foreach($masters as $m): ?>
                <tr>
                    <td><?=$m['id']?></td>
                    <td><?=htmlspecialchars($m['name'])?></td>
                    <td><?=htmlspecialchars($m['category']['name_rus'] ?? '-')?></td>
                    <td>
                        <button onclick='editMaster(<?=json_encode($m)?>)'>Редактировать</button>
                        <form method="post" style="display:inline">
                            <input type="hidden" name="action" value="delete_master">
                            <input type="hidden" name="id" value="<?=$m['id']?>">
                            <button>Удалить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    </main>
</div>

<div class="modal-backdrop" id="categoryModal">
    <div class="modal">
        <form method="post">
            <input type="hidden" name="action" id="catAction">
            <input type="hidden" name="id" id="catId">
            <input type="text" name="name" id="catName" placeholder="Key name">
            <input type="text" name="name_rus" id="catNameRus" placeholder="Название">
            <button>Сохранить</button>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="masterModal">
    <div class="modal">
        <form method="post">
            <input type="hidden" name="action" id="masterAction">
            <input type="hidden" name="id" id="masterId">
            <input type="text" name="name" id="masterName" placeholder="Имя мастера">
            <select name="category_id">
                <option value="">Без категории</option>
                <?php foreach($categories as $c): ?>
                    <option value="<?=$c['id']?>"><?=$c['name_rus']?></option>
                <?php endforeach; ?>
            </select>
            <button>Сохранить</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>

<script>
    function openCategoryModal(){
        document.getElementById('categoryModal').classList.add('modal-open');
        document.getElementById('catAction').value='create_category';
    }
    function editCategory(cat){
        document.getElementById('categoryModal').classList.add('modal-open');
        catAction.value='update_category';catId.value=cat.id;catName.value=cat.name;catNameRus.value=cat.name_rus;
    }
    function openMasterModal(){
        document.getElementById('masterModal').classList.add('modal-open');
        document.getElementById('masterAction').value='create_master';
    }
    function editMaster(m){
        document.getElementById('masterModal').classList.add('modal-open');
        masterAction.value='update_master';masterId.value=m.id;masterName.value=m.name;
    }
</script>
</body>
</html>