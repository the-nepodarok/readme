<?php

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

session_start();

// Перенаправление анонимного пользователя
if (!isset($_SESSION['user'])) {
    header('Location: /');
    exit;
}

require_once 'utils.php';
require_once 'db_config.php';
require_once 'email_config.php';

// параметр запроса пользователя
$user_id = filter_input(INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT);

// если параметр передан и пользователь не пытается подписаться на самого себя
if ($user_id and $user_id !== $_SESSION['user']['id']) {

    // проверка, что такой пользователь существует
    $user_exists = check_user($db_connection, $user_id); 

    if ($user_exists) {
        // проверка на уже существующую пару в подписках
        $query = 'SELECT id
                      FROM follower_list
                  WHERE following_user_id = ' . $_SESSION['user']['id'] . '
                  AND followed_user_id = ' . $user_id;
        $already_subscribed = get_data_from_db($db_connection, $query);

        if ($already_subscribed) {
            // запрос на отписку от пользователя
            $query = 'DELETE FROM follower_list
                      WHERE following_user_id = ' . $_SESSION['user']['id'] . '
                      AND followed_user_id = ' . $user_id;
        } else {
            // запрос на подписку на пользователя
            $query = 'INSERT INTO follower_list
                             (following_user_id, followed_user_id)
                      VALUES (' .
                $_SESSION['user']['id'] . ', ' . $user_id . ')';

            // Получение данных пользователя для e-mail оповещения
            $user_data_query = 'SELECT user_name,
                                       user_email
                                FROM user
                                WHERE id = ' . $user_id;
            $user_data = get_data_from_db($db_connection, $user_data_query, 'row');
            $user_name = $user_data['user_name'];
            $user_email = $user_data['user_email'];

            // Формирование e-mail сообщения
            $message = new Email();
            $message->to("the_lost_number@mail.ru");
            $message->from("the_lost_number@mail.ru");
            $message->subject("У вас новый подписчик");
            $message->text('Здравствуйте, ' . $user_name .
                '. На вас подписался новый пользователь ' . $_SESSION['user']['user_name'] .
                '. Вот ссылка на его профиль: readme/profile.php?user_id=' . $_SESSION['user']['id']);

            // Отправка сообщения
            $mailer = new Mailer($transport);
            $mailer->send($message);
        }
        mysqli_query($db_connection, $query);
    }
}

// переадресация на прошлую страницу
header('Location: ' . $_SESSION['prev_page']);
