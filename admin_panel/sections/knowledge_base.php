<?php
require __DIR__ . '/../include/auth.php';
require __DIR__ . '/../include/config.php';

$apiBaseUrl = API_BASE_URL;
$token      = $_SESSION['auth_token'] ?? null;

$currentCategoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

$categories     = [];
$articles       = [];
$icons          = [];
$iconMap        = [];
$errorMessage   = null;
$successMessage = null;

function apiRequestKB(string $method, string $url, string $token, ?array $data = null, bool $isMultipart = false): array
{
    $ch = curl_init($url);

    $headers = [
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
    ];

    if ($data !== null) {
        if ($isMultipart) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        }
    }

    curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
    ]);

    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$status, json_decode($response, true)];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token) {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_category') {
        $name = trim($_POST['name'] ?? '');
        $icon = $_POST['icon'] ?? '';
        if ($name !== '') {
            [$status, $data] = apiRequestKB('POST', $apiBaseUrl . '/knowledge-base/categories', $token, [
                    'name' => $name,
                    'icon' => $icon,
            ]);
            if ($status === 201) {
                $successMessage = 'Категория создана';
            } else {
                $errorMessage = $data['message'] ?? 'Ошибка создания категории';
            }
        }
    }

    if ($action === 'update_category') {
        $id   = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $icon = $_POST['icon'] ?? '';
        if ($id && $name !== '') {
            [$status, $data] = apiRequestKB('PUT', $apiBaseUrl . '/knowledge-base/categories/' . $id, $token, [
                    'name' => $name,
                    'icon' => $icon,
            ]);
            if ($status === 200) {
                $successMessage = 'Категория обновлена';
            } else {
                $errorMessage = $data['message'] ?? 'Ошибка обновления категории';
            }
        }
    }

    if ($action === 'delete_category') {
        $id = (int)($_POST['category_id'] ?? 0);
        if ($id) {
            [$status, $data] = apiRequestKB('DELETE', $apiBaseUrl . '/knowledge-base/categories/' . $id, $token);
            if ($status === 200) {
                $successMessage = $data['message'] ?? 'Категория удалена';
                if ($currentCategoryId === $id) {
                    $currentCategoryId = 0;
                }
            } else {
                $errorMessage = $data['message'] ?? 'Ошибка удаления категории';
            }
        }
    }

    if ($action === 'create_article') {
        $payload = [
                'title'       => $_POST['title'] ?? '',
                'content'     => $_POST['content'] ?? '',
                'category_id' => $_POST['category_id'] ?? '',
                'icon'        => $_POST['icon'] ?? '',
        ];

        $multipart = $payload;

        if (!empty($_FILES['photos']['name'][0])) {
            foreach ($_FILES['photos']['tmp_name'] as $i => $tmp) {
                if (!$tmp) {
                    continue;
                }
                $multipart["photos[$i]"] = new CURLFile(
                        $tmp,
                        $_FILES['photos']['type'][$i] ?? 'image/jpeg',
                        $_FILES['photos']['name'][$i] ?? ('photo_' . $i)
                );
            }
        }

        [$status, $data] = apiRequestKB(
                'POST',
                $apiBaseUrl . '/knowledge-base/articles',
                $token,
                $multipart,
                true
        );

        if ($status === 201) {
            $successMessage = 'Статья создана';
            if (!empty($payload['category_id'])) {
                $currentCategoryId = (int)$payload['category_id'];
            }
        } else {
            $errorMessage = $data['message'] ?? 'Ошибка создания статьи';
        }
    }

    if ($action === 'update_article') {
        $id = (int)($_POST['article_id'] ?? 0);
        if ($id) {
            $payload = [
                    'title'       => $_POST['title'] ?? '',
                    'content'     => $_POST['content'] ?? '',
                    'category_id' => $_POST['category_id'] ?? '',
                    'icon'        => $_POST['icon'] ?? '',
            ];

            $multipart = $payload;

            if (!empty($_FILES['photos']['name'][0])) {
                foreach ($_FILES['photos']['tmp_name'] as $i => $tmp) {
                    if (!$tmp) {
                        continue;
                    }
                    $multipart["photos[$i]"] = new CURLFile(
                            $tmp,
                            $_FILES['photos']['type'][$i] ?? 'image/jpeg',
                            $_FILES['photos']['name'][$i] ?? ('photo_' . $i)
                    );
                }
            }

            [$status, $data] = apiRequestKB(
                    'POST',
                    $apiBaseUrl . '/knowledge-base/articles/' . $id,
                    $token,
                    $multipart,
                    true
            );

            if ($status === 200) {
                $successMessage = 'Статья обновлена';
                if (!empty($payload['category_id'])) {
                    $currentCategoryId = (int)$payload['category_id'];
                }
            } else {
                $errorMessage = $data['message'] ?? 'Ошибка обновления статьи';
            }
        }
    }

    if ($action === 'delete_article') {
        $id = (int)($_POST['article_id'] ?? 0);
        if ($id) {
            [$status, $data] = apiRequestKB('DELETE', $apiBaseUrl . '/knowledge-base/articles/' . $id, $token);
            if ($status === 200) {
                $successMessage = $data['message'] ?? 'Статья удалена';
            } else {
                $errorMessage = $data['message'] ?? 'Ошибка удаления статьи';
            }
        }
    }

    $qs = $_SERVER['QUERY_STRING'] ? ('?' . $_SERVER['QUERY_STRING']) : '';
    header('Location: ' . $_SERVER['PHP_SELF'] . $qs);
    exit;
}

