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

$type_id = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_NUMBER_INT); // Параметр запроса фильтрации по типу контента
$sort_by = filter_input(INPUT_GET, 'sort_by', FILTER_SANITIZE_SPECIAL_CHARS); // Параметр запроса сортировки

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
    $query .= ' GROUP BY post_id';
} // сортировка по лайкам

// сортировка по просмотрам по умолчанию
$query .= $sort_by ? " ORDER BY $sort_by" : " ORDER BY view_count";
// запрос сформирован

// список всех постов
$all_posts = fetch_from_db($db_connection, $query);

// кол-во всех постов
$all_posts_count = count($all_posts);

// лимит на кол-во постов на странице: 6 без сортировки/фильтрации и 9 при выборе любой из них
$show_limit = ($type_id || $sort_by) ? 9 : 6;

// кол-во страниц с постами
$page_count = ceil($all_posts_count / $show_limit);

// текущая страница
$current_page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT) ?? 1;

// список публикаций к отображению на странице
$posts = array_slice($all_posts, (($current_page - 1) * $show_limit), $show_limit, true);

// формирование адреса для предыдущей страницы
$prev_page = ($sort_by ? "sort_by=$sort_by&" : '') .
             ($type_id ? "type_id=$type_id&" : '') .
             'page=' . ($current_page -= ($current_page > 1) ? 1 : 0);

// формирование адреса для следующей страницы
$next_page = ($sort_by ? "sort_by=$sort_by&" : '') .
             ($type_id ? "type_id=$type_id&" : '') .
             'page=' . ($current_page += ($current_page < $page_count) ? 1 : 0);

// массив с данными для пагинации
$pagination = array(
    'page_count' => $page_count,
    'current_page' => $current_page,
    'prev_page' => $prev_page,
    'next_page' => $next_page,
    'all_posts_count' => $all_posts_count,
);

// защита от XXS
array_walk_recursive($posts, 'secure');

//foreach ($posts as $key => $post) { // добавляем постам в массиве рандомные даты - the-nepodarok
//    $posts[$key]['date'] = generate_random_date($key);
//}

$main_content = include_template('main.php', [
    'content_types' => $content_types,
    'show_limit' => $show_limit,
    'type_id' => $type_id ?? '',
    'sort_by' => $sort_by ?? 'view_count',
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
