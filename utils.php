<?php

define('HASHTAG', '/^(\d|[a-zA-Zа-яА-Я]|_)+$/');

// массив с допустимыми для загрузки в форме типами файлов
define('ALLOWED_IMG_TYPES', array(
    'jpg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
));

// константа для пути сохранения файлов, загружаемых из формы
define('UPLOAD_PATH', 'uploads/');

// часовой пояс по умолчанию
date_default_timezone_set('Europe/Moscow');

/**
 * Обрезает текст до заданного предела
 *
 * @param string $string Исходный текст в виде строки
 * @param string $link Текст ссылки для перехода к полному тексту
 * @param number $max_post_length Максимальное количество символов
 * @return string Возвращает строку, обрезанную до $max_post_length, либо исходную строку без изменений,
 * если лимит символов не превышен
 */
function slice_string($string, $link = '', $max_post_length = 300)
{
    $result_string = trim($string);

    if (mb_strlen($result_string) > $max_post_length) {
        $words = explode(' ', $result_string);
        $i = 0;
        $result_string = '';

        while (mb_strlen($result_string . ' ' . $words[$i]) < $max_post_length) {
            $result_string .= ' ' . $words[$i];
            $i++;
        }

        $result_string = trim(
                $result_string,
                '/ :–-,;'
            ) . '...' . '<a class="post-text__more-link" href="' . $link . '">Читать далее</a>';
        // trim нужен здесь, потому что пробел в начале параграфа добавляется на этапе цикла и trim в начале (равно как и в конце) не поможет;
        // знаки препинания я всё-таки убираю, потому что в задании как бы требуется, чтобы строка обрезалась именно по слову;
    }

    return $result_string;
}

//  Второй вариант функции
function slice_string_2($string, $max_post_length = 300)
{
    $result_string = trim($string);

    if (mb_strlen($result_string) > $max_post_length) {
        $temp_string = mb_substr($string, 0, $max_post_length);
        $result_string = '<p>' . mb_substr(
                $temp_string,
                0,
                mb_strripos($temp_string, ' ')
            ) . '...</p><a class="post-text__more-link" href="#">Читать далее</a>';
    }

    return $result_string;
}

/**
 * Заменяет потенциально опасные символы в являющемся строкой элементе на HTML-мнемоники, делая текст безопасным для вывода на страницу
 * @param mixed $value Входящий элемент любого типа
 */
function secure(&$value)
{
    if (is_string($value)) {
        $value = htmlspecialchars($value);
    }
}

/**
 * Преобразует дату в формат «дд.мм.гггг чч:мм», необходимый для атрибута title
 *
 * @param string $date Дата в виде строки
 * @return string Строка с датой в формате «дд.мм.гггг чч:мм»
 */
function get_title_date($date)
{
    $date = strtotime($date);
    return date('d.m.Y H:i', $date);
}

/**
 * Получает интервал времени с прошедшего до текущего момента времени в формате "n минут/часов/etc. назад"
 *
 * - если до текущего времени прошло меньше 60 минут, то формат будет вида «% минут назад»;
 * - если до текущего времени прошло больше 60 минут, но меньше 24 часов, то формат будет вида «% часов назад»;
 * - если до текущего времени прошло больше 24 часов, но меньше 7 дней, то формат будет вида «% дней назад»;
 * - если до текущего времени прошло больше 7 дней, но меньше 5 недель, то формат будет вида «% недель назад»;
 * - если до текущего времени прошло больше 5 недель, то формат будет вида «% месяцев назад».
 * - если до текущего времени прошло больше 1 года, то формат будет вида «% лет назад».
 *
 * @param string $date Дата, с которой начинается отсчёт
 * @return string Строка, отражающая количество времени, прошедшего с $date
 */
