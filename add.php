<?php
require_once 'helpers.php';
require_once 'utils.php';
require_once 'db.php';

// массив с данными страницы и пользователя
$params = array(
    'is_auth' => rand(0, 1),
    'page_title' => 'публикация',
    'user_name' => 'the-nepodarok', // укажите здесь ваше имя
    'user' => array(
        'id' => 1,
    ),
);

// получение типов контента
$query = 'SELECT * FROM content_type';
$content_types = get_data_from_db($db_connection, $query);

// параметр типа добавляемой публикации
$post_type = filter_input(INPUT_GET, 'post_type', FILTER_SANITIZE_STRING);

// перечень допустимых параметров
$form_tab_options = array_column($content_types, 'type_val');

// открытие формы с созданием текстовой публикации по умолчанию
if (!in_array($post_type, $form_tab_options)) {
    $post_type = 'text';
}

$errors = [];
$values = [];

// обработка данных формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // удаление XSS
    $_POST = filter_input_array(INPUT_POST);

    // возврат на форму, с которой возникли ошибки
    $form_tab = $_POST['form_tab'];

    // повторная проверка параметра
    if (!in_array($form_tab, $form_tab_options)) {
        $form_tab = 'text';
    }

    $post_type = $form_tab;

    // выборка обязательных для заполнения полей
    $required_fields = [
        'post-heading' => 'Заголовок',
    ];

    switch ($post_type) {
        case 'text':
            $required_fields += [
                'post-text' => 'Текст поста',
            ];
            break;
        case 'quote':
            $required_fields += [
                'cite-text' => 'Текст цитаты',
                'quote-author' => 'Автор',
            ];
            break;
        case 'video':
            $required_fields += [
                'video-url' => 'Ссылка YouTube',
            ];
            break;
        case 'link':
            $required_fields += [
                'post-link' => 'Ссылка',
            ];
    }

    // обработка пустых обязательных полей
    foreach ($required_fields as $key => $value) {
        if (empty($_POST[$key])) {
            fill_errors($errors, $key, 'Пустое поле', $value, 'Это поле должно быть заполнено');
        }
    }

    // валидация ссылок и загрузка файлов
    switch ($post_type) {
        case 'video':
            $video_url = $_POST['video-url'];
            if (!empty($video_url)) {
                if (check_url($video_url)) {
                    $yt_check = check_youtube_url($video_url);
                    if ($yt_check !== true) {
                        $err_type = 'Не найдено видео по ссылке';
                        $err_heading = 'Ссылка YouTube';
                        $err_text = $yt_check;
                        fill_errors($errors, 'video-url', $err_type, $err_heading, $err_text);
                    }
                } else {
                    $err_type = 'Недействительная ссылка';
                    $err_heading = 'Ссылка YouTube';
                    $err_text = 'Ссылка YouTube. Введите корректную ссылку на видео с YouTube';
                    fill_errors($errors, 'video-url', $err_type, $err_heading, $err_text);
                }
            }
            break;

        case 'photo':
            $file_error = $_FILES['userpic_file_photo']['error'];
            switch ($file_error) {
                // если файл загружен
                case 0:
                    // проверка на валидность файла
                    if (validate_file('userpic_file_photo')) {
                        // загрузка и перемещение файла
                        $file = upload_file('userpic_file_photo');
                    } else {
                        $err_type = 'Неверный тип файла';
                        $err_heading = 'Картинка';
                        $err_text = 'Загрузите картинку в формате jpg, png или gif';
                        fill_errors($errors, 'file-error', $err_type, $err_heading, $err_text);
                    }
                    break;
                case 1:
                case 2:
                    $err_type = 'Размер файла';
                    $err_heading = 'Картинка';
                    $err_text = 'Превышен максимальный размер файла';
                    fill_errors($errors, 'file-error', $err_type, $err_heading, $err_text);
                    break;
                case 3:
                case 4:
                    if (empty($_POST['photo-url'])) {
                        $err_type = 'Файл отсутствует';
                        $err_heading = 'Картинка';
                        $err_text = 'Файл не был загружен или загрузился с ошибками. Попробуйте ещё раз';
                        fill_errors($errors, 'file-error', $err_type, $err_heading, $err_text);
                    } else { // загрузка файла по ссылке
                        // проверка валидности и доступности ссылки
                        if (check_url($_POST['photo-url'])) {
                            $file_from_url = download_file_from_url('photo-url');
                            if ($file_from_url) {
                                $file = $file_from_url;
                            } else {
                                $err_type = 'Неверный тип файла';
                                $err_heading = 'Ссылка из интернета';
                                $err_text = 'Файл по ссылке не является изображением в формате jpg, png или gif';
                                fill_errors($errors, 'photo-url', $err_type, $err_heading, $err_text);
                            }
                        } else {
                            $err_type = 'Файл отсутствует';
                            $err_heading = 'Ссылка из интернета';
                            $err_text = 'Не удалось получить файл, убедитесь в правильности ссылки';
                            fill_errors($errors, 'photo-url', $err_type, $err_heading, $err_text);
                        }
                    }
                    break;
                case 6:
                case 7:
                    $err_type = 'Не удалось записать файл';
                    $err_heading = 'Ошибка сервера';
                    $err_text = 'Пожалуйста, попробуйте ещё раз позднее';
                    fill_errors($errors, 'file-error', $err_type, $err_heading, $err_text);
                    break;
            }
            break;

        case 'link':
            $post_link = $_POST['post-link'];
            if (!empty($post_link) and !filter_var($post_link, FILTER_VALIDATE_URL)) {
                $err_type = 'Некорректная ссылка';
                $err_heading = 'Ссылка';
                $err_text = 'Введите корректный URL';
                fill_errors($errors, 'post-link', $err_type, $err_heading, $err_text);
            }
            break;
    }

    // валидация хэштегов
    $tags_field = $_POST['tags'];

    if (!empty($tags_field)) {
        // убираются лишние пробелы
        $tags_field = trim_extra_spaces($tags_field);
        $tags = explode(' ', $tags_field);

        foreach ($tags as $tag) {
            $tag = trim($tag, '#');
            // проверка на соответствие формату
            $match = preg_match(HASHTAG, $tag);

            if (!$match) {
                $err_type = 'Недопустимые символы';
                $err_heading = 'Теги';
                $err_text = 'Теги могут состоять из букв, цифр и символа подчёркивания и должны быть разделены пробелами';
                fill_errors($errors, 'tags', $err_type, $err_heading, $err_text);
                break;
            }
        }
    }

    // Обработка полученных данных в случае правильного заполнения всех полей
    if (empty($errors)) {

        // подготовка выражения
        $sql = 'INSERT INTO post
                         (post_header, text_content, quote_origin, photo_content, video_content, link_text_content, user_id, content_type_id)
                       VALUES
                         (?, ?, ?, ?, ?, ?, ?, ?)';
        $sql_statement = mysqli_prepare($db_connection, $sql);

        // Добавление новых публикаций в базу данных
        switch ($post_type) {
            case 'text':
                $query_vars = array(
                    $_POST['post-heading'],
                    $_POST['post-text'],
                    NULL, NULL, NULL, NULL,
                    $params['user']['id'],
                    1
                );
                break;
            case 'quote':
                $query_vars = array(
                    $_POST['post-heading'],
                    $_POST['cite-text'],
                    $_POST['quote-author'],
                    NULL, NULL, NULL,
                    $params['user']['id'],
                    2
                );
                break;
            case 'photo':
                $query_vars = array(
                    $_POST['post-heading'],
                    NULL, NULL,
                    $file,
                    NULL, NULL,
                    $params['user']['id'],
                    3
                );
                break;
            case 'video':
                $query_vars = array(
                    $_POST['post-heading'],
                    NULL, NULL, NULL,
                    $_POST['video-url'],
                    NULL,
                    $params['user']['id'],
                    4
                );
                break;
            case 'link':
                $query_vars = array(
                    $_POST['post-heading'],
                    NULL, NULL, NULL, NULL,
                    $_POST['post-link'],
                    $params['user']['id'],
                    5
                );
                break;
        }

        // выполнение подготовленного выражения
        mysqli_stmt_bind_param($sql_statement, 'ssssssii', ...$query_vars);
        mysqli_stmt_execute($sql_statement);

        // сохраняем id нового поста
        $new_post_id = mysqli_insert_id($db_connection);

        // добавление тегов
        if (!empty($tags_field)) {

            $tags = explode(' ', $tags_field);

            foreach ($tags as $tag) {
                $tag = trim($tag, '#');

                // подготовка запроса для связки поста с тегами
                $sql = 'INSERT INTO post_hashtag_link
                                 (post_id, hashtag_id)
                               VALUES
                                 (?, ?)';
                $sql_statement = mysqli_prepare($db_connection, $sql);

                // проверка на уже существующие теги
                $query = "SELECT id FROM hashtag WHERE hashtag_name = '$tag'";
                $tag_id = get_data_from_db($db_connection, $query, 'one');

                if (!$tag_id) {
                    mysqli_query($db_connection, "INSERT INTO hashtag SET hashtag_name = '$tag'");
                    $tag_id = mysqli_insert_id($db_connection);
                }

                $query_vars = array(
                    $new_post_id,
                    $tag_id
                );

                // выполнение подготовленного выражения
                mysqli_stmt_bind_param($sql_statement,'ii',...$query_vars);
                mysqli_stmt_execute($sql_statement);
            }
        }

        // переадресация на страницу с созданным постом
        header('Location: post.php?post_id=' . $new_post_id);
        exit();
    } else {
        // заполняется массив с введёнными пользователем данными
        foreach ($_POST as $key => $value) {
            $values[$key] = $value;
        }
    }
}

