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

// параметр поискового запроса
$search_query = filter_input(INPUT_GET, SEARCH,FILTER_SANITIZE_SPECIAL_CHARS);
$search_query = trim_extra_spaces($search_query); // обрезка лишних пробелов
$posts = []; // массив для заполнения постами по результатам поиска

if ($search_query) {

    // производится ли поиск по хэштегу
    $hashtag_search = mb_substr($search_query, 0,1) === '#';

    if ($hashtag_search) {
        $hashtag_query = substr($search_query, 1);
        $query = "SELECT id FROM hashtag WHERE hashtag_name = '$hashtag_query'";
        $tag_id = get_data_from_db($db_connection, $query, 'one');
    }

    // формирование запроса для получения публикаций по поисковому запросу
    $query = 'SELECT p.*,
                     u.user_avatar,
                     u.user_name,
                     (SELECT COUNT(id) FROM fav_list WHERE fav_list.post_id = p.id) AS like_count,
                     (SELECT COUNT(id) FROM comment WHERE comment.post_id = p.id) AS comment_count';

    if (!$hashtag_search) {
        $query .= ', MATCH(post_header, text_content) AGAINST(' . "'$search_query'" . ') ' . 'AS score';
    }

    $query .= ' FROM post AS p
                      INNER JOIN user AS u
                          ON p.user_id = u.id';

    if ($hashtag_search) {
        $query .= ' INNER JOIN post_hashtag_link as phl
                        ON p.id = phl.post_id
                WHERE phl.hashtag_id =' . $tag_id . '
                ORDER BY phl.id DESC ';
    } else {
        $query .= ' WHERE MATCH (post_header, text_content) AGAINST (' . "'$search_query'" . ')
                   ORDER by score DESC';
    } // запрос сформирован

    $posts = get_data_from_db($db_connection, $query); // список постов к выводу
}

// массив с данными страницы
$params = array(
    'page_title' => 'поиск',
    'search_query' => $search_query ?? '',
);

// формирование страницы результатов поиска
if ($posts) {
    array_walk_recursive($posts, 'secure'); // очистка от вредоносного кода
    $main_content = include_template('search-results_template.php', [
        'search_query' => $search_query,
        'posts' => $posts,
    ]);
} else {
    $main_content = include_template('no-results_template.php', [
        'search_query' => $search_query,
    ]);
}

print build_page('layout.php', $params, $main_content);
