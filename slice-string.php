<?php
/**
 * Обрезает текст до заданного предела
 *
 * @param string $string Исходный текст в виде строки
 * @param number $max_post_length Максимальное количество символов
 *
 * @return string Возвращает строку, обрезанную до $max_post_length, либо исходную строку без изменений,
 *                если лимит символов не превышен
 */

define('TRIM_CHARACTERS', ' /t/n!/,:;'); // Можно ли использовать const для объявления констант?

$text = 'Чтобы карточки оставались компактными и не занимали слишком
 много места размер содержимого надо принудительно ограничивать.
 Для фотографий и видео это можно сделать через CSS, для цитат и
 ссылок есть ограничение длины при создании поста. Остаётся текстовый контент.
 Его длина никак не ограничивается в момент создания, а так как пользователи могут
 писать очень длинные тексты, необходимо предусмотреть обрезание текста до приемлемой
 длины при показе карточки поста на странице популярного.';

function slice_string($string, $max_post_length = 300) {

    if (mb_strlen($string) <= $max_post_length) {
        return '<p>' . $string . '</p>';
    }

    $words = explode(' ', $string);
    $i = 0;
    $result_string = $words[$i]; // Во избежание пробела в начале текста

    while (mb_strlen($result_string . ' ' . $words[$i + 1]) < $max_post_length) {
        $result_string = $result_string . ' ' . $words[$i + 1];
        $i++;
    }

    $result_string = rtrim($result_string, TRIM_CHARACTERS);
    return '<p>' . $result_string . '...' . '</p>' . '<a class="post-text__more-link" href="#">Читать далее</a>';
};

print(slice_string($text));

//  Второй (более ранний) вариант функции, громоздкий, но рабочий
//
//function slice_string($string, $max_post_length = 300) {
//
//    if (mb_strlen($string) <= $max_post_length) {
//        return '<p>' . $string . '</p>';
//    }
//
//    $array_from_words = explode(' ', $string);
//    $i = 0;
//    $post_length = 0;
//
//    while ($post_length < $max_post_length) {
//        if (!isset($array_from_words[$i])) {
//            break;
//        }
//
//        $post_length += mb_strlen($array_from_words[$i]);
//        $i++;
//    }
//
//    $result_string = implode(array_slice($array_from_words, 0, $i), ' ');
//
//    if (mb_strlen($result_string) < $max_post_length) {
//        return '<p>' . $result_string . '...' . '</p>';
//    } else {
//        while (mb_strlen($result_string) > $max_post_length) {
//            $retest_array = explode(' ', $result_string);
//            array_pop($retest_array);
//            $result_string = implode($retest_array, ' ');
//        }
//
//        $result_string = rtrim($result_string, '!,-/:;');
//        return '<p>' . $result_string . '...' . '</p>' . '<a class="post-text__more-link" href="#">Читать далее</a>';
//    }
//}
//
//$sliced_text = slice_string($text);
//
//print($sliced_text);
