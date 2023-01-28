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
    foreach ($required_fields as $key => $value) {
        if (empty($auth_data[$key])) {
            fill_errors($errors, $key, 'Пустое поле', $value, 'Это поле должно быть заполнено');
        }
    }

    // валидация e-mail
    if ($auth_data['email']) {
        $email_input = validate_email($errors, $auth_data['email']);
        if ($email_input and check_email($db_connection, $errors, $email_input)) {
            $email = $email_input;
        }

        // проверка пароля
        if (isset($email) && $auth_data['password']) {
            $query = "SELECT id, user_password FROM user WHERE user_email = '$email'";
            $user = get_data_from_db($db_connection, $query, 'row');

            // аутентификация пользователя при успешном вводе данных
            if (password_verify($auth_data['password'], $user['user_password'])) {
                $query = "SELECT id,
                                 user_reg_dt,
                                 user_email,
                                 user_name,
                                 user_avatar
                          FROM user
                          WHERE user_email = '$email'";

                $user_data = get_data_from_db($db_connection, $query, 'row');
                $_SESSION['user'] = $user_data;

                // запись в сессию типов контента
                $content_types_query = 'SELECT * FROM content_type';
                $_SESSION['ct_types'] = get_data_from_db($db_connection, $content_types_query);

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
