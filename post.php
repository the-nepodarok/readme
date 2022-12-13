<?php
require_once 'helpers.php';
require_once 'utils.php';
require_once 'db.php';

$post_id = filter_input(INPUT_GET, 'id'); // параметр запроса id поста

// получаем данные поста
$query = "
    SELECT p.*,
           u.avatar,
           u.user_name,
           ct.type_val
    FROM post AS p
        JOIN user AS u
            ON u.id = p.user_id
        JOIN content_type AS ct
            ON ct.id = p.content_type_id
    WHERE p.id = $post_id
";

$post = get_row_from_db($db_connection, $query);
$user_id = $post['user_id'];
$post_id = $post['id'];

$post_exists = $post_id && $post; // проверка на существование поста, соответствующего id из параметра запроса

if ($post_exists) {

// получаем кол-во публикаций от пользователя
$query = "
    SELECT user_id,
           COUNT(id) AS post_count
    FROM post
    WHERE user_id = $user_id
    GROUP BY user_id
";

$post_count = get_row_from_db($db_connection, $query)['post_count'];

// получаем кол-во подписчиков у пользователя
$query = "
    SELECT followed_user_id,
           COUNT(following_user_id) AS f_count
    FROM follower_list
    WHERE followed_user_id = $user_id
    GROUP BY followed_user_id
";

$follower_count = get_row_from_db($db_connection, $query)['f_count'];

// получаем кол-во лайков у записи
$query = "
    SELECT post_id,
        COUNT(user_id) AS like_count
    FROM fav_list
    WHERE post_id = $post_id
    GROUP BY post_id
";

$like_count = get_row_from_db($db_connection, $query)['like_count'];

// получаем кол-во репостов записи
$query = "
    SELECT origin_post_id,
        COUNT(id) AS repost_count
    FROM post
    WHERE origin_post_id = $post_id
    GROUP BY origin_post_id
";

$repost_count = get_row_from_db($db_connection, $query)['repost_count'];

// получаем хэштеги записи
$query = "
    SELECT post_id, hashtag_name
    FROM post AS p
        JOIN post_hashtag_link AS phl
            ON post_id = p.id
        JOIN hashtag AS ht
            ON ht.id = phl.hashtag_id
    WHERE post_id = $post_id";

$post_hashtag_list = get_data_from_db($db_connection, $query);
}

$is_auth = rand(0, 1);
$page_title = $post['header'];
$user_name = 'the-nepodarok'; // укажите здесь ваше имя

// отображение поста или страницы ошибки
$main_content = $post_exists ?
    include_template('post_template.php', [
        'post' => $post,
        'post_count' => $post_count,
        'follower_count' => $follower_count,
        'like_count' => $like_count,
        'repost_count' => $repost_count,
        'post_hashtag_list' => $post_hashtag_list,
])
    : include_template('page-404.php', []);

$layout_template = include_template('layout.php', [
    'page_title' => $page_title,
    'is_auth' => $is_auth,
    'user_name' => $user_name,
    'main_content' => $main_content,
]);

print $layout_template;