function format_date($date)
{
    $post_date = date_create($date);
    $current_date = date_create('now');
    $diff = date_diff($current_date, $post_date); // Разница между current_date и post_date в виде объекта

    $result = '';

    if ($diff->invert === 0) {
        $result = 'Дата ещё не наступила!';
    } else {
        $minutes_in_hour = 60; // Кол-во минут в 1 часе
        $hours_in_day = 24; // Кол-во часов в 1 сутках;

        $minutes = $diff->i;
        $hours = $diff->h;
        $days = $diff->days;

        if ($days === 0) {
            if ($minutes >= $minutes_in_hour / 2) { // часы округляются вверх
                $hours++;
            }

            $result = $hours ?
                $hours . ' час' . get_noun_plural_form($hours, '', 'а', 'ов')
                :
                $minutes . ' минут' . get_noun_plural_form($minutes, 'у', 'ы', '');
        } else {
            $days_in_week = 7; // Кол-во дней в 1 неделе;
            $days_in_month = 30; // Кол-во дней в 1 месяце;
            $days_in_year = 365; // Кол-во дней в 1 году;
            $five_weeks = 35; // 5 недель;

            $years = $diff->y;

            if ($hours >= $hours_in_day / 2) { // дни округляются вверх
                $days++;
            }

            if ($days < $days_in_week) {
                $result = $days . ' ' . get_noun_plural_form($days, 'день', 'дня', 'дней');
            } elseif ($days < $five_weeks) {
                $weeks = round($days / $days_in_week);
                $result = $weeks . ' недел' . get_noun_plural_form($weeks, 'ю', 'и', 'ь');
            } elseif ($days < $days_in_year) {
                $months = round($days / $days_in_month);
                $result = $months . ' месяц' . get_noun_plural_form($months, '', 'а', 'ев');
            } elseif ($years) {
                $years = ($days >= $days_in_year / 2) ? $years++ : $years;
                $result = $years . ' ' . get_noun_plural_form($years, 'год', 'года', 'лет');
            }
        }
    }
    return $result;
}

/**
 * Выполняет запрос в базу данных
 *
 * @param mysqli $src_db Подключение к БД
 * @param string $query Текст запроса
 * @param string $mode Режим выполнения функции:
 *        'all' - для вывода всех данных в виде двумерного массива,
 *        'row' - для вывода одной строки данных в виде одномерного ассоц. массива,
 *        'col' - для вывода всех значений искомого поля в виде нумерованного массива,
 *        'one' - для вывода одного значения поля в виде строки
 * @return mixed Полученные данные в виде, заданном режимом $mode
 */
function get_data_from_db(mysqli $src_db, string $query, string $mode = 'all')
{
    $result = mysqli_query($src_db, $query);

    if (!$result) {
        echo mysqli_error($src_db);
        exit();
    }

    switch ($mode) {
        case 'all':
            $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
            break;
        case 'row':
            $data = mysqli_fetch_assoc($result);
            break;
        case 'col':
            $data = mysqli_fetch_all($result);
            $data = array_column($data, 0);
            break;
        case 'one':
            $data = mysqli_fetch_row($result)[0];
            break;
        default:
            $data = [];
    }

    return $data;
}

/**
 * Приводит ссылки к единому виду, обрезая протокол
 *
 * @param string $link_text Текст ссылки
 */
function trim_link(string $link_text): string
{
    $scheme = parse_url($link_text, PHP_URL_SCHEME);

    if ($scheme) {
        $link_text = 'https' . str_replace($scheme, '', $link_text);
    } else {
        $link_text = 'https://' . $link_text;
    }

    return $link_text;
}

/**
 * Подгатавливает шаблон страницы
 *
 * @param string $page Название файла шаблона
 * @param array $params Массив с данными для передачи из сценария в шаблон
 * @param string $main_content Основное содержимое страницы, передаваемое в шаблон
 */
function build_page($page, $params, $main_content): string
{
    return include_template($page, $params + ['main_content' => $main_content]);
}

/**
 * Валидирует загруженный файл
 *
 * @param string $page Название поля input, из которого загружается файл
 * @param array $allowed_types Массив с разрешёнными MIME-типами файлов
 * @return boolean Булево значение проверки
 */
