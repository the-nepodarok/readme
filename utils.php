<?php

// Временные отрезки в виде констант
define('MINUTES_IN_HOUR', 60);
define('HOURS_IN_DAY', 24);
define('DAYS_IN_WEEK', 7);
define('DAYS_IN_MONTH', 30);
define('FIVE_WEEKS', 35);

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
 * @return array Массив со случайными датами в паре 'date' => 'дата'
 */

function getDates($array)
{
    for ($i = 0; $i < count($array); $i++) {
        $array[$i]['date'] = generate_random_date($i);
    }
    return $array;
}

/**
 * Приводит дату к формату "n минут/часов/etc. назад"
 *
 * @param string $date Массив с данными
 *
 * @return string Строка, отражающая количество времени, прошедшего с $date
 */

function formatDate($date)
{
    $post_date = date_create($date);
    $current_date = date_create('now');
    $diff = date_diff($current_date, $post_date);

    $days = date_interval_format($diff, '%a');
    $minutes = date_interval_format($diff, '%i');
    $hours = date_interval_format($diff, '%h');

    $result = $date;

    if ($minutes > 0 && $minutes <= MINUTES_IN_HOUR) {
        $result = ($minutes . ' ' . get_noun_plural_form($minutes, 'минута', 'минуты', 'минут') . ' назад');
    }
    if ($hours > 0 && $hours <= HOURS_IN_DAY) {
        $result = ($hours . ' ' . get_noun_plural_form($hours, 'час', 'часа', 'часов') . ' назад');
    }
    if ($days > 0 && $days <= DAYS_IN_WEEK) {
        $result = ($days . ' ' . get_noun_plural_form($days, 'день', 'дня', 'дней') . ' назад');
    }
    if ($days > DAYS_IN_WEEK && $days <= FIVE_WEEKS) {
        $weeks = floor($days / DAYS_IN_WEEK);
        $result = ($weeks . ' ' . get_noun_plural_form($weeks, 'неделю', 'недели', 'недель') . ' назад');
    }
    if ($days > FIVE_WEEKS) {
        $months = floor($days / DAYS_IN_MONTH);
        $result = ($months . ' ' . get_noun_plural_form($months, 'месяц', 'месяца', 'месяцев') . ' назад');
    }

    return $result;
}
