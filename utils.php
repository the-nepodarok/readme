<?php
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
    if (mb_strlen($string) > $max_post_length) {
        $words = explode(' ', $string);
        $i = 0;
        $result_string = '';

        while (mb_strlen($result_string . ' ' . $words[$i]) < $max_post_length) {
            $result_string = $result_string . ' ' . $words[$i];
            $i++;
        }
        $result_string = '<p>' . trim($result_string) . '...</p><a class="post-text__more-link" href="#">Читать далее</a>';
    } else {
        $result_string = '<p>' . trim($string) . '</p>';
    }

    return $result_string;
}

//  Второй вариант функции

function alt_slice_string($string, $max_post_length = 300)
{
    if (mb_strlen($string) <= $max_post_length) {
        $result_string = '<p>' . trim($string) . '</p>';
    } else {
        $temp_string = mb_substr($string, 0, $max_post_length);
        $result_string = '<p>' . trim(mb_substr($temp_string, 0, mb_strripos($temp_string, ' '))) .
            '...</p><a class="post-text__more-link" href="#">Читать далее</a>';
    }

    return trim($result_string);
}
