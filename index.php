<?php
require_once 'helpers.php';
require_once 'utils.php';

$is_auth = rand(0, 1);
$page_title = 'популярное';
$user_name = 'the-nepodarok'; // укажите здесь ваше имя

$posts = [
    [
        'post_title' => 'Цитата',
        'post_type' => 'post-quote',
        'post_content' => 'Мы в жизни любим только раз, а после ищем лишь похожих',
        'post_user' => 'Лариса',
        'post_avatar' => 'userpic-larisa-small.jpg',
    ],
    [
        'post_title' => 'Игра Престолов',
        'post_type' => 'post-text',
        'post_content' => 'Не могу дождаться начала финального сезона своего любимого сериала!',
        'post_user' => 'Владик',
        'post_avatar' => 'userpic.jpg',
    ],
    [
        'post_title' => 'Наконец, обработал фотки!',
        'post_type' => 'post-photo',
        'post_content' => 'rock-medium.jpg',
        'post_user' => 'Виктор',
        'post_avatar' => 'userpic-mark.jpg',
    ],
    [
        'post_title' => 'Моя мечта',
        'post_type' => 'post-photo',
        'post_content' => 'coast-medium.jpg',
        'post_user' => 'Лариса',
        'post_avatar' => 'userpic-larisa-small.jpg',
    ],
    [
        'post_title' => 'Лучшие курсы',
        'post_type' => 'post-link',
        'post_content' => 'www.htmlacademy.ru',
        'post_user' => 'Владик',
        'post_avatar' => 'userpic.jpg',
    ],
];

array_walk_recursive($posts, 'secure'); // защита от XXS

foreach ($posts as $key => $post) {
    $posts[$key]['date'] = generate_random_date($key);
}

$main_content = include_template('main.php', [
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
