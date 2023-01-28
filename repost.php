<?php
//Производит репост публикации

// параметр запроса репоста
$repost_id = filter_input(INPUT_GET, 'repost', FILTER_SANITIZE_NUMBER_INT);

if ($repost_id) {
    // получаем оригинальный пост
    $query = "SELECT p.* FROM post AS p WHERE id = $repost_id";
    $original_post = get_data_from_db($db_connection, $query, 'row');

    if ($original_post and $original_post['user_id'] !== $_SESSION['user']['id']) {
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
                  VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?)"; // 10 полей
        $stmt = mysqli_prepare($db_connection, $query);

        // данные для подстановки
        $query_vars = array(
            $original_post['post_header'],
            $original_post['text_content'] ?? null,
            $original_post['quote_origin'] ?? null,
            $original_post['photo_content'] ?? null,
            $original_post['video_content'] ?? null,
            $original_post['link_text_content'] ?? null,
            $_SESSION['user']['id'],
            $original_post['id'],
            $original_post['content_type_id'],
        );

        // выполнение подготовленного выражения
        mysqli_stmt_bind_param($stmt, 'ssssssiii', ...$query_vars);
        mysqli_stmt_execute($stmt);

        // сохранение id нового поста
        $new_post_id = mysqli_insert_id($db_connection);

        // получение хэштегов записи
        $query = "SELECT hashtag_name,
                         ht.id
                  FROM post AS p
                      JOIN post_hashtag_link AS phl
                          ON phl.post_id = p.id
                      JOIN hashtag AS ht
                          ON ht.id = phl.hashtag_id
                  WHERE phl.post_id = '$repost_id'";
        $post_hashtag_list = get_data_from_db($db_connection, $query, 'all');

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
        header('Location: post.php?post_id=' . $new_post_id);
        exit;
    }
}
