<?php
session_start();

// Перенаправление анонимного пользователя
if (!isset($_SESSION['user'])) {
    header('Location: /');
    exit;
}

require_once 'helpers.php';
require_once 'utils.php';
require_once 'db_config.php';

$comment_limit = 2; // ограничение на кол-во показываемых комментариев

// параметр ID пользователя профиля
$user_id = filter_input(INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT);

// параметр активной вкладки страницы профиля
$active_tab = filter_input(INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS);
if (!$active_tab) {
    $active_tab = 'posts'; // вкладка по умолчанию
}

// добавление комментария
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = []; // массив для ошибок валидации поля ввода комментария
    $comment_data = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
    $comment_text = str_replace('&#13;&#10;', ' ', $comment_data['comment-text']);
    $comment_text = trim($comment_text); // обрезка лишних пробелов

    // проверка на пустой комментарий
    if (empty($comment_text)) {
        $err_type = 'Поле не заполнено';
        $err_heading = 'Пустой комментарий';
        $err_text = 'Напишите комментарий';
    } elseif (mb_strlen($comment_text) < 4) { // проверка на количество символов
        $err_type = 'Слишком короткий комментарий';
        $err_heading = 'Длина меньше 4 символов';
        $err_text = 'Добавьте ещё пару слов';
    } else {
        // проверка существования публикации
        $query = 'SELECT id FROM post WHERE id = ' . $comment_data['post-id'];
        $comment_post = get_data_from_db($db_connection, $query, 'one');

        if ($comment_post && !$errors) {
            // подготовка выражения
            $query = "INSERT INTO comment (
                                comment_content,
                                user_id,
                                post_id
                             )
                             VALUES (?, ?, ?)"; // 3 поля
            $stmt = mysqli_prepare($db_connection, $query);

            // данные для подстановки
            $query_vars = array(
                $comment_text,
                $_SESSION['user']['id'],
                $comment_data['post-id'],
            );

            // выполнение подготовленного выражения
            mysqli_stmt_bind_param($stmt, 'sii', ...$query_vars);
            mysqli_stmt_execute($stmt);
            header('Location: profile.php?user_id=' . $user_id .
                '&tab='. $active_tab . '&show_comments=' . $comment_post);
            exit;
        }
    }
    if (isset($err_text)) { // заполнить массив с ошибками, если таковые возникли
        $field_name = 'comment-text';
        fill_errors($errors, $field_name, $err_type, $err_heading, $err_text);
    }
}

$posts = []; // массив для публикаций в профиле
$user_data = []; // данные профиля
$subscription = false; // флаг подписки на этот профиль

if ($user_id) {
    // запрос на получение данных профиля
    $query = 'SELECT user_reg_dt,
                     user_name,
                     user_avatar,
                     (SELECT COUNT(id) FROM post WHERE user_id = ' . $user_id . ') AS post_count,
                     (SELECT COUNT(id) FROM follower_list WHERE followed_user_id = ' . $user_id . ') AS follower_count
                  FROM user
              WHERE id = ' . $user_id;
    $user_data = get_data_from_db($db_connection, $query, 'row');

    // запрос на получение данных о подписке аутент. польз-ля на этот профиль
    $query = 'SELECT id
                  FROM follower_list
              WHERE following_user_id = ' . $_SESSION['user']['id'] . '
              AND followed_user_id = ' . $user_id;
    $subscription = get_data_from_db($db_connection, $query, 'one') > 0;

    if ($user_data) {
    array_walk_recursive($user_data, 'secure'); // очистка от вредоносного кода

    // запрос для получения публикаций
    $query = 'SELECT p.*,
                     (SELECT COUNT(id) FROM fav_list WHERE fav_list.post_id = p.id) AS like_count,
                     (SELECT COUNT(id) FROM comment WHERE comment.post_id = p.id) AS comment_count,
                     (SELECT COUNT(id) FROM post WHERE origin_post_id = p.id) AS repost_count
                  FROM post AS p
              WHERE user_id = ' . $user_id .
            ' ORDER BY create_dt DESC';
    $posts = get_data_from_db($db_connection, $query);
    } else {
        header('Location: profile.php?user_id=' . $_SESSION['user']['id']);
        exit;
    }
} else {
    header('Location: profile.php?user_id=' . $_SESSION['user']['id']);
    exit;
}

if ($posts) {
    // получение хэштегов для каждой публикации
    foreach ($posts as &$post) {
        $post_hashtag_list = get_hashtags($db_connection, $post['id']);;

        if ($post_hashtag_list) {
            $post['hashtags'] = $post_hashtag_list;
        }

        // получение данных автора для репостов
        if ($post['is_repost']) {
            $query = 'SELECT u.id,
                             u.user_name,
                             u.user_avatar,
                             p.create_dt AS op_date
                          FROM user AS u
                              INNER JOIN post AS p
                                  ON p.user_id = u.id
                      WHERE p.id = ' . $post['origin_post_id'];
            $repost_author = get_data_from_db($db_connection, $query, 'row');
        }
    }

    // устранение вредоносного кода
    array_walk_recursive($posts, 'secure');
}

$comment_list = []; // массив для списка комментариев

// параметр отображения комментариев
$show_comments = filter_input(INPUT_GET, 'show_comments', FILTER_SANITIZE_NUMBER_INT);

if ($show_comments) { // если такой параметр передан

    // запрос на получение комментариев к публикации
    $query = 'SELECT c.*,
                     u.user_avatar,
                     u.user_name
                  FROM comment AS c
                      INNER JOIN user AS u
                          ON c.user_id = u.id
              WHERE post_id = ' . $show_comments . '
              ORDER BY c.comment_create_dt DESC LIMIT ' . $comment_limit;
    $comment_list = get_data_from_db($db_connection, $query);
}

$likes = []; // массив для списка лайков

// вкладка с лайками
if ($active_tab === 'likes') {
    // запрос на получение списка лайков публикации
    $query = 'SELECT p.id AS post_id,
                     p.photo_content,
                     p.video_content,
                     p.content_type_id,
                     u.user_name,
                     u.user_avatar,
                     fl.like_dt,
                     fl.user_id
                  FROM post AS p
                      INNER JOIN fav_list as fl
                          ON fl.post_id = p.id
                      INNER JOIN user AS u
                          ON u.id = fl.user_id
              WHERE p.user_id = ' . $user_id . '
              ORDER BY fl.like_dt DESC';
    $likes = get_data_from_db($db_connection, $query);
}

$followers = []; // массив для подписанных пользователей

// вкладка с подписчиками
if ($active_tab === 'following') {
    // запрос на получение подписчиков пользователя
    $query = 'SELECT u.id AS user_id,
                     u.user_name,
                     u.user_avatar,
                     u.user_reg_dt,
                     (SELECT COUNT(id) FROM post WHERE user_id = u.id) AS post_count,
                     (SELECT COUNT(id) FROM follower_list WHERE followed_user_id = u.id) AS follower_count
                  FROM user AS u
                      INNER JOIN follower_list AS fl
                          ON following_user_id = u.id
              WHERE followed_user_id = ' . $user_id;
    $followers = get_data_from_db($db_connection, $query);

    // проверка подписки аутент. польз-ля на подписанных на этот профиль
    foreach ($followers as &$follower) {
        $query = 'SELECT id
                      FROM follower_list
                  WHERE following_user_id = ' . $_SESSION['user']['id'] . '
                  AND followed_user_id = ' . $follower['user_id'];
        $follower['subscribed_to_follower'] = get_data_from_db($db_connection, $query, 'one') > 0;
    }
}

// сохранение адреса страницы для перенаправления со страницы поиска
$_SESSION['prev_page'] = 'profile.php?user_id=' . $user_id . '&tab=' . $active_tab;

// класс для отображения ошибки рядом с полем
$alert_class = 'form__input-section--error';

// массив с данными страницы
$params = array(
    'page_title' => 'профиль пользователя',
);

$main_content = include_template('profile_template.php', [
    'user_data' => $user_data,
    'subscription' => $subscription,
    'user_id' => $user_id,
    'active_tab' => $active_tab,
    'posts' => $posts,
    'repost_author' => $repost_author ?? [],
    'show_comments' => $show_comments,
    'comment_list' => $comment_list,
    'comment_limit' => $comment_limit,
    'errors' => $errors ?? [],
    'alert_class' => $alert_class,
    'comment_text' => $comment_text ?? '',
    'likes' => $likes,
    'followers' => $followers,
]);

print build_page('layout.php', $params, $main_content);
