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

$comment_limit = 2; // ограничение на кол-во показываемых комментариев

// параметр запроса id поста
$post_id = filter_input(INPUT_GET, 'post_id', FILTER_SANITIZE_NUMBER_INT);
$post_id = intval($post_id); // приведение к целочисленному типу

if ($post_id > 0) {
    // получаем данные поста
    $query = "
        SELECT p.*,
               u.user_avatar,
               u.user_name,
               u.user_reg_dt AS reg_date
        FROM post AS p
            JOIN user AS u
                ON u.id = p.user_id
        WHERE p.id = $post_id
    ";
    $post = get_data_from_db($db_connection, $query, 'row');
} else { // обработка ошибки запроса
    $error_page = include_template('page-404.php', ['main_content' => 'Запрос сформирован неверно!']);
    die(build_page('layout.php', ['page_title' => 'Ошибка запроса'], $error_page));
}

if ($post) {
    $query = 'UPDATE post SET view_count = view_count + 1'; // увеличение счётчика просмотров
    mysqli_query($db_connection, $query);
} else {
    // обработка ошибки несуществующей публикации
    $error_page = include_template('page-404.php', ['main_content' => 'Публикация не найдена']);
    die(build_page('layout.php', ['page_title' => 'Ошибка 404'], $error_page));
}

array_walk_recursive($post, 'secure'); // обезопасить данные страницы

// массив, собирающий в себя числовые значения для отображения количества лайков, репостов и т.д.
$count_arr = [];

// получаем кол-во публикаций от пользователя
$post_user_id = $post['user_id'];
$query = "SELECT COUNT(id) FROM post WHERE user_id = $post_user_id";
$count_arr['post_count'] = get_data_from_db($db_connection, $query, 'one');

// получаем кол-во подписчиков у пользователя
$query = "SELECT COUNT(id) FROM follower_list WHERE followed_user_id = $post_user_id";
$count_arr['follower_count'] = get_data_from_db($db_connection, $query, 'one');

// получаем кол-во лайков у записи
$query = "SELECT COUNT(id) FROM fav_list WHERE post_id = $post_id";
$count_arr['like_count'] = get_data_from_db($db_connection, $query, 'one');

// получаем кол-во репостов записи
$query = "SELECT COUNT(id) FROM post WHERE origin_post_id = $post_id";
$count_arr['repost_count'] = get_data_from_db($db_connection, $query, 'one');

// получаем кол-во комментариев к записи
$query = "SELECT COUNT(id) FROM comment AS c WHERE post_id = $post_id";
$count_arr['comment_count'] = get_data_from_db($db_connection, $query, 'one');

// получаем хэштеги записи
$post_hashtag_list = get_hashtags($db_connection, $post_id);
array_walk_recursive($post_hashtag_list, 'secure'); // очистка хэштегов от вредоносного кода

// проверка отображения комментариев
$show_all_comments = isset($_GET['show_all_comments']);

// получаем комментарии к записи
$query = "
    SELECT c.*,
           u.user_avatar,
           u.user_name
    FROM comment AS c
        JOIN user AS u
            ON c.user_id = u.id
    WHERE post_id = $post_id
    ORDER BY c.comment_create_dt DESC";

// отображение всех комментариев при соответствующем запросе или их обрезание до $comment_limit
if (!$show_all_comments) {
    $query.= " LIMIT $comment_limit";
}

$comment_list = get_data_from_db($db_connection, $query);
array_walk_recursive($comment_list, 'secure'); // очистка комментариев от вредоносного кода

// условие для скрытия комментариев при превышение лимита
$hide_comments = $count_arr['comment_count'] > $comment_limit && !$show_all_comments;

// добавление нового комментария
$errors = []; // массив для заполнения ошибками
$comment_text = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_text = filter_input(INPUT_POST, 'comment-text', FILTER_SANITIZE_SPECIAL_CHARS);
    $comment_text = str_replace('&#13;&#10;', " ", $comment_text);
    $comment_text = trim($comment_text); // обрезка лишних пробелов

    // проверка на пустой комментарий
    if (empty($comment_text)) {
        $err_type = 'Поле не заполнено';
        $err_heading = 'Пустой комментарий';
        $err_text = 'Напишите комментарий';
    } elseif (mb_strlen($comment_text) < 4) { // проверка на количество символов
        $err_type = 'Слишком короткий комментарий';
        $err_heading = 'Длина меньше 4 символов';
        $err_text = 'Добавьте ещё пару слов';
    } else {
        $query = 'SELECT id FROM post WHERE id = ' . $post_id;
        $comment_post = get_data_from_db($db_connection, $query, 'one');

        if ($comment_post && !$errors) {
            // подготовка выражения
            $query = "INSERT INTO comment (
                                comment_content,
                                user_id,
                                post_id
                             )
                             VALUES (?, ?, ?)"; // 3 поля
            $stmt = mysqli_prepare($db_connection, $query);

            // данные для подстановки
            $query_vars = array(
                $comment_text,
                $_SESSION['user']['id'],
                $post_id,
            );

            // выполнение подготовленного выражения
            mysqli_stmt_bind_param($stmt, 'sii', ...$query_vars);
            mysqli_stmt_execute($stmt);

            header('Location: profile.php?user_id=' . $post['user_id']);
            exit;
        }
    }
    if (isset($err_text)) { // заполнить массив с ошибками, если таковые возникли
        $field_name = 'comment-text';
        fill_errors($errors, $field_name, $err_type, $err_heading, $err_text);
    }
}

// записываем тип публикации
$post_type = $_SESSION['ct_types'][$post['content_type_id']]['type_val'];

// сохранение адреса страницы для перенаправления на странице поиска
$_SESSION['prev_page'] = 'post.php?post_id=' . $post_id;

// класс для отображения ошибки рядом с полем
$alert_class = 'form__input-section--error';

// массив с данными страницы
$params = array(
    'page_title' => 'публикация. ' . $post['post_header'],
);

// отображение поста
$post_type_template = include_template('post-' . $post_type . '_template.php', ['post' => $post]);

// подключение шаблонов
$main_content = include_template('post_template.php', [
    'post' => $post,
    'post_type_template' => $post_type_template,
    'count_arr' => $count_arr,
    'post_hashtag_list' => $post_hashtag_list,
    'errors' => $errors,
    'alert_class' => $alert_class,
    'comment_text' => $comment_text ?? '',
    'comment_list' => $comment_list,
    'hide_comments' => $hide_comments,
]);

// подключение layout
print build_page('layout.php', $params, $main_content);
