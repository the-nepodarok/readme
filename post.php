<?php
require_once 'helpers.php';
require_once 'utils.php';
require_once 'db.php';

$is_auth = rand(0, 1);
$page_title = 'публикация';
$user_name = 'the-nepodarok'; // укажите здесь ваше имя
$comment_limit = 2; // ограничение на кол-во показываемых комментариев

// подключение шаблона страницы об ошибке
$error_page = include_template('layout.php', [
    'is_auth' => $is_auth,
    'user_name' => $user_name,
    'page_title' => 'Ошибка 404',
    'main_content' => include_template('page-404.php'),
]);

$post_id = filter_input(INPUT_GET, 'post_id', FILTER_SANITIZE_NUMBER_INT); // параметр запроса id поста
$post_id = intval($post_id); // обработка неправильного типа параметра url

// проверка на существование поста, соответствующего id из параметра запроса
$post_exists = fetch_from_db($db_connection, "SELECT id FROM post WHERE id = $post_id", 'row');

// обработка ошибки существовании публикации
if (!$post_exists) {
    die(print $error_page);
}

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

$post = fetch_from_db($db_connection, $query, 'row');

$page_title = htmlspecialchars($post['header']); // обезопасить название страницы

// получаем кол-во публикаций от пользователя
$user_id = $post['user_id'];
$query = "
    SELECT user_id,
           COUNT(id) AS count
    FROM post
    WHERE user_id = $user_id
";

$post_count = count_values($db_connection, $query);

// получаем кол-во подписчиков у пользователя
$query = "
    SELECT followed_user_id,
           COUNT(following_user_id) AS count
    FROM follower_list
    WHERE followed_user_id = $user_id
";

$follower_count = count_values($db_connection, $query);

// получаем кол-во лайков у записи
$query = "
    SELECT post_id,
        COUNT(user_id) AS count
    FROM fav_list
    WHERE post_id = $post_id
";

$like_count = count_values($db_connection, $query);

// получаем кол-во репостов записи
$query = "
    SELECT origin_post_id,
        COUNT(id) AS count
    FROM post
    WHERE origin_post_id = $post_id
";

$repost_count = count_values($db_connection, $query);

// получаем кол-во комментариев к записи
$query = "
    SELECT post_id,
        COUNT(id) AS count
    FROM comment AS c
    WHERE post_id = $post_id
";

$comment_count = count_values($db_connection, $query);

// получаем хэштеги записи
$query = "
    SELECT post_id, hashtag_name
    FROM post AS p
        JOIN post_hashtag_link AS phl
            ON post_id = p.id
        JOIN hashtag AS ht
            ON ht.id = phl.hashtag_id
    WHERE post_id = $post_id
";

$post_hashtag_list = fetch_from_db($db_connection, $query, 'all');

// установка режима отображения комментариев
$show_all_comments = filter_input(INPUT_GET, 'show_all_comments', FILTER_SANITIZE_STRING);
// отображение всех комментариев при соответствующем запросе или их обрезание до $comment_limit
$limit = $show_all_comments ?? "LIMIT $comment_limit";

// получаем комментарии к записи
$query = "
    SELECT c.*, u.avatar, u.user_name
    FROM comment AS c
        JOIN user AS u
            ON c.user_id = u.id
    WHERE post_id = $post_id
    ORDER BY c.create_dt DESC
    $limit
";

$comment_list = fetch_from_db($db_connection, $query, 'all');

// массив, собирающий в себя числовые значения для отображения количества лайков, репостов и т.д.
$count_arr = array(
    'post_count' => $post_count,
    'follower_count' => $follower_count,
    'like_count' => $like_count,
    'repost_count' => $repost_count,
    'comment_count' => $comment_count,
);

// отображение поста
$post_type_template = include_template("post-$post[type_val]_template.php", ['post' => $post]);

// подключение шаблонов
$main_content = include_template('post_template.php', [
        'comment_limit' => $comment_limit,
        'post' => $post,
        'post_type_template' => $post_type_template,
        'count_arr' => $count_arr,
        'post_hashtag_list' => $post_hashtag_list,
        'comment_list' => $comment_list,
        'show_all_comments' => $show_all_comments,
]);

$layout_template = include_template('layout.php', [
    'page_title' => $page_title,
    'is_auth' => $is_auth,
    'user_name' => $user_name,
    'main_content' => $main_content,
]);

print $layout_template;
