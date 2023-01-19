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
$post_type_options = array_column($content_types, 'type_val'); // перечень допустимых параметров

// функция для проверки на допустимое значение параметра post_type
$filter_post_type = function ($type, $options) {
    if (!in_array($type, $options)) {
        $type = 'text';
    }
    return $type;
};

$post_data = []; // массив для заполнения данными из формы
$errors = []; // массив для заполнения ошибками полей формы

// обработка данных формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // фильтрация данных формы
    $post_data = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
    $post_type = $post_data['post_type']; // тип поста
    $post_type = $filter_post_type($post_type, $post_type_options); // проверка параметра

    // выборка обязательных для заполнения полей
    switch ($post_type) {
        case 'text':
            $required_fields = [
                'post-text' => 'Текст поста',
            ];
            break;
        case 'quote':
            $required_fields = [
                'post-text' => 'Текст цитаты',
                'quote-author' => 'Автор',
            ];
            break;
        case 'video':
            $required_fields = [
                'video-url' => 'Ссылка YouTube',
            ];
            break;
        case 'link':
            $required_fields = [
                'post-link' => 'Ссылка',
            ];
            break;
        default:
            $required_fields = [];
    }

    // заголовок - общее обязательное поле для всех
    $required_fields = ['post-heading' => 'Заголовок'] + $required_fields;

    // обработка пустых обязательных полей
    foreach ($required_fields as $key => $value) {
        if (empty($post_data[$key])) {
            fill_errors($errors, $key, 'Пустое поле', $value, 'Это поле должно быть заполнено');
        }
    }

    // валидация ссылок и загрузка файлов
    switch ($post_type) {
        case 'video':
            // прервать дальнейшую проверку, если поле со ссылкой на видео пустое
            if (!$post_data['video-url']) {
                break;
            }
            // приведение ссылки к общему протоколу https
            $video_url = trim_link($post_data['video-url']);
            if ($video_url) {
                if (check_url($video_url, PHP_URL_PATH)) {
                    $yt_check = check_youtube_url($video_url);
                    if ($yt_check !== true) {
                        $err_type = 'Не найдено видео по ссылке';
                        $err_heading = 'Ссылка YouTube';
                        $err_text = $yt_check;
                    }
                } else {
                    $err_type = 'Недействительная ссылка';
                    $err_heading = 'Ссылка YouTube';
                    $err_text = 'Введите корректную ссылку на видео с YouTube';
                }
            }
            break;

        case 'photo':
            $file_photo = $_FILES[UPLOAD_IMG_NAME];
            $file_attached = !empty($file_photo['name']); // был ли приложен файл
            if ($file_attached) {
                $file_error = $file_photo['error'];
                $err_heading = 'Изображение';
                switch ($file_error) {
                    case UPLOAD_ERR_OK:
                        if (validate_file(UPLOAD_IMG_NAME)) {
                            // проверка размера файла
                            if ($file_photo['size'] > MAX_FILE_SIZE) {
                                $err_type = 'Размер файла';
                                $err_text = 'Размер файла не должен превышать ' . MAX_FILE_SIZE_USER . ' Мб';
                            } else {
                                // происходит загрузка файла
                                $file_name = $file_photo['name'];
                                $file_path = UPLOAD_PATH . $file_name;
                                // перемещение файла в папку и обработка ошибки перемещения
                                if (move_uploaded_file($file_photo['tmp_name'], $file_path)) {
                                    $file = $file_name;
                                } else {
                                    $err_type = 'Ошибка при копировании файла';
                                    $err_text = 'Не удалось загрузить файл, попробуйте снова позднее';
                                }
                            }
                        } else {
                            $err_type = 'Неверный тип файла';
                            $err_text = 'Неверный тип файла. Загрузите изображение в формате jpg, png или gif';
                        }
                        break;
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $err_type = 'Размер файла';
                        $err_text = 'Размер файла не должен превышать ' . MAX_FILE_SIZE_USER . ' Мб';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                    case UPLOAD_ERR_NO_FILE:
                        $err_type = 'Файл отсутствует';
                        $err_text = 'Файл не был загружен или загрузился с ошибками. Попробуйте ещё раз';
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                    case UPLOAD_ERR_CANT_WRITE:
                    case UPLOAD_ERR_EXTENSION:
                        $err_type = 'Не удалось записать файл';
                        $err_text = 'Ошибка сервера или PHP-модуля. Пожалуйста, попробуйте ещё раз позднее';
                        break;
                }
            } elseif ($post_data['photo-url']) { // если файл не загружен и заполнено поле со ссылкой
                // приведение ссылки к общему протоколу https
                $file_url = trim_link($post_data['photo-url']);
                // проверка ссылки на path
                if (check_url($file_url, PHP_URL_PATH)) {
                    // валидация по типу и размеру файла по ссылке
                    $file_from_url = download_file_from_url($file_url, $errors);
                    if ($file_from_url) {
                        $file = $file_from_url;
                    }
                } else { // если ссылка неверная или нерабочая
                    $err_type = 'Файл отсутствует';
                    $err_heading = 'Ссылка из интернета';
                    $err_text = 'Не удалось получить файл, убедитесь в правильности ссылки';
                }
            } else { // если файл не был загружен ни из файловой системы, ни по ссылке
                $err_type = 'Файл не был загружен';
                $err_heading = 'Нет файла';
                $err_text = 'Воспользуйтесь полем для загрузки файла или вставьте ссылку на изображение';
            }
            break;

        case 'link':
            // прервать дальнейшую проверку, если поле для ссылки пустое
            if (!$post_data['post-link']) {
                break;
            }
            // приведение к общему протоколу https:
            $post_link = trim_link($post_data['post-link']);
            // проверка валидности и доступности ссылки
            if ($post_link and !check_url($post_link)) {
                $err_type = 'Некорректная ссылка';
                $err_heading = 'Ссылка';
                $err_text = 'Введите корректный URL';
            }
            break;
    }

    // заполнить массив с ошибками, если такие возникли
    if (isset($err_text)) {
        fill_errors($errors, 'file-error', $err_type, $err_heading, $err_text);
    }

    // валидация хэштегов
    $tags_field = $post_data['tags'];

    if (!empty($tags_field)) {
        $tags_field = trim_extra_spaces($tags_field); // убираются лишние пробелы
        $tags = explode(' ', $tags_field); // разбивка на отдельные теги по пробелу

        foreach ($tags as $tag) {
            // проверка на соответствие формату
            $match = preg_match('/^#(\d|[A-zА-я]|_)+$/', $tag);

            if ($match === 0) {
                $err_type = 'Недопустимые символы';
                $err_heading = 'Теги';
                $err_text = 'Теги должны начинаться с #, могут состоять из букв, цифр и символа подчёркивания и разделены пробелами';
                fill_errors($errors, 'tags', $err_type, $err_heading, $err_text);
                break;
            } elseif (!$match) { // если по какой-то причине preg_match вернул false
                $err_type = 'Ошибка алгоритма';
                $err_heading = 'Теги';
                $err_text = 'Что-то пошло не так, попробуйте ещё раз';
                fill_errors($errors, 'tags', $err_type, $err_heading, $err_text);
                break;
            }
        }
    }

    // Обработка полученных данных в случае правильного заполнения всех полей и добавление новой публикации в БД
    if (empty($errors)) {

        // получение типа контента в зав-ти от формы
        $query = "SELECT id
                  FROM content_type
                  WHERE type_val = '$post_type'";
        $content_type_id = get_data_from_db($db_connection, $query, 'one');

        // подготовка выражения
        $query = "INSERT INTO post (
                            post_header,
                            text_content,
                            quote_origin,
                            photo_content,
                            video_content,
                            link_text_content,
                            user_id,
                            content_type_id
                         )
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"; // 8 полей
        $stmt = mysqli_prepare($db_connection, $query);

        // данные для подстановки
        $query_vars = array(
            $post_data['post-heading'],
            $post_data['post-text'] ?? NULL,
            $post_data['quote-author'] ?? NULL,
            $file ?? NULL,
            $post_data['video-url'] ?? NULL,
            $post_data['post-link'] ?? NULL,
            $params['user']['id'],
            $content_type_id
        );

        // выполнение подготовленного выражения
        mysqli_stmt_bind_param($stmt, 'ssssssii', ...$query_vars);
        mysqli_stmt_execute($stmt);

        // сохраняем id нового поста
        $new_post_id = mysqli_insert_id($db_connection);

        // добавление тегов
        if ($tags) {
            foreach ($tags as $tag) {
                $tag = trim($tag, '#');

                // подготовка запроса для связки поста с тегами
                $sql = 'INSERT INTO post_hashtag_link
                                 (post_id, hashtag_id)
                               VALUES
                                 (?, ?)';
                $stmt = mysqli_prepare($db_connection, $sql);

                // проверка на уже существующие теги
                $query = "SELECT id FROM hashtag WHERE hashtag_name = '$tag'";
                $tag_id = get_data_from_db($db_connection, $query, 'one');

                // если такого тега ещё нет, добавить его и взять его id
                if (!$tag_id) {
                    mysqli_query($db_connection, "INSERT INTO hashtag SET hashtag_name = '$tag'");
                    $tag_id = mysqli_insert_id($db_connection);
                }

                $query_vars = array(
                    $new_post_id,
                    $tag_id
                );

                // выполнение подготовленного выражения
                mysqli_stmt_bind_param($stmt,'ii',...$query_vars);
                mysqli_stmt_execute($stmt);
            }
        }

        // переадресация на страницу с созданным постом
        header('Location: post.php?post_id=' . $new_post_id);
        exit;
    }
} else {
    // параметр типа добавляемой публикации
    $post_type = filter_input(INPUT_GET, 'post_type', FILTER_SANITIZE_STRING);

    // проверка параметра или задание значения по умолчанию
    $post_type = $filter_post_type($post_type, $post_type_options);
}

