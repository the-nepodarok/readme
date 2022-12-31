<?php
require_once 'helpers.php';
require_once 'utils.php';
require_once 'db.php';

// массив с данными страницы и пользователя
$params = array(
    'is_auth' => $is_auth = rand(0, 1),
    'page_title' => $page_title = 'публикация',
    'user_name' => $user_name = 'the-nepodarok', // укажите здесь ваше имя
);

// получение типов контента
$query = 'SELECT * FROM content_type';
$content_types = get_data_from_db($db_connection, $query);

// параметр запроса типа добавляемого поста
$form_tab = filter_input(INPUT_GET, 'post_type', FILTER_SANITIZE_STRING);
$form_tab_options = array_column($content_types, 'type_val');

// тип публикации, для добавления которой форма открывается по умолчанию
if (!in_array($form_tab, $form_tab_options)) {
    $form_tab = 'text';
}

$errors = [];

// массив с допустимыми для загрузки в форме типами файлов
$allowed_file_types = array(
    'image/jpeg',
    'image/png',
    'image/gif',
);

// запуск валидации
if ($_POST) {
    array_walk($_POST, 'secure');
    $errors = validateField($form_tab);
}

// временная переменная со случайным пользователем - автором публикации
$user_id = rand(1, 4);

// обработка полученных данных в случае правильного заполнения всех полей
if (empty($errors) && $_POST) {
    // Добавление новых публикаций в базу данных
    switch ($form_tab) {
        case 'text':
            $query = "INSERT INTO post
                        (post_header, text_content, quote_origin, photo_content, video_content, link_text_content, user_id, content_type_id)
                      VALUES
                        ('{$_POST[$form_tab . '-heading']}', '{$_POST['post-text']}', NULL, NULL, NULL, NULL, $user_id, 1)";
            $result = mysqli_query($db_connection, $query);
            break;
        case 'quote':
            $query = "INSERT INTO post
                        (post_header, text_content, quote_origin, photo_content, video_content, link_text_content, user_id, content_type_id)
                      VALUES
                        ('{$_POST[$form_tab . '-heading']}', '{$_POST['cite-text']}', '{$_POST['quote-author']}', NULL, NULL, NULL, $user_id, 2)";
            $result = mysqli_query($db_connection, $query);
            break;
        case 'photo':
            $file = validateFile('userpic_file_photo', $allowed_file_types) ? uploadFile('userpic_file_photo') : uploadFileFromURL('photo-url');
            $query = "INSERT INTO post
                        (post_header, text_content, quote_origin, photo_content, video_content, link_text_content, user_id, content_type_id)
                      VALUES
                        ('{$_POST[$form_tab . '-heading']}', NULL, NULL, '$file', NULL, NULL, $user_id, 3)";
            $result = mysqli_query($db_connection, $query);
            break;
        case 'video':
            $query = "INSERT INTO post
                        (post_header, text_content, quote_origin, photo_content, video_content, link_text_content, user_id, content_type_id)
                      VALUES
                        ('{$_POST[$form_tab . '-heading']}', NULL, NULL, NULL, '{$_POST['video-url']}', NULL, $user_id, 4)";
            $result = mysqli_query($db_connection, $query);
            break;
        case 'link':
            $query = "INSERT INTO post
                        (post_header, text_content, quote_origin, photo_content, video_content, link_text_content, user_id, content_type_id)
                      VALUES
                        ('{$_POST[$form_tab . '-heading']}', NULL, NULL, NULL, NULL, '{$_POST['post-link']}', $user_id, 5)";
            $result = mysqli_query($db_connection, $query);
            break;
    }

    // сохраняем id нового поста
    $new_post_id = mysqli_insert_id($db_connection);

    // добавление тегов
    $tags_field = $_POST[$form_tab === 'quote' ? 'cite-tags' : $form_tab . '-tags'];

    if (!empty($tags_field)) {
        $tags = explode(' ', $tags_field);
        foreach ($tags as $key => $value) {
            $tag_exists = get_data_from_db($db_connection, "SELECT id FROM hashtag WHERE hashtag_name = '$value'", 'one');
                if ($tag_exists) {
                    mysqli_query($db_connection, "INSERT INTO post_hashtag_link
                                                            (post_id, hashtag_id)
                                                          VALUES
                                                            ('$new_post_id', $tag_exists)");
                } else {
                    mysqli_query($db_connection, "INSERT INTO hashtag SET hashtag_name = '$value'");
                    $new_tag_id = mysqli_insert_id($db_connection);
                    mysqli_query($db_connection, "INSERT INTO post_hashtag_link
                                                            (post_id, hashtag_id)
                                                          VALUES
                                                            ('$new_post_id', $new_tag_id)");
                }
            }
        }

    // Переадресация на страницу с новосозданным постом
    header('Location: post.php?post_id=' . $new_post_id);
}

// подключение шаблона для отображения блока с ошибками заполнения полей формы
$error_div = include_template('add-post_error_template.php', []);
$alert_class = 'form__input-section--error';

// подключение шаблона для отображения поля ввода заголовка
$header_field = include_template('add-post_header_template.php', [
    'type' => $form_tab,
    'errors' => $errors,
]);

// подключение шаблона для отображения поля ввода тегов
$tag_field = include_template('add-post_tags_template.php', [
    'type' => $form_tab,
]);

// отображение страницы
$main_content = include_template('add-post_template.php', [
    'content_types' => $content_types,
    'errors' => $errors,
    'alert_class' => $alert_class,
    'form_tab' => $form_tab,
    'header_field' => $header_field,
    'tag_field' => $tag_field,
    'error_div' => $error_div,
]);

print build_page('layout.php', $params, $main_content);
