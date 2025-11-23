<?php
$adminName = $_SESSION['admin_name'] ?? '';
$adminRole = $_SESSION['admin_role'] ?? '';
?>
<header class="header">
    <div class="header__left">
        <button class="header__menu-btn js-menu-toggle">☰</button>
        <span class="header__title">WIRES HOME</span>
    </div>
    <div class="header__right">
        <span class="header__user">
            <?= htmlspecialchars($adminName) ?>
            <span class="header__role">(<?= htmlspecialchars($adminRole) ?>)</span>
        </span>
    </div>
</header>