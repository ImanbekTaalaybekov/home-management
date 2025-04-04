<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'include/database.php';

$complexes = $pdo->query("SELECT * FROM residential_complexes ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная - Wires Home</title>
    <link rel="stylesheet" href="include/style.css">
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div class="login-container-logo">
            <img src="include/logo.png" alt="Логотип" class="login-logo">
        </div>
        <h2>Меню</h2>
        <ul>
            <li><a href="user.php">Пользователи</a></li>
            <li><a href="residential_complex.php">Жилые комплексы</a></li>
            <li><a href="debt.php">Загрузка данных коммунальных услуг</a></li>
            <li><a href="debt_view.php">Просмотр данных коммунальных услуг</a></li>
            <li><a href="notification.php">Управление уведомлениями</a></li>
            <li><a href="knowledge_base.php">Управление базами знаний</a></li>
            <li><a href="complaint.php">Жалобы</a></li>
            <li><a href="suggestion.php">Предложения</a></li>
            <li><a href="service.php">Вызов мастера</a></li>
            <li><a href="announcement.php">Объявления</a></li>
            <li><a href="poll.php">Голосования</a></li>
            <li><a href="logout.php">Выход</a></li>
        </ul>
    </aside>

    <main class="dashboard-content">
        <div class="dashboard-header">
            <h1>Главная страница</h1>
            <select id="complexFilter">
                <option value="">Все ЖК</option>
                <?php foreach ($complexes as $complex): ?>
                    <option value="<?= $complex['id'] ?>"><?= htmlspecialchars($complex['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="dashboardCards" class="dashboard-cards">
        </div>
    </main>
</div>

<script>
    function loadDashboard(complexId = '') {
        fetch('dashboard_request.php' + (complexId ? '?complex_id=' + complexId : ''))
            .then(response => response.json())
            .then(data => {
                let cardsHtml = `
                    <div class="dashboard-card">
                        <div class="dashboard-card-icon">👤</div>
                        <div class="dashboard-card-info">
                            <h3>Пользователей в обслуживании:</h3>
                            <p>${data.users}</p>
                            <a href="user.php" class="dashboard-card-btn">Перейти →</a>
                        </div>
                    </div>
                    <div class="dashboard-card">
                        <div class="dashboard-card-icon">🏢</div>
                        <div class="dashboard-card-info">
                            <h3>Жилых комплексов в обслуживании:</h3>
                            <p>${data.complexes}</p>
                            <a href="residential_complex.php" class="dashboard-card-btn">Перейти →</a>
                        </div>
                    </div>
                    <div class="dashboard-card">
                        <div class="dashboard-card-icon">💸</div>
                        <div class="dashboard-card-info">
                            <h3>Просроченные платежи:</h3>
                            <p>${data.debts}</p>
                            <a href="debt_view.php" class="dashboard-card-btn">Перейти →</a>
                        </div>
                    </div>
                    <div class="dashboard-card">
                        <div class="dashboard-card-icon">📣</div>
                        <div class="dashboard-card-info">
                            <h3>Жалобы текущего месяца:</h3>
                            <p>Всего: ${data.complaints_new} / Обработано: ${data.complaints_done}</p>
                            <a href="complaint.php" class="dashboard-card-btn">Перейти →</a>
                        </div>
                    </div>
                    <div class="dashboard-card">
                        <div class="dashboard-card-icon">💡</div>
                        <div class="dashboard-card-info">
                            <h3>Предложений текущего месяца:</h3>
                            <p>Всего: ${data.suggestions_new} / Обработано: ${data.suggestions_done}</p>
                            <a href="suggestion.php" class="dashboard-card-btn">Перейти →</a>
                        </div>
                    </div>
                    <div class="dashboard-card">
                        <div class="dashboard-card-icon">🛠️</div>
                        <div class="dashboard-card-info">
                            <h3>Вызовы мастеров (текущий меясц):</h3>
                            <p>Всего: ${data.services_new} / Обработано: ${data.services_done}</p>
                            <a href="service.php" class="dashboard-card-btn">Перейти →</a>
                        </div>
                    </div>
                    <div class="dashboard-card">
                        <div class="dashboard-card-icon">📢</div>
                        <div class="dashboard-card-info">
                            <h3>Объявления (текущий месяц)</h3>
                            <p>${data.announcements}</p>
                            <a href="announcement.php" class="dashboard-card-btn">Перейти →</a>
                        </div>
                    </div>
                `;
                document.getElementById('dashboardCards').innerHTML = cardsHtml;
            });
    }

    document.getElementById('complexFilter').addEventListener('change', function(){
        loadDashboard(this.value);
    });

    loadDashboard();
</script>
</body>
</html>
