<?php

date_default_timezone_set('Europe/Moscow');

/**
 * Обрезает текст до заданного предела
 *
 * @param string $string Исходный текст в виде строки
 * @param number $max_post_length Максимальное количество символов
 *
 * @return string Возвращает строку, обрезанную до $max_post_length, либо исходную строку без изменений,
 * если лимит символов не превышен
 */

function slice_string($string, $max_post_length = 300)
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

        $result_string = '<p>' . trim($result_string, '/ :–-,;') . '...</p><a class="post-text__more-link" href="#">Читать далее</a>';
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
        $result_string = '<p>' . mb_substr($temp_string, 0, mb_strripos($temp_string, ' ')) . '...</p><a class="post-text__more-link" href="#">Читать далее</a>';
    }

    return $result_string;
}

/**
 * Заменяет потенциально опасные символы на HTML-мнемоники
 *
 * @param string $string Входящий текст в виде reference-строки
 *
 * Превращает текст в безопасный для вывода на страницу
 */

function secure(string &$string)
{
    $string = htmlspecialchars($string);
}

/**
 * Генерирует случайные даты и добавляет их в виде новой записи в обрабатываемом ассоциативном массиве
 *
 * @param array $array Массив с данными
 *
 * @return array Массив с псевдослучайными датами в паре 'date' => 'дата' в формате ГГГГ-ММ-ДД ЧЧ: ММ: СС
 */

function get_dates($array)
{
    $id = 0;
    foreach ($array as &$item) {
        $item['id'] = $id;
        $item['date'] = generate_random_date($id);
        $id++;
    }
    return $array;
}

/**
 * Преобразует дату в формат «дд.мм.гггг чч: мм», необходимый для атрибута title
 *
 * @param string $date Дата в виде строки
 *
 * @return string Строка с датой в формате «дд.мм.гггг чч: мм»
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
 *
 * @return string Строка, отражающая количество времени, прошедшего с $date
 */

function format_date($date)
{
    $minutes_in_hour = 60; // Кол-во минут в 1 часе
    $hours_in_day = 24; // Кол-во часов в 1 сутках;
    $days_in_week = 7; // Кол-во дней в 1 неделе;
    $days_in_month = 30; // Кол-во дней в 1 месяце;
    $five_weeks = 35; // 5 недель;

    $post_date = date_create($date);
    $current_date = date_create('now');
    $diff = date_diff($current_date, $post_date); // Разница между current_date и post_date в виде объекта

    $minutes = $diff->i;
    $hours = $diff->h;
    $days = $diff->days;
    $years = $diff->y;

    $result = $date;

    if ($diff->invert === 0) {
        $result = "Дата ещё не наступила!";
    } else {
        if ($years > 0) {
            $result = $years . ' ' . get_noun_plural_form($years, 'год', 'года', 'лет');
        } elseif ($days >= $five_weeks) {
            $months = ceil($days / $days_in_month);
            $result = $months . ' месяц' . get_noun_plural_form($months, '', 'а', 'ев');
        } elseif ($days >= $days_in_week && $days < $five_weeks) {
            $weeks = ceil($days / $days_in_week);
            $result = $weeks . ' недел' . get_noun_plural_form($weeks, 'ю', 'и', 'ь');
        } elseif ($days > 0 && $days <= $days_in_week) {
            $result = $days . ' ' . get_noun_plural_form($days, 'день', 'дня', 'дней');
        } elseif ($hours > 0 && $hours < $hours_in_day) {
            $result = $hours . ' час' . get_noun_plural_form($hours, '', 'а', 'ов');
        } elseif ($minutes > 0 && $minutes < $minutes_in_hour) {
            $result = $minutes . ' минут' . get_noun_plural_form($minutes, 'у', 'ы', '');
        }

        $result .= ' назад';
    }

    return $result;
}

// Второй вариант функции, почти целиком на объектах

function format_date_2($date)
{
    $post_date = date_create($date);
    $current_date = date_create('now');
    $diff = date_diff($current_date, $post_date);

    $hour = DateInterval::createFromDateString('60 minutes');
    $day = DateInterval::createFromDateString('24 hours');
    $week = DateInterval::createFromDateString('1 week');
    $five_weeks = DateInterval::createFromDateString('5 weeks');
    $year = DateInterval::createFromDateString('1 year');

    $result = $date;

    if ($diff->invert === 0) {
        $result = "Дата ещё не наступила";
    } else {
        if ($diff->y >= $year->y) {
            $result = $diff->y . ' ' . get_noun_plural_form($diff->y, 'год', 'года', 'лет');
        } elseif ($diff->days >= $five_weeks->d) {
            $result = $diff->m . ' месяц' . get_noun_plural_form($diff->m, '', 'а', 'ев');
        } elseif ($diff->days >= $week->d && $diff->days < $five_weeks->d) {
            $result = ceil($diff->days / 7) . ' недел' . get_noun_plural_form(ceil($diff->days / 7), 'ю', 'и', 'ь');
        } elseif ($diff->days > 0 && $diff->days < $week->d) {
            $result = $diff->days . ' ' . get_noun_plural_form($diff->days, 'день', 'дня', 'дней');
        } elseif ($diff->h > 0 && $diff->h < $day->h) {
            $result = $diff->h . ' час' . get_noun_plural_form($diff->days, '', 'а', 'ов');
        } elseif ($diff->i > 0 && $diff->i < $hour->i) {
            $result = $diff->i . ' минут' . get_noun_plural_form($diff->i, 'у', 'ы', '');
        }

        $result .= ' назад';
    }

    return $result;
}
