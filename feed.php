<?php
require_once 'helpers.php';
require_once 'utils.php';
require_once 'config.php';

// Получение данных пользователя
$user = check_session($db_connection);

// Перенаправление анонимного пользователя
if (!$user) {
    header('Location: /');
    exit;
}

// массив с данными страницы и пользователя
$params = array(
    'page_title' => 'моя лента',
    'user_name' => $user['user_name'],
    'user_avatar' => $user['user_avatar'],
    'active_class' => 'feed',
);

// параметр запроса репоста
$repost_id = filter_input(INPUT_GET, 'repost', FILTER_SANITIZE_NUMBER_INT);

// получение типов контента
$query = 'SELECT * FROM content_type';
$content_types = get_data_from_db($db_connection, $query);

// Параметр запроса фильтрации по типу контента; по умолчанию равен 0
$type_id = filter_input(INPUT_GET, 'type_id', FILTER_SANITIZE_NUMBER_INT);
$type_options = array_column($content_types,'id');
if (!in_array($type_id, $type_options)) {
    $type_id = 0; // default value
}

// запрос на получение подписок пользователя
$query = "SELECT followed_user_id FROM follower_list WHERE following_user_id = '{$user['id']}'";
$f_users = get_data_from_db($db_connection, $query, 'col');
$posts = []; // массив для заполнения постами от подписок пользователя

foreach ($f_users as $f_user) {
    // запрос для получения публикаций
    $query = "SELECT p.*,
                     u.user_avatar,
                     u.user_name,
                     ct.type_val,
                     ct.type_name,
                     (SELECT COUNT(id) FROM fav_list WHERE fav_list.post_id = p.id) AS like_count,
                     (SELECT COUNT(id) FROM comment WHERE comment.post_id = p.id) AS comment_count,
                     (SELECT COUNT(id) FROM post WHERE origin_post_id = p.id) AS repost_count
          FROM post AS p
             JOIN user AS u
                ON p.user_id = u.id
             JOIN content_type AS ct
                ON p.content_type_id = ct.id
          WHERE user_id = $f_user";

    if ($type_id) { // фильтрация по типу
        $query .= " AND p.content_type_id = $type_id";
    }

    $query .= " ORDER BY create_dt DESC"; // сортировка по дате

    $f_posts = get_data_from_db($db_connection, $query);

    // получение хэштегов каждой из публикаций
    foreach ($f_posts as &$post) {
        $hashtag_query = "SELECT hashtag_name
                  FROM post AS p
                        JOIN post_hashtag_link AS phl
                            ON phl.post_id = p.id
                        JOIN hashtag AS ht
                            ON ht.id = phl.hashtag_id
                  WHERE phl.post_id = '{$post['id']}'";
        $post_hashtag_list = get_data_from_db($db_connection, $hashtag_query, 'col');

        if($post_hashtag_list) {
            $post['hashtags'] = $post_hashtag_list;
        }
    }
    $posts[] = $f_posts;
}

// объединение подмассивов в один общий массив с постами
$posts = array_merge(...$posts);

// подключение функционала репоста
if ($repost_id) {
    repost($db_connection, $repost_id, $user['id']);
}

$main_content = include_template('feed_template.php', [
    'content_types' => $content_types,
    'type_id' => $type_id,
    'posts' => $posts,
]);

print build_page('layout.php', $params, $main_content);
