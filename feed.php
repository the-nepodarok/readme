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

// массив с данными страницы
$params = array(
    'page_title' => 'моя лента',
    'active_page' => 'feed',
);

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

// добавление данных о типе публикаций
$posts = array_map(function ($post) {
    $post['type_val'] = $_SESSION['ct_types'][$post['content_type_id']]['type_val'];
    return $post;
}, $posts);

// получение хэштегов для каждой публикации
foreach ($posts as &$post) {
    $post_hashtag_list = get_hashtags($db_connection, $post['id']);;

    if ($post_hashtag_list) {
        $post['hashtags'] = $post_hashtag_list;
    }
}

// параметр запроса репоста
$repost_id = filter_input(INPUT_GET, 'repost_id', FILTER_SANITIZE_NUMBER_INT);

// репост
if ($repost_id) {
    header( 'Location: /repost.php?repost_id=' . $repost_id);
    exit;
}

// формирование ссылки на пост
$post_link = '/post.php?post_id=';

$main_content = include_template('feed_template.php', [
    'type_id' => $type_id,
    'posts' => $posts,
    'post_link' => $post_link,
]);

print build_page('layout.php', $params, $main_content);
