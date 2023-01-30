<?php
session_start();

// Перенаправление аутентифицированного пользователя
if (isset($_SESSION['user'])) {
    header('Location: /feed.php');
    exit;
}

require_once 'helpers.php';
require_once 'utils.php';
require_once 'db_config.php';

$auth_data = []; // массив для заполнения данными из формы
$errors = []; // массив для заполнения ошибками полей формы

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // фильтрация данных формы
    $auth_data = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

    // обязательные поля
    $required_fields = array(
        'email' => 'Логин',
        'password' => 'Пароль',
    );

    // обработка пустых обязательных полей
    check_if_empty($errors, $required_fields, $auth_data);

    // валидация e-mail
    if ($auth_data['email']) {
        $email_input = validate_email($errors, $auth_data['email']);
        if ($email_input and check_email($db_connection, $errors, $email_input)) {
            $email = $email_input;
        }

        // проверка пароля
        if (isset($email) && $auth_data['password']) {
            $query = "SELECT * FROM user WHERE user_email = '$email'";
            $user_data = get_data_from_db($db_connection, $query, 'row');

            // аутентификация пользователя при успешном вводе данных
            if (password_verify($auth_data['password'], $user_data['user_password'])) {
                unset($user_data['user_password']); // удаление кэша пароля из массива данных
                $_SESSION['user'] = $user_data; // запись данных в сессию

                // запись в сессию типов контента
                $content_types_query = 'SELECT * FROM content_type';
                $_SESSION['ct_types'] = get_data_from_db($db_connection, $content_types_query);
                $_SESSION['ct_types'] = array_column($_SESSION['ct_types'], null, 'id'); // реиндексация по id

                // перенаправление пользователя на страницу, к которой он пытался обратиться, или на ленту
                header('Location:' . ($_COOKIE['prev_page'] ?? '/feed.php'));
                exit;
            } else {
                fill_errors(
                    $errors,
                    'password',
                    'Неверный пароль',
                    'Пароль',
                    'Неверный пароль'
                );
            }
        }
    }
}

// класс для отображения ошибки рядом с полем
$alert_class = 'form__input-section--error';

print include_template('landing.php', [
    'errors' => $errors,
    'alert_class' => $alert_class,
    'auth_data' => $auth_data,
]);