// класс для отображения ошибки рядом с полем
$alert_class = 'form__input-section--error';

// подключение шаблона для отображения блоков с ошибками заполнения справа от формы
if ($errors) {
    $error_list = include_template('add-post_error-list.php', [
        'errors' => $errors,
    ]);
}

// подключение шаблона для отображения поля ввода заголовка
$show_error_class = (array_key_exists('post-heading', $errors)) ? $alert_class : '';
$header_field = include_template('add-post_header_template.php', [
    'post_type' => $post_type,
    'show_error_class' => $show_error_class,
    'post_heading' => $post_data['post-heading'] ?? '',
    'err_msg' => show_error_msg($errors, 'post-heading'),
]);

// подключение шаблона для отображения поля ввода тегов
$show_error_class = (array_key_exists('tags', $errors)) ? $alert_class : '';
$tag_field = include_template('add-post_tags_template.php', [
    'show_error_class' => $show_error_class,
    'tag_values' => $post_data['tags'] ?? '',
    'err_msg' => show_error_msg($errors, 'tags') ?? '',
]);

// отображение страницы
$main_content = include_template('add-post_template.php', [
    'content_types' => $content_types,
    'post_type' => $post_type,
    'header_field' => $header_field,
    'errors' => $errors,
    'alert_class' => $alert_class,
    'post_data' => $post_data,
    'error_list' => $error_list ?? '',
    'tag_field' => $tag_field,
]);

print build_page('layout.php', $params, $main_content);
