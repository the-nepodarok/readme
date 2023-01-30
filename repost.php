<?php // Производит репост публикации
session_start();

// Перенаправление анонимного пользователя
if (!isset($_SESSION['user'])) {
    header('Location: /');
    exit;
}

require_once 'utils.php';
require_once 'db_config.php';

// параметр запроса репоста
$post_id = filter_input(INPUT_GET, 'post_id', FILTER_SANITIZE_NUMBER_INT);
$tmp_id = $post_id;

if ($post_id) {
    // получаем оригинальный пост
    $query = "SELECT * FROM post WHERE id = $post_id";
    $post_data = get_data_from_db($db_connection, $query, 'row');

    if ($post_data and $post_data['user_id'] !== $_SESSION['user']['id']) {

        // подготовка выражения
        $query = "INSERT INTO post (
                      post_header,
                      text_content,
                      quote_origin,
                      photo_content,
                      video_content,
                      link_text_content,
                      user_id,
                      is_repost,
                      origin_post_id,
                      content_type_id
                    )
                  VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?)"; // 9 полей
        $stmt = mysqli_prepare($db_connection, $query);

        // данные для подстановки
        $query_vars = array(
            $post_data['post_header'],
            $post_data['text_content'] ?? null,
            $post_data['quote_origin'] ?? null,
            $post_data['photo_content'] ?? null,
            $post_data['video_content'] ?? null,
            $post_data['link_text_content'] ?? null,
            $_SESSION['user']['id'],
            $post_data['id'],
            $post_data['content_type_id'],
        );

        // выполнение подготовленного выражения
        mysqli_stmt_bind_param($stmt, 'ssssssiii', ...$query_vars);
        mysqli_stmt_execute($stmt);

        // сохранение id нового поста
        $new_post_id = mysqli_insert_id($db_connection);

        // получение хэштегов записи
        $post_hashtag_list = get_hashtags($db_connection, $post_id);

        // добавление хэштегов репосту
        if ($post_hashtag_list) {
            foreach ($post_hashtag_list as $tag) {
                // подготовка запроса для связки поста с тегами
                $query = "INSERT INTO post_hashtag_link
                            (post_id, hashtag_id)
                          VALUES
                            ($new_post_id, {$tag['id']})";
                mysqli_query($db_connection, $query);
            }
        }
        // переадресация на страницу с репостом
        $tmp_id = $new_post_id;
    }
}
header('Location: post.php?post_id=' . $tmp_id); // переадресация на страницу с постом
exit;