// класс для отображения ошибки инпутов
$alert_class = 'form__input-section--error';

// подключение шаблона для отображения блоков с ошибками заполнения полей формы
$error_list = include_template('add-post_error-list.php', [
    'errors' => $errors,
]);

// подключение шаблона для отображения поля ввода заголовка
$show_error_class = (array_key_exists('post-heading', $errors)) ? $alert_class : '';
$header_field = include_template('add-post_header_template.php', [
    'post_type' => $post_type,
    'show_error_class' => $show_error_class,
    'post_heading' => $values['post-heading'] ?? '',
    'err_msg' => show_error_msg($errors, 'post-heading'),
]);

// подключение шаблона для отображения поля ввода тегов
$show_error_class = (array_key_exists('tags', $errors)) ? $alert_class : '';
$tag_field = include_template('add-post_tags_template.php', [
    'show_error_class' => $show_error_class,
    'tag_values' => $values['tags'] ?? '',
    'err_msg' => show_error_msg($errors, 'tags'),
]);

// отображение страницы
$main_content = include_template('add-post_template.php', [
    'content_types' => $content_types,
    'post_type' => $post_type,
    'header_field' => $header_field,
    'errors' => $errors,
    'alert_class' => $alert_class,
    'values' => $values,
    'error_list' => $error_list,
    'tag_field' => $tag_field,
]);

print build_page('layout.php', $params, $main_content);
