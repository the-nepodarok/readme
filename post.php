<?php
require_once 'helpers.php';
require_once 'utils.php';
require_once 'db.php';

$is_auth = rand(0, 1);
$page_title = 'публикация';
$user_name = 'the-nepodarok'; // укажите здесь ваше имя
$comment_limit = 2; // ограничение на кол-во показываемых комментариев

// подключение шаблона страницы об ошибке

$post_id = filter_input(INPUT_GET, 'post_id', FILTER_SANITIZE_NUMBER_INT); // параметр запроса id поста
$post_id = intval($post_id); // приведение к целочисленному типу

// обработка ошибки существовании публикации
if (!isset($post_id)) {
    die(print include_template('layout.php', [
        'is_auth' => $is_auth,
        'user_name' => $user_name,
        'page_title' => 'Ошибка 404',
        'main_content' => include_template('page-404.php', ['text_content' => 'Страница не существует']),
    ]));
}

// получаем данные поста
$query = "
    SELECT p.*,
           u.avatar,
           u.user_name,
           u.create_dt AS reg_date,
           ct.type_val
    FROM post AS p
        JOIN user AS u
            ON u.id = p.user_id
        JOIN content_type AS ct
            ON ct.id = p.content_type_id
    WHERE p.id = $post_id
";

$post = fetch_from_db($db_connection, $query, 'row');

if (!$post) {
    die(print include_template('layout.php', [
        'is_auth' => $is_auth,
        'user_name' => $user_name,
        'page_title' => 'Ошибка 404',
        'main_content' => include_template('page-404.php', ['text_content' => 'Публикация не найдена']),
    ]));
}

$page_title = htmlspecialchars($post['header']); // обезопасить название страницы

// массив, собирающий в себя числовые значения для отображения количества лайков, репостов и т.д.
$count_arr = [];

// получаем кол-во публикаций от пользователя
$user_id = $post['user_id'];
$query = "
    SELECT COUNT(id)
    FROM post
    WHERE user_id = $user_id
";

$count_arr['post_count'] = fetch_from_db($db_connection, $query, 'col', 'COUNT(id)');

// получаем кол-во подписчиков у пользователя
$query = "
    SELECT COUNT(id)
    FROM follower_list
    WHERE followed_user_id = $user_id
";

$count_arr['follower_count'] = fetch_from_db($db_connection, $query, 'col', 'COUNT(id)');

// получаем кол-во лайков у записи
$query = "
    SELECT COUNT(user_id)
    FROM fav_list
    WHERE post_id = $post_id
";

$count_arr['like_count'] = fetch_from_db($db_connection, $query, 'col', 'COUNT(user_id)');

// получаем кол-во репостов записи
$query = "
    SELECT COUNT(id)
    FROM post
    WHERE origin_post_id = $post_id
";

$count_arr['repost_count'] = fetch_from_db($db_connection, $query, 'col', 'COUNT(id)');

// получаем кол-во комментариев к записи
$query = "
    SELECT COUNT(id)
    FROM comment AS c
    WHERE post_id = $post_id
";

$count_arr['comment_count'] = fetch_from_db($db_connection, $query, 'col', 'COUNT(id)');

// получаем хэштеги записи
$query = "
    SELECT post_id,
           hashtag_name
    FROM post AS p
        JOIN post_hashtag_link AS phl
            ON post_id = p.id
        JOIN hashtag AS ht
            ON ht.id = phl.hashtag_id
    WHERE post_id = $post_id
";

$post_hashtag_list = fetch_from_db($db_connection, $query);

// проверка отображения комментариев
$show_all_comments = isset($_GET['show_all_comments']);

// получаем комментарии к записи
$query = "
    SELECT c.*,
           u.avatar,
           u.user_name
    FROM comment AS c
        JOIN user AS u
            ON c.user_id = u.id
    WHERE post_id = $post_id
    ORDER BY c.create_dt DESC" .
    ($show_all_comments ? '' : " LIMIT $comment_limit") // отображение всех комментариев при соответствующем запросе или их обрезание до $comment_limit
;

$comment_list = fetch_from_db($db_connection, $query);

// условие для скрытия комментариев при превышение лимита
$hide_comments = $count_arr['comment_count'] > $comment_limit && !$show_all_comments;

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
    'hide_comments' => $hide_comments,
]);

$layout_template = include_template('layout.php', [
    'page_title' => $page_title,
    'is_auth' => $is_auth,
    'user_name' => $user_name,
    'main_content' => $main_content,
]);

print $layout_template;
