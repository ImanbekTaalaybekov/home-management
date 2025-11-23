<?php
require __DIR__.'/include/auth.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>WIRES HOME</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/include/style.css">
</head>
<body>

<div class="layout">

    <?php include __DIR__.'/include/header.php'; ?>

    <aside class="sidebar">
        <?php include __DIR__.'/include/sidebar.php'; ?>
    </aside>

    <main class="content">
        <h1 class="content__title">Добро пожаловать в WIRES HOME</h1>

        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-title">Жилые комплексы</div>
                <div class="stat-value">12</div>
            </div>

            <div class="stat-card">
                <div class="stat-title">Дома</div>
                <div class="stat-value">48</div>
            </div>

            <div class="stat-card">
                <div class="stat-title">Жители</div>
                <div class="stat-value">1 284</div>
            </div>

            <div class="stat-card">
                <div class="stat-title">Обращения</div>
                <div class="stat-value">37</div>
            </div>

            <div class="stat-card">
                <div class="stat-title">Заявки на мастера</div>
                <div class="stat-value">14</div>
            </div>

            <div class="stat-card">
                <div class="stat-title">Жалобы</div>
                <div class="stat-value">6</div>
            </div>

            <div class="stat-card">
                <div class="stat-title">Активные опросы</div>
                <div class="stat-value">3</div>
            </div>
        </div>
    </main>

</div>

<?php include __DIR__.'/include/footer.php'; ?>
