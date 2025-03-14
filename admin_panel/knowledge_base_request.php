<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['submit_create_category'])) {
        $categoryName = $_POST['category_name'] ?? '';

        $url = 'http://212.112.105.242:8800/api/knowledge-base/categories?name=' . urlencode($categoryName);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, []);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            echo "<p style='color:red;'>Ошибка при создании категории: {$error}</p>";
        } else {
            echo "<p style='color:green;'>Ответ сервера (создать категорию): {$response}</p>";
        }
    }

    if (isset($_POST['submit_create_article'])) {
        $title      = $_POST['title']      ?? '';
        $content    = $_POST['content']    ?? '';
        $categoryId = $_POST['category_id'] ?? '';

        $postFields = [
            'title'       => $title,
            'content'     => $content,
            'category_id' => $categoryId
        ];

        if (!empty($_FILES['photos']['name'][0])) {
            foreach ($_FILES['photos']['tmp_name'] as $index => $tmpPath) {
                if ($_FILES['photos']['error'][$index] === UPLOAD_ERR_OK) {
                    $originalName = $_FILES['photos']['name'][$index];
                    $mimeType     = mime_content_type($tmpPath) ?: 'application/octet-stream';
                    $postFields["photos[$index]"] = curl_file_create($tmpPath, $mimeType, $originalName);
                }
            }
        }

        $ch = curl_init('http://212.112.105.242:8800/api/knowledge-base/articles');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            echo "<p style='color:red;'>Ошибка при создании статьи: {$error}</p>";
        } else {
            echo "<p style='color:green;'>Ответ сервера (создать статью): {$response}</p>";
        }
    }

    if (isset($_POST['submit_show_categories'])) {
        $url = 'http://212.112.105.242:8800/api/knowledge-base/categories';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            echo "<p style='color:red;'>Ошибка при получении категорий: {$error}</p>";
        } else {
            echo "<p style='color:green;'>Категории (JSON):</p>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
        }
    }

    if (isset($_POST['submit_show_articles'])) {
        $categoryId = $_POST['filter_category_id'] ?? '';
        $url = 'http://212.112.105.242:8800/api/knowledge-base/articles';
        if ($categoryId !== '') {
            $url .= '?category_id=' . urlencode($categoryId);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            echo "<p style='color:red;'>Ошибка при получении списка статей: {$error}</p>";
        } else {
            echo "<p style='color:green;'>Список статей (JSON):</p>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
        }
    }

    if (isset($_POST['submit_show_one_article'])) {
        $articleId = $_POST['article_id'] ?? '';
        if ($articleId === '') {
            echo "<p style='color:red;'>Не указан ID статьи!</p>";
        } else {
            $url = 'http://212.112.105.242:8800/api/knowledge-base/articles/' . urlencode($articleId);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $error    = curl_error($ch);
            curl_close($ch);

            if ($error) {
                echo "<p style='color:red;'>Ошибка при получении статьи #{$articleId}: {$error}</p>";
            } else {
                echo "<p style='color:green;'>Статья #{$articleId} (JSON):</p>";
                echo "<pre>" . htmlspecialchars($response) . "</pre>";
            }
        }
    }
}
?>