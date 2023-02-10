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
    // проверка на существование поста
    $post = check_post($db_connection, $post_id);
    if ($post) {
        $query = 'INSERT INTO fav_list
                    (user_id, post_id)
                  VALUES (' .
                    $_SESSION['user']['id'] . ', ' . $post_id . ')';
        mysqli_query($db_connection, $query);
    }
}
// переадресация на прошлую страницу
header('Location: ' . $_SERVER['HTTP_REFERER'] . '#post_id=' . $post_id);
