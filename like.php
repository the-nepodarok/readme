<?php
session_start();

// Перенаправление анонимного пользователя
if (!isset($_SESSION['user'])) {
    header('Location: /');
    exit;
}

require_once 'utils.php';
require_once 'db_config.php';

// параметр ID поста
$post_id = filter_input(INPUT_GET, 'post_id', FILTER_SANITIZE_NUMBER_INT);

if ($post_id) {
    $query = 'SELECT id FROM post WHERE id =' . $post_id;
    $post = get_data_from_db($db_connection, $query, 'one');

    // проверка на существование поста
    if ($post) {
        $query = 'SELECT * FROM fav_list
                  WHERE post_id = ' . $post_id . '
                  AND user_id = ' . $_SESSION['user']['id'];
        $like = get_data_from_db($db_connection, $query, 'row');

        // если лайк уже есть
        if ($like) {
            $query = 'DELETE FROM fav_list
                      WHERE post_id = ' . $post_id . '
                      AND user_id = ' . $_SESSION['user']['id'];
        } else {
            $query = 'INSERT INTO fav_list
                        (user_id, post_id)
                      VALUES (' .
                        $_SESSION['user']['id'] . ', ' . $post_id . ')';
        }
        mysqli_query($db_connection, $query);
    }
}
// переадресация на прошлую страницу
header('Location: ' . $_SESSION['prev_page']);