if ($token) {
    [$statusCat, $dataCat] = apiRequestKB('GET', $apiBaseUrl . '/knowledge-base/categories', $token);
    if ($statusCat === 200) {
        $categories = $dataCat ?? [];
    } else {
        $errorMessage = $dataCat['message'] ?? 'Ошибка получения категорий';
    }

    [$statusIcons, $dataIcons] = apiRequestKB('GET', $apiBaseUrl . '/knowledge-base/icons', $token);
    if ($statusIcons === 200) {
        $icons = $dataIcons ?? [];
        foreach ($icons as $icon) {
            if (isset($icon['name'], $icon['url'])) {
                $iconMap[$icon['name']] = $icon['url'];
            }
        }
    } else {
        if (!$errorMessage) {
            $errorMessage = $dataIcons['message'] ?? 'Ошибка получения иконок';
        }
    }
} else {
    $errorMessage = 'Нет токена авторизации';
}

if ($currentCategoryId === 0 && !empty($categories)) {
    $currentCategoryId = (int)($categories[0]['id'] ?? 0);
}

$currentCategoryName = '';
foreach ($categories as $cat) {
    if ((int)$cat['id'] === $currentCategoryId) {
        $currentCategoryName = $cat['name'] ?? '';
        break;
    }
}

if ($currentCategoryId && $token) {
    $query = $apiBaseUrl . '/knowledge-base/articles?category_id=' . urlencode($currentCategoryId);
    [$statusArt, $dataArt] = apiRequestKB('GET', $query, $token);
    if ($statusArt === 200) {
        $articles = $dataArt ?? [];
    } else {
        $errorMessage = $errorMessage ?: ($dataArt['message'] ?? 'Ошибка получения статей');
    }
}
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
        <h1 class="content__title">База знаний</h1>

        <?php if ($successMessage): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($successMessage ?? '', ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($errorMessage ?? '', ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="layout-two-columns">
            <section class="card card--side">
                <div class="card-header">
                    <h2 class="card-title">Категории</h2>
                    <button type="button" class="button-primary button-xs" onclick="openCategoryCreateModal()">
                        + Категория
                    </button>
                </div>

                <?php if (empty($categories)): ?>
                    <p>Категорий пока нет. Создайте первую.</p>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table class="admins-table kb-categories-table">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Иконка</th>
                                <th style="width:140px;">Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <?php
                                $catId        = (int)($cat['id'] ?? 0);
                                $catName      = $cat['name'] ?? '';
                                $catIconName  = $cat['icon'] ?? '';
                                $catIconUrl   = ($catIconName && isset($iconMap[$catIconName])) ? $iconMap[$catIconName] : '';
                                ?>
                                <tr class="<?= $catId === $currentCategoryId ? 'kb-category-item--active' : '' ?>">
                                    <td><?= htmlspecialchars($catId, ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <a href="?category_id=<?= $catId ?>" class="kb-category-link">
                                            <?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($catIconUrl): ?>
                                            <img src="<?= htmlspecialchars($catIconUrl, ENT_QUOTES, 'UTF-8') ?>" alt="" style="width:24px;height:24px;">
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="admins-actions">
                                            <button
                                                    type="button"
                                                    class="btn-small btn-edit"
                                                    onclick="openCategoryEditModal(this)"
                                                    data-id="<?= $catId ?>"
                                                    data-name="<?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8') ?>"
                                                    data-icon="<?= htmlspecialchars($catIconName, ENT_QUOTES, 'UTF-8') ?>"
                                            >Редактировать</button>

                                            <form method="post" style="display:inline"
                                                  onsubmit="return confirm('Удалить категорию?');">
                                                <input type="hidden" name="action" value="delete_category">
                                                <input type="hidden" name="category_id" value="<?= $catId ?>">
                                                <button type="submit" class="btn-small btn-delete">Удалить</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            </section>

            <section class="card card--main">
                <div class="card-header card-header--space-between">
                    <div>
                        <h2 class="card-title">Статьи</h2>
                        <div class="card-subtitle">
                            Категория базы знаний
                        </div>
                    </div>

                    <div class="card-header-actions">
                        <select id="kbCategorySelect" class="select-small">
                            <?php foreach ($categories as $cat): ?>
                                <?php
                                $catId   = (int)($cat['id'] ?? 0);
                                $catName = $cat['name'] ?? '';
                                ?>
                                <option value="<?= $catId ?>"
                                        <?= $catId === $currentCategoryId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <?php if ($currentCategoryId): ?>
                            <button type="button" class="button-primary button-xs"
                                    onclick="openArticleCreateModal(<?= (int)$currentCategoryId ?>)">
                                + Статья
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!$currentCategoryId): ?>
                    <p>Сначала выберите категорию или создайте новую.</p>
                <?php else: ?>
                    <?php if (empty($articles)): ?>
                        <p>В этой категории пока нет статей.</p>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table class="admins-table">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Заголовок</th>
                                    <th>Иконка</th>
                                    <th>Дата</th>
                                    <th>Фото</th>
                                    <th>Действия</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($articles as $article): ?>
                                    <?php
                                    $aId       = $article['id'] ?? '';
                                    $aTitle    = $article['title'] ?? '';
                                    $aDate     = $article['created_at'] ?? '';
                                    $aPhotos   = $article['photos'] ?? [];
                                    $aContent  = $article['content'] ?? '';
                                    $aCatId    = $article['category_id'] ?? $currentCategoryId;
                                    $aIconName = $article['icon'] ?? '';
                                    $aIconUrl  = ($aIconName && isset($iconMap[$aIconName])) ? $iconMap[$aIconName] : '';
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string)$aId, ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($aTitle, ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <?php if ($aIconUrl): ?>
                                                <img src="<?= htmlspecialchars($aIconUrl, ENT_QUOTES, 'UTF-8') ?>" alt="" style="width:24px;height:24px;">
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($aDate, ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= is_array($aPhotos) ? count($aPhotos) : 0 ?></td>
                                        <td>
                                            <div class="admins-actions">
                                                <button
                                                        type="button"
                                                        class="btn-small btn-edit"
                                                        onclick="openArticleEditModal(this)"
                                                        data-id="<?= htmlspecialchars((string)$aId, ENT_QUOTES, 'UTF-8') ?>"
                                                        data-title="<?= htmlspecialchars($aTitle, ENT_QUOTES, 'UTF-8') ?>"
                                                        data-content="<?= htmlspecialchars($aContent, ENT_QUOTES, 'UTF-8') ?>"
                                                        data-category-id="<?= htmlspecialchars((string)$aCatId, ENT_QUOTES, 'UTF-8') ?>"
                                                        data-icon="<?= htmlspecialchars($aIconName, ENT_QUOTES, 'UTF-8') ?>"
                                                >Редактировать</button>

                                                <button
                                                        type="button"
                                                        class="btn-small"
                                                        onclick="openArticleViewModal(this)"
                                                        data-title="<?= htmlspecialchars($aTitle, ENT_QUOTES, 'UTF-8') ?>"
                                                        data-content="<?= htmlspecialchars($aContent, ENT_QUOTES, 'UTF-8') ?>"
                                                >Просмотр</button>

                                                <form method="post" style="display:inline"
                                                      onsubmit="return confirm('Удалить статью?');">
                                                    <input type="hidden" name="action" value="delete_article">
                                                    <input type="hidden" name="article_id"
                                                           value="<?= htmlspecialchars((string)$aId, ENT_QUOTES, 'UTF-8') ?>">
                                                    <button type="submit" class="btn-small btn-delete">Удалить</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </section>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>

<script src="/include/scripts.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var sidebarInfo = document.getElementById('sidebar-information');
        if (sidebarInfo) {
            sidebarInfo.classList.add('sidebar__group--open');
        }
        var menuKb = document.getElementById('menu_knowledge_base');
        if (menuKb) {
            menuKb.classList.add('menu-selected-point');
        }
    });

    function catEls() {
        return {
            modal: document.getElementById('categoryModal'),
            action: document.getElementById('categoryFormAction'),
            id: document.getElementById('categoryId'),
            name: document.getElementById('categoryName'),
            title: document.getElementById('categoryModalTitle'),
            iconHidden: document.getElementById('categoryIcon')
        };
    }

    function openCategoryCreateModal() {
        var e = catEls();
        e.modal.classList.add('modal-open');
        e.title.textContent = 'Новая категория';
        e.action.value = 'create_category';
        e.id.value = '';
        e.name.value = '';
        if (e.iconHidden) {
            e.iconHidden.value = '';
            selectIconInPicker('categoryIconPicker', 'categoryIcon', '');
        }
    }

    function openCategoryEditModal(btn) {
        var e = catEls();
        e.modal.classList.add('modal-open');
        e.title.textContent = 'Редактировать категорию';
        e.action.value = 'update_category';
        e.id.value = btn.dataset.id || '';
        e.name.value = btn.dataset.name || '';
        if (e.iconHidden) {
            var iconName = btn.dataset.icon || '';
            e.iconHidden.value = iconName;
            selectIconInPicker('categoryIconPicker', 'categoryIcon', iconName);
        }
    }

    function closeCategoryModal() {
        catEls().modal.classList.remove('modal-open');
    }

    function artEls() {
        return {
            modal: document.getElementById('articleModal'),
            action: document.getElementById('articleFormAction'),
            id: document.getElementById('articleId'),
            categorySelect: document.getElementById('articleCategoryId'),
            title: document.getElementById('articleTitle'),
            content: document.getElementById('articleContent'),
            headerTitle: document.getElementById('articleModalHeaderTitle'),
            iconHidden: document.getElementById('articleIcon')
        };
    }

    function openArticleCreateModal(categoryId) {
        var e = artEls();
        e.modal.classList.add('modal-open');
        e.headerTitle.textContent = 'Новая статья';
        e.action.value = 'create_article';
        e.id.value = '';
        e.title.value = '';
        e.content.value = '';

        if (categoryId) {
            e.categorySelect.value = String(categoryId);
        }
        if (!e.categorySelect.value && e.categorySelect.options.length > 0) {
            e.categorySelect.selectedIndex = 0;
        }

        if (e.iconHidden) {
            e.iconHidden.value = '';
            selectIconInPicker('articleIconPicker', 'articleIcon', '');
        }
    }

    function openArticleEditModal(btn) {
        var e = artEls();
        e.modal.classList.add('modal-open');
        e.headerTitle.textContent = 'Редактировать статью';
        e.action.value = 'update_article';
        e.id.value = btn.dataset.id || '';
        e.title.value = btn.dataset.title || '';
        e.content.value = btn.dataset.content || '';

        var catId = btn.dataset.categoryId || '';
        if (catId) {
            e.categorySelect.value = catId;
        }

        if (e.iconHidden) {
            var iconName = btn.dataset.icon || '';
            e.iconHidden.value = iconName;
            selectIconInPicker('articleIconPicker', 'articleIcon', iconName);
        }
    }

    function closeArticleModal() {
        artEls().modal.classList.remove('modal-open');
    }

    function viewEls() {
        return {
            modal: document.getElementById('articleViewModal'),
            title: document.getElementById('articleViewTitle'),
            content: document.getElementById('articleViewContent')
        };
    }

    function openArticleViewModal(btn) {
        var e = viewEls();
        e.modal.classList.add('modal-open');
        e.title.textContent = btn.dataset.title || '';
        e.content.textContent = btn.dataset.content || '';
    }

    function closeArticleViewModal() {
        viewEls().modal.classList.remove('modal-open');
    }

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

    function initIconPicker(pickerId, hiddenInputId) {
        var picker = document.getElementById(pickerId);
        var hidden = document.getElementById(hiddenInputId);
        if (!picker || !hidden) return;

        picker.addEventListener('click', function (e) {
            var btn = e.target.closest('.icon-picker__item');
            if (!btn) return;

            var active = picker.querySelector('.icon-picker__item--active');
            if (active) active.classList.remove('icon-picker__item--active');

            btn.classList.add('icon-picker__item--active');
            hidden.value = btn.getAttribute('data-icon') || '';
        });
    }

    function selectIconInPicker(pickerId, hiddenInputId, iconName) {
        var picker = document.getElementById(pickerId);
        var hidden = document.getElementById(hiddenInputId);
        if (!picker || !hidden) return;

        var active = picker.querySelector('.icon-picker__item--active');
        if (active) active.classList.remove('icon-picker__item--active');

        if (!iconName) {
            hidden.value = '';
            return;
        }

        var btns = picker.querySelectorAll('.icon-picker__item');
        for (var i = 0; i < btns.length; i++) {
            if (btns[i].getAttribute('data-icon') === iconName) {
                btns[i].classList.add('icon-picker__item--active');
                hidden.value = iconName;
                break;
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        initIconPicker('categoryIconPicker', 'categoryIcon');
        initIconPicker('articleIconPicker', 'articleIcon');
    });

    picker.addEventListener('click', function (e) {
        var btn = e.target.closest('.icon-picker__item');
        if (!btn) return;

        var active = picker.querySelector('.icon-picker__item--active');
        if (active) active.classList.remove('icon-picker__item--active');

        btn.classList.add('icon-picker__item--active');
        hidden.value = btn.getAttribute('data-icon') || '';
    });
</script>

<div class="modal-backdrop" id="categoryModal">
    <div class="modal">
        <div class="modal-header">
            <strong id="categoryModalTitle">Категория</strong>
            <button type="button" class="modal-close" onclick="closeCategoryModal()">×</button>
        </div>
        <form method="post" class="login-form">
            <input type="hidden" name="action" id="categoryFormAction">
            <input type="hidden" name="category_id" id="categoryId">

            <div class="login-group">
                <label>Название категории</label>
                <input type="text" name="name" id="categoryName" required>
            </div>

            <div class="login-group">
                <label>Иконка</label>
                <input type="hidden" name="icon" id="categoryIcon">
                <div class="icon-picker" id="categoryIconPicker">
                    <?php foreach ($icons as $icon): ?>
                        <?php
                        $iconName = $icon['name'] ?? '';
                        $iconUrl  = $icon['url'] ?? '';
                        ?>
                        <button type="button"
                                class="icon-picker__item"
                                data-icon="<?= htmlspecialchars((string)$iconName, ENT_QUOTES, 'UTF-8') ?>">
                            <img src="<?= htmlspecialchars((string)$iconUrl, ENT_QUOTES, 'UTF-8') ?>"
                                 alt=""
                                 style="width:32px;height:32px;">
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeCategoryModal()">Отмена</button>
                <button type="submit" class="login-button">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="articleModal">
    <div class="modal modal--lg">
        <div class="modal-header">
            <strong id="articleModalHeaderTitle">Статья</strong>
            <button type="button" class="modal-close" onclick="closeArticleModal()">×</button>
        </div>
        <form method="post" enctype="multipart/form-data" class="login-form">
            <input type="hidden" name="action" id="articleFormAction">
            <input type="hidden" name="article_id" id="articleId">

            <div class="login-group">
                <label>Категория</label>
                <select name="category_id" id="articleCategoryId" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars((string)$cat['id'], ENT_QUOTES, 'UTF-8') ?>"
                                <?= (int)$cat['id'] === $currentCategoryId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="login-group">
                <label>Заголовок</label>
                <input type="text" name="title" id="articleTitle" required>
            </div>

            <div class="login-group">
                <label>Контент</label>
                <textarea name="content" id="articleContent" rows="8" required></textarea>
            </div>

            <div class="login-group">
                <label>Иконка</label>
                <input type="hidden" name="icon" id="articleIcon">
                <div class="icon-picker" id="articleIconPicker">
                    <?php foreach ($icons as $icon): ?>
                        <?php
                        $iconName = $icon['name'] ?? '';
                        $iconUrl  = $icon['url'] ?? '';
                        ?>
                        <button type="button"
                                class="icon-picker__item"
                                data-icon="<?= htmlspecialchars((string)$iconName, ENT_QUOTES, 'UTF-8') ?>">
                            <img src="<?= htmlspecialchars((string)$iconUrl, ENT_QUOTES, 'UTF-8') ?>"
                                 alt=""
                                 style="width:32px;height:32px;">
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="login-group">
                <label>Фото (можно несколько)</label>
                <input type="file" name="photos[]" multiple accept="image/*">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeArticleModal()">Отмена</button>
                <button type="submit" class="login-button">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="articleViewModal">
    <div class="modal modal--lg">
        <div class="modal-header">
            <strong id="articleViewTitle">Статья</strong>
            <button type="button" class="modal-close" onclick="closeArticleViewModal()">×</button>
        </div>
        <div class="modal-body">
            <pre id="articleViewContent" style="white-space: pre-wrap;"></pre>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeArticleViewModal()">Закрыть</button>
        </div>
    </div>
</div>

</body>
</html>