function validate_file($field_name)
{
    $validity = false;

    $file_name = $_FILES[$field_name]['tmp_name'];
    $file_type = mime_content_type($file_name);

    if (in_array($file_type, ALLOWED_IMG_TYPES)) {
        $validity = true;
    }

    return $validity;
}

/**
 * Загружает файл из input с типом file и перемещает полученный файл в установленную папку
 *
 * @param string $field_name Ключ массива $_FILES
 * @return string Конечное имя файла; выводит сообщение об ошибке при неудачной попытке записать файл
 */
function upload_file($field_name)
{
    $file_name = $_FILES[$field_name]['name'];
    $file_path = UPLOAD_PATH . $file_name;

    try {
        move_uploaded_file($_FILES[$field_name]['tmp_name'], $file_path);
    } catch (Exception $exc) {
        echo('Не удалось загрузить файл! Попробуйте снова.');
    } finally {
        return $file_name;
    }
}

/**
 * Загружает файл по ссылке из текстового поля формы и перемещает полученный файл в установленную папку
 *
 * @param string $field Название (name) поля формы
 * @return mixed Конечное имя загруженного файла или false, если файл не прошёл одну из проверок
 */
function download_file_from_url($field)
{
    $file_src = filter_input(INPUT_POST, $field, FILTER_VALIDATE_URL);
    $file_extension = pathinfo($file_src)['extension'];

    // проверка на текстовое расширение в ссылке
    if (array_key_exists($file_extension, ALLOWED_IMG_TYPES)) {
        if ($file_extension === 'jpeg') {
                $file_extension = 'jpg';
            }

        $file_name = uniqid($field . '_') . ".$file_extension";
        $file_path = UPLOAD_PATH . $file_name;

        file_put_contents($file_path, fopen($file_src, 'r'));

        $file_type = mime_content_type($file_path);

        // проверка настоящего типа файла
        if (in_array($file_type, ALLOWED_IMG_TYPES)) {
            return $file_name;
        } else {
            unlink($file_path);
        }
    }

    return false;
}

/**
 * Проверят валидность ссылки с обязательной частью path после адреса хоста
 *
 * @param string $url Ссылка
 * @return boolean Валидность ссылки
 */
function check_url($url)
{
    $url_validity = false;

    if (parse_url($url, PHP_URL_PATH) && filter_var($url, FILTER_VALIDATE_URL)) {

        // проверка доступности и работоспособности ссылки
        $response = get_headers($url);
        if (stripos($response[0], "200 OK")) {
            $url_validity = true;
        }
    }

    return $url_validity;
}

/**
 * Формирует и выводит шаблон блока с текстом ошибки заполнения поля на основе массива ошибок
 *
 * @param array $err Массив с ошибками
 * @param string $field_name Название поля
 * @return string Итоговый HTML-шаблон
 */
function show_error_msg($err, $field_name) {
    $params = [];

    if (array_key_exists($field_name, $err)) {
        $err_msg = isset($err[$field_name]) ? '<b>' . $err[$field_name]['heading'] . '</b>. ' . $err[$field_name]['text'] : '';
        $params = array(
            'err_msg' => $err_msg,
            'error_type' => $err[$field_name]['type'],
        );
    }

    return include_template('add-post_error_template.php', $params);
}

/**
 * Удаляет лишние (повторяющиеся) пробелы в строке
 *
 * @param string $str Строка
 */
function trim_extra_spaces($str) {
    return trim(preg_replace('/\s+/',' ', $str));
}

/**
 * Заполняет массив с ошибками
 *
 * @param array $err Массив для заполнения
 * @param string $field Название поля
 * @param string $type Тип ошибки
 * @param string $heading Заголовок ошибки
 * @param string $text Пояснительный текст ошибки
 */
function fill_errors(&$err, $field, $type, $heading, $text) {
    $err[$field]['type'] = $type;
    $err[$field]['heading'] = $heading;
    $err[$field]['text'] = $text;
}
