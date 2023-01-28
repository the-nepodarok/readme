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
require_once 'repost.php';

// массив с данными страницы
$params = array(
    'page_title' => 'моя лента',
    'active_class' => 'feed',
);

$content_types = $_SESSION['ct_types']; // типы контента

// Параметр запроса фильтрации по типу контента; по умолчанию равен 0
$type_id = filter_input(INPUT_GET, 'type_id', FILTER_SANITIZE_NUMBER_INT);
$type_options = array_column($content_types,'id');
if (!in_array($type_id, $type_options)) {
    $type_id = 0; // default value
}

$posts = []; // массив для заполнения постами от подписок пользователя

// запрос для получения публикаций
$query = 'SELECT p.*,
                 u.user_avatar,
                 u.user_name,
                 ct.type_val,
                 ct.type_name,
                 (SELECT COUNT(id) FROM fav_list WHERE fav_list.post_id = p.id) AS like_count,
                 (SELECT COUNT(id) FROM comment WHERE comment.post_id = p.id) AS comment_count,
                 (SELECT COUNT(id) FROM post WHERE origin_post_id = p.id) AS repost_count
          FROM post AS p
              INNER JOIN user AS u
                  ON p.user_id = u.id
              INNER JOIN content_type AS ct
                  ON p.content_type_id = ct.id
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
    $hashtag_query = "SELECT hashtag_name
                      FROM post AS p
                          JOIN post_hashtag_link AS phl
                              ON phl.post_id = p.id
                          JOIN hashtag AS ht
                              ON ht.id = phl.hashtag_id
                      WHERE phl.post_id = " . $post['id'];
    $post_hashtag_list = get_data_from_db($db_connection, $hashtag_query, 'col');

    if ($post_hashtag_list) {
        $post['hashtags'] = $post_hashtag_list;
    }
}

$main_content = include_template('feed_template.php', [
    'content_types' => $content_types,
    'type_id' => $type_id,
    'posts' => $posts,
]);

print build_page('layout.php', $params, $main_content);
