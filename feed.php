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

// Параметр запроса фильтрации по типу контента; по умолчанию равен 0
$type_id = filter_input(INPUT_GET, 'type_id', FILTER_SANITIZE_NUMBER_INT);
if (!key_exists($type_id, $_SESSION['ct_types'])) {
    $type_id = 0; // default value
}

$posts = []; // массив для заполнения постами от подписок пользователя

// запрос для получения публикаций
$query = 'SELECT p.*,
                 u.user_avatar,
                 u.user_name,
                 (SELECT COUNT(id) FROM fav_list WHERE fav_list.post_id = p.id) AS like_count,
                 (SELECT COUNT(id) FROM comment WHERE comment.post_id = p.id) AS comment_count,
                 (SELECT COUNT(id) FROM post WHERE origin_post_id = p.id) AS repost_count
          FROM post AS p
              INNER JOIN user AS u
                  ON p.user_id = u.id
              JOIN follower_list AS fl
                  ON followed_user_id = u.id
          WHERE following_user_id = ' . $_SESSION['user']['id'];

if ($type_id) { // фильтрация по типу
    $query .= " AND p.content_type_id = $type_id";
}

$query .= ' ORDER BY create_dt DESC'; // сортировка по дате
$posts = get_data_from_db($db_connection, $query);

// получение хэштегов для каждой публикации
foreach ($posts as &$post) {
    $post_hashtag_list = get_hashtags($db_connection, $post['id']);;

    if ($post_hashtag_list) {
        $post['hashtags'] = $post_hashtag_list;
    }
}

// устранение вредоносного кода
array_walk_recursive($posts, 'secure');

// сохранение адреса страницы для перенаправления на странице поиска
$_SESSION['prev_page'] = 'feed.php';

// массив с данными страницы
$params = array(
    'page_title' => 'моя лента',
    'active_page' => 'feed',
    'db_connection' => $db_connection,
);

$main_content = include_template('feed_template.php', [
    'type_id' => $type_id,
    'posts' => $posts,
]);

print build_page('layout.php', $params, $main_content);
