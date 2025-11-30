<?php
require __DIR__ . '/../include/auth.php';
require __DIR__ . '/../include/config.php';

$apiBaseUrl = API_BASE_URL;
$token      = $_SESSION['auth_token'] ?? null;

$currentCategoryId   = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

$categories      = [];
$articles        = [];
$errorMessage    = null;
$successMessage  = null;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>База знаний</title>
    <link rel="stylesheet" href="/include/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<div class="layout">
    <?php include __DIR__ . '/../include/header.php'; ?>
    <aside class="sidebar"><?php include __DIR__ . '/../include/sidebar.php'; ?></aside>

    <main class="content">
        <h1 class="content__title">Камеры</h1>
    </main>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar-information');

        if (sidebar) {
            sidebar.classList.add('sidebar__group--open');
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('menu_cameras');

        if (sidebar) {
            sidebar.classList.add('menu-selected-point');
        }
    });
</script>

<script src="/include/scripts.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var select = document.getElementById('kbCategorySelect');
        if (select) {
            select.addEventListener('change', function () {
                var id = this.value || '';
                if (id) {
                    window.location.href = '?category_id=' + encodeURIComponent(id);
                } else {
                    window.location.href = '?';
                }
            });
        }
    });
</script>
<script src="/include/scripts.js"></script>

</body>
</html>