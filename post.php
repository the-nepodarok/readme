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

// параметр запроса id поста
$post_id = filter_input(INPUT_GET, 'post_id', FILTER_SANITIZE_NUMBER_INT);
$post_id = intval($post_id); // приведение к целочисленному типу

if ($post_id > 0) {
    // получаем данные поста
    $query = "
        SELECT p.*,
               u.user_avatar,
               u.user_name,
               u.user_reg_dt AS reg_date
        FROM post AS p
            JOIN user AS u
                ON u.id = p.user_id
        WHERE p.id = $post_id
    ";
    $post = get_data_from_db($db_connection, $query, 'row');
} else { // обработка ошибки запроса
    $error_page = include_template('page-404.php', ['main_content' => 'Запрос сформирован неверно!']);
    die(build_page('layout.php', ['page_title' => 'Ошибка запроса'], $error_page));
}

// обработка ошибки несуществующей публикации
if (!$post) {
    $error_page = include_template('page-404.php', ['main_content' => 'Публикация не найдена']);
    die(build_page('layout.php', ['page_title' => 'Ошибка 404'], $error_page));
}

array_walk_recursive($post, 'secure'); // обезопасить данные страницы

// массив, собирающий в себя числовые значения для отображения количества лайков, репостов и т.д.
$count_arr = [];

// получаем кол-во публикаций от пользователя
$post_user_id = $post['user_id'];
$query = "SELECT COUNT(id) FROM post WHERE user_id = $post_user_id";
$count_arr['post_count'] = get_data_from_db($db_connection, $query, 'one');

// получаем кол-во подписчиков у пользователя
$query = "SELECT COUNT(id) FROM follower_list WHERE followed_user_id = $post_user_id";
$count_arr['follower_count'] = get_data_from_db($db_connection, $query, 'one');

// получаем кол-во лайков у записи
$query = "SELECT COUNT(id) FROM fav_list WHERE post_id = $post_id";
$count_arr['like_count'] = get_data_from_db($db_connection, $query, 'one');

// получаем кол-во репостов записи
$query = "SELECT COUNT(id) FROM post WHERE origin_post_id = $post_id";
$count_arr['repost_count'] = get_data_from_db($db_connection, $query, 'one');

// получаем кол-во комментариев к записи
$query = "SELECT COUNT(id) FROM comment AS c WHERE post_id = $post_id";
$count_arr['comment_count'] = get_data_from_db($db_connection, $query, 'one');

// получаем хэштеги записи
$post_hashtag_list = get_hashtags($db_connection, $post_id);
array_walk_recursive($post_hashtag_list, 'secure'); // очистка хэштегов от вредоносного кода

// проверка отображения комментариев
$show_all_comments = isset($_GET['show_all_comments']);

// получаем комментарии к записи
$query = "
    SELECT c.*,
           u.user_avatar,
           u.user_name
    FROM comment AS c
        JOIN user AS u
            ON c.user_id = u.id
    WHERE post_id = $post_id
    ORDER BY c.comment_create_dt DESC";

// отображение всех комментариев при соответствующем запросе или их обрезание до $comment_limit
if (!$show_all_comments) {
    $query.= " LIMIT $comment_limit";
}

$comment_list = get_data_from_db($db_connection, $query);
array_walk_recursive($comment_list, 'secure'); // очистка комментариев от вредоносного кода

// условие для скрытия комментариев при превышение лимита
$hide_comments = $count_arr['comment_count'] > $comment_limit && !$show_all_comments;

// записываем тип публикации
$post_type = $_SESSION['ct_types'][$post['content_type_id']]['type_val'];

// сохранение адреса страницы для перенаправления на странице поиска
$_SESSION['prev_page'] = 'post.php?post_id=' . $post_id;

// массив с данными страницы
$params = array(
    'page_title' => 'публикация. ' . $post['post_header'],
);

// отображение поста
$post_type_template = include_template('post-' . $post_type . '_template.php', ['post' => $post]);

// подключение шаблонов
$main_content = include_template('post_template.php', [
    'post' => $post,
    'post_type_template' => $post_type_template,
    'count_arr' => $count_arr,
    'post_hashtag_list' => $post_hashtag_list,
    'comment_list' => $comment_list,
    'hide_comments' => $hide_comments,
]);

// подключение layout
print build_page('layout.php', $params, $main_content);
