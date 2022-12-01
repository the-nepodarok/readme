<?php
require_once 'helpers.php';
require_once 'utils.php';
require_once 'config/db.php';

$db_connection = mysqli_connect($db['host'],
    $db['user'],
    $db['password'],
    $db['database']); // устанавливается соединение с БД
mysqli_set_charset($db_connection, 'utf8'); // Установка кодировки по ум.

if (!$db_connection) {
    $err = mysqli_connect_error();
    print($err);
} else {
    $content_types_query = 'SELECT * FROM content_type';
    $content_types = mysqli_query($db_connection, $content_types_query);

    if ($content_types) {
        $content_types = mysqli_fetch_all($content_types, MYSQLI_ASSOC);
    } else {
        $err = mysqli_error($db_connection);
        print($err);
    }

    $user_posts_query = 'SELECT p.*,
           p.header AS post_title,
           u.avatar AS avatar,
           u.user_name,
           ct.type_val AS post_type
        FROM post AS p
        JOIN user AS u
            ON p.user_id = u.id
        JOIN content_type ct
            ON p.content_type_id = ct.id
        WHERE `user_id` IS NOT NULL
        ORDER BY p.view_count DESC';
    $user_posts = mysqli_query($db_connection, $user_posts_query);

    if ($user_posts) {
        $posts = mysqli_fetch_all($user_posts, MYSQLI_ASSOC);
    } else {
        $err = mysqli_error($db_connection);
        print($err);
    }
}

$is_auth = rand(0, 1);
$page_title = 'популярное';
$user_name = 'the-nepodarok'; // укажите здесь ваше имя

//$posts = [
//    [
//        'post_title' => 'Цитата',
//        'post_type' => 'post-quote',
//        'post_content' => 'Мы в жизни любим только раз, а после ищем лишь похожих',
//        'post_user' => 'Лариса',
//        'post_avatar' => 'userpic-larisa-small.jpg',
//    ],
//    [
//        'post_title' => 'Игра Престолов',
//        'post_type' => 'post-text',
//        'post_content' => 'Не могу дождаться начала финального сезона своего любимого сериала!',
//        'post_user' => 'Владик',
//        'post_avatar' => 'userpic.jpg',
//    ],
//    [
//        'post_title' => 'Наконец, обработал фотки!',
//        'post_type' => 'post-photo',
//        'post_content' => 'rock-medium.jpg',
//        'post_user' => 'Виктор',
//        'post_avatar' => 'userpic-mark.jpg',
//    ],
//    [
//        'post_title' => 'Моя мечта',
//        'post_type' => 'post-photo',
//        'post_content' => 'coast-medium.jpg',
//        'post_user' => 'Лариса',
//        'post_avatar' => 'userpic-larisa-small.jpg',
//    ],
//    [
//        'post_title' => 'Лучшие курсы',
//        'post_type' => 'post-link',
//        'post_content' => 'www.htmlacademy.ru',
//        'post_user' => 'Владик',
//        'post_avatar' => 'userpic.jpg',
//    ],
//];

array_walk_recursive($posts, 'secure'); // защита от XXS

//foreach ($posts as $key => $post) { // добавляем постам в массиве рандомные даты - the-nepodarok
//    $posts[$key]['date'] = generate_random_date($key);
//}

$main_content = include_template('main.php', [
    'posts' => $posts,
    'content_types' => $content_types,
]);

$layout_template = include_template('layout.php', [
    'page_title' => $page_title,
    'is_auth' => $is_auth,
    'user_name' => $user_name,
    'main_content' => $main_content,
]);

print($layout_template);
?>
