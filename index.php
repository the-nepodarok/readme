<?php
require_once 'helpers.php';
require_once 'utils.php';
require_once 'db.php';

$is_auth = rand(0, 1);
$page_title = 'популярное';
$user_name = 'the-nepodarok'; // укажите здесь ваше имя

// получение типов контента
$query = 'SELECT * FROM content_type';
$content_types = fetch_from_db($db_connection, $query);

// Параметр запроса фильтрации по типу контента; по умолчанию равен 0
$type_id = filter_input(INPUT_GET, 'type_id', FILTER_SANITIZE_NUMBER_INT, ['options' => ['default' => 0]]);
$type_options = array_column($content_types,'id');
array_unshift($type_options, 0);
if (!in_array($type_id, $type_options)) {
    $type_id = 0; // default value
}

// Параметр запроса сортировки; по умолчанию задаётся сортировка по просмотрам
$sort_by = filter_input(INPUT_GET, 'sort_by', FILTER_SANITIZE_SPECIAL_CHARS, ['options' => ['default' => 'view_count']]);
$sort_options = array('view_count', 'create_dt', 'like_count');
if (!in_array($sort_by, $sort_options)) {
    $sort_by = $sort_options[0];
}

// Склеивание запроса для вывода постов с фильтром, сортировкой и ограничение на кол-во постов на странице
$query = 'SELECT p.*,
             u.avatar,
             u.user_name,
             ct.type_val,
             ct.type_name';

if ($sort_by === 'like_count') {
    $query .= ", fl.post_id, COUNT(fl.user_id) AS like_count";
} // сортировка по лайкам

$query .= " FROM post AS p
               JOIN user AS u
                   ON p.user_id = u.id
               JOIN content_type AS ct
                   ON p.content_type_id = ct.id";

if ($sort_by === 'like_count') {
    $query .= " JOIN fav_list AS fl
                   ON fl.post_id = p.id";
} // сортировка по лайкам

if ($type_id) {
    $query .= " WHERE p.content_type_id = $type_id";
} // фильтрация по типу

if ($sort_by === 'like_count') {
    $query .= ' GROUP BY fl.post_id';
} // сортировка по лайкам

// сортировка по просмотрам по умолчанию
$query .= ' ORDER BY p.' . ($sort_by ?: 'view_count') . ' DESC';
// запрос сформирован

// список всех постов
$all_posts = fetch_from_db($db_connection, $query);

// собираем информацию о числе лайков и комментариев, чтобы вывести их в карточке поста
foreach ($all_posts as &$post) {
    // запрос на кол-во лайков
    $query = "
        SELECT COUNT(user_id)
        FROM fav_list
        WHERE post_id = $post[id]
    ";
    $post['like_count'] = fetch_from_db($db_connection, $query, 'col', 'COUNT(user_id)');

    // запрос на кол-во комментариев
    $query = "
        SELECT COUNT(id)
        FROM comment AS c
        WHERE post_id = $post[id]
    ";
    $post['comment_count'] = fetch_from_db($db_connection, $query, 'col', 'COUNT(id)');
}

// кол-во всех постов
$all_posts_count = count($all_posts);

// лимит на кол-во постов на странице
$show_limit = 6;

// кол-во страниц с постами
$page_count = ceil($all_posts_count / $show_limit);

// текущая страница
$current_page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT);

// кол-во всех страниц
$all_pages = range(1, $page_count);

// отсылаем к первой странице, если для page передано недопустимое значение
if (!in_array($current_page, $all_pages)) {
    $current_page = $all_pages[0];
}

// список публикаций к отображению на странице
$posts = array_slice($all_posts, (($current_page - 1) * $show_limit), $show_limit, true);

$current_url = array('type_id' => $type_id, 'sort_by' => $sort_by);

// формирование адреса для предыдущей страницы
$prev_page = http_build_query($current_url, '', '&') .
             '&page=' . ($current_page -= ($current_page > 1) ? 1 : 0);

// формирование адреса для следующей страницы
$next_page = http_build_query($current_url, '', '&') .
             '&page=' . ($current_page += ($current_page < $page_count) ? 1 : 0);

// защита от XSS
array_walk_recursive($posts, 'secure');

// массив с данными для пагинации
$pagination = array(
    'page_count' => $page_count,
    'current_page' => $current_page,
    'prev_page' => $prev_page,
    'next_page' => $next_page,
    'all_posts_count' => $all_posts_count,
);

//foreach ($posts as $key => $post) { // добавляем постам в массиве рандомные даты - the-nepodarok
//    $posts[$key]['date'] = generate_random_date($key);
//}

$main_content = include_template('main.php', [
    'content_types' => $content_types,
    'show_limit' => $show_limit,
    'type_id' => $type_id,
    'sort_by' => $sort_by,
    'pagination' => $pagination,
    'posts' => $posts,
]);

$layout_template = include_template('layout.php', [
    'page_title' => $page_title,
    'is_auth' => $is_auth,
    'user_name' => $user_name,
    'main_content' => $main_content,
]);

print($layout_template);
?>
