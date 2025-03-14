<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Knowledge Base Admin Panel</title>
</head>
<body>
<h1>Управление базой знаний</h1>

<section>
    <h2>Создать категорию</h2>
    <form action="knowledge_base_request.php" method="post">
        <label for="category_name">Название категории:</label>
        <input type="text" name="category_name" id="category_name" placeholder="Общие правила" required>
        <button type="submit" name="submit_create_category">Создать категорию</button>
    </form>
</section>

<hr>

<section>
    <h2>Создать статью</h2>
    <form action="knowledge_base_request.php" method="post" enctype="multipart/form-data">
        <div>
            <label for="title">Заголовок статьи:</label>
            <input type="text" name="title" id="title" value="Как пользоваться лифтом" required>
        </div>
        <div>
            <label for="content">Содержание статьи:</label>
            <textarea name="content" id="content" rows="3" required>Надо правильно пользоваться</textarea>
        </div>
        <div>
            <label for="category_id">ID категории:</label>
            <input type="text" name="category_id" id="category_id" value="1" required>
        </div>
        <div>
            <label for="photos">Фотографии (необязательно):</label>
            <input type="file" name="photos[]" id="photos" multiple>
        </div>
        <button type="submit" name="submit_create_article">Создать статью</button>
    </form>
</section>

<hr>

<section>
    <h2>Показать категории</h2>
    <form action="knowledge_base_request.php" method="post">
        <button type="submit" name="submit_show_categories">Показать все категории</button>
    </form>
</section>

<hr>

<section>
    <h2>Показать статьи</h2>
    <form action="knowledge_base_request.php" method="post">
        <label for="filter_category_id">ID категории (опционально):</label>
        <input type="text" name="filter_category_id" id="filter_category_id" placeholder="Например: 1">
        <button type="submit" name="submit_show_articles">Показать статьи</button>
    </form>
</section>

<hr>

<section>
    <h2>Показать конкретную статью</h2>
    <form action="knowledge_base_request.php" method="post">
        <label for="article_id">ID статьи:</label>
        <input type="text" name="article_id" id="article_id" placeholder="1">
        <button type="submit" name="submit_show_one_article">Показать статью</button>
    </form>
</section>

</body>
</html>