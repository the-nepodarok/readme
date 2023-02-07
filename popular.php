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

// Параметр запроса сортировки; по умолчанию задаётся сортировка по кол-ву просмотров
$sort_by = filter_input(INPUT_GET, 'sort_by', FILTER_SANITIZE_SPECIAL_CHARS);
$sort_options = array('view_count', 'like_count', 'create_dt');
if (!in_array($sort_by, $sort_options)) {
    $sort_by = $sort_options[0];
}

$sort_by_likes = $sort_by === 'like_count';

// текущая страница
$current_page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT);

// перенаправление к первой странице, если для page передано значение меньше допустимого
if ($current_page < 1) {
    $current_page = 1;
}

// лимит на кол-во постов на странице
$show_limit = 6;

// получение количества всех постов
$query = "SELECT COUNT(id) FROM post" . ($type_id ? " WHERE content_type_id = $type_id" : '');
$all_posts_count = get_data_from_db($db_connection, $query, 'one');

// Формирование запроса для вывода постов с фильтром, сортировкой и ограничение на кол-во постов на странице
$query = '
      SELECT p.*,
             u.user_avatar,
             u.user_name,
             (SELECT COUNT(id) FROM fav_list WHERE post_id = p.id) AS like_count,
             (SELECT COUNT(id) FROM comment WHERE comment.post_id = p.id) AS comment_count
          FROM post AS p
              INNER JOIN user AS u
                  ON p.user_id = u.id';

if ($type_id) {
    $query .= " WHERE p.content_type_id = $type_id"; // фильтрация по типу
}

// сортировка
$query .= ' ORDER BY ' . ($sort_by_likes ? '' : 'p.') . $sort_by . ' DESC
            LIMIT ' . $show_limit . '
            OFFSET ' . $show_limit * ($current_page - 1);
// запрос сформирован

// список всех постов
$posts = get_data_from_db($db_connection, $query);

// кол-во страниц с постами
$page_count = ceil($all_posts_count / $show_limit);

// условие для отображения/скрытия кнопок пагинации
$show_pagination = $all_posts_count > $show_limit;

// отсылаем к последней странице, если задано значение больше кол-ва страниц
if ($current_page > $page_count) {
    $current_page = $page_count;
}

// параметры текущего адреса
$url_param = array(
    'type_id' => $type_id,
    'sort_by' => $sort_by,
);

// формирование адреса для предыдущей страницы
$url_less = $url_param + ['page' => ($current_page - (int)($current_page > 1))];
$prev_page = http_build_query($url_less , '', '&');

// формирование адреса для следующей страницы
$url_more = $url_param + ['page' => ($current_page + (int)($current_page < $page_count))];
$next_page = http_build_query($url_more, '', '&');

// массив с данными для пагинации
$pagination = array(
    'page_count' => $page_count,
    'current_page' => $current_page,
    'prev_page' => $prev_page,
    'next_page' => $next_page,
);

// защита от XSS
array_walk_recursive($posts, 'secure');

// формирование параметра запроса для фильтрации по типу
$type_filter_url = $type_id ? "&type_id=$type_id" : '';

//foreach ($posts as $key => $post) { // добавляем постам в массиве рандомные даты - the-nepodarok
//    $posts[$key]['date'] = generate_random_date($key);
//}

// сохранение адреса страницы для перенаправления на странице поиска
$_SESSION['prev_page'] = 'popular.php?type_id=' . $type_id . '&sort_by=' . $sort_by . '&page=' . $current_page;

// массив с данными страницы
$params = array(
    'page_title' => 'популярное',
    'active_page' => 'popular',
);

$main_content = include_template('main.php', [
    'sort_by' => $sort_by,
    'type_id' => $type_id,
    'type_filter_url' => $type_filter_url,
    'posts' => $posts,
    'pagination' => $pagination,
    'show_pagination' => $show_pagination,
]);

print build_page('layout.php', $params, $main_content);
