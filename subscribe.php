<?php
session_start();

// Перенаправление анонимного пользователя
if (!isset($_SESSION['user'])) {
    header('Location: /');
    exit;
}

require_once 'utils.php';
require_once 'db_config.php';

// параметр запроса пользователя
$user_id = filter_input(INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT);

// если параметр передан и пользователь не пытается подписаться на самого себя
if ($user_id and $user_id !== $_SESSION['user']['id']) {

    // проверка, что такой пользователь существует
    $query = 'SELECT id FROM user WHERE id = ' . $user_id;
    $user_exists = get_data_from_db($db_connection, $query, 'one');

    if ($user_exists) {

        // проверка на уже существующую пару в подписках
        $query = 'SELECT id
                      FROM follower_list
                  WHERE following_user_id = ' . $_SESSION['user']['id'] . '
                  AND followed_user_id = ' . $user_id;
        $subscription = get_data_from_db($db_connection, $query, 'all');

        if ($subscription) {

            // запрос на отписку от пользователя
            $query = 'DELETE FROM follower_list
                      WHERE following_user_id = ' . $_SESSION['user']['id'] . '
                      AND followed_user_id = ' . $user_id;
        } else {

            // запрос на подписку на пользователя
            $query = 'INSERT INTO follower_list
                             (following_user_id, followed_user_id)
                      VALUES (' .
                             $_SESSION['user']['id'] . ', ' . $user_id . ')';
        }
        mysqli_query($db_connection, $query);
    }
}
// переадресация на прошлую страницу
header('Location: ' . $_SESSION['prev_page']);
