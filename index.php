<?php
require_once 'helpers.php';
require_once 'utils.php';
require_once 'config.php';

// Перенаправление аутентифицированного пользователя
if (isset($_SESSION['user'])) {
    header('Location: /feed.php');
    exit;
}

$post_data = []; // массив для заполнения данными из формы
$errors = []; // массив для заполнения ошибками полей формы

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // фильтрация данных формы
    $post_data = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

    // обязательные поля
    $required_fields = array(
        'email' => 'Логин',
        'password' => 'Пароль',
    );

    // обработка пустых обязательных полей
    foreach ($required_fields as $key => $value) {
        if (empty($post_data[$key])) {
            fill_errors($errors, $key, 'Пустое поле', $value, 'Это поле должно быть заполнено');
        }
    }

    // валидация e-mail
    if ($post_data['email']) {
        $email = validate_email($db_connection, $errors, $post_data['email']);

        // проверка пароля
        if($email && $post_data['password']) {
            $query = "SELECT id, user_password FROM user WHERE user_email = '$email'";
            $user = get_data_from_db($db_connection, $query, 'row');

            if (!password_verify($post_data['password'], $user['user_password'])) {
                fill_errors(
                    $errors,
                    'password',
                    'Неверный пароль',
                    'Пароль',
                    'Неверный пароль'
                );
            } else { // аутентификация пользователя при успешном вводе данных
                $_SESSION['user'] = $user['id'];

                // перенаправление пользователя на страницу, к которой он пытался обратиться, или на ленту
                header('Location:' . ($_COOKIE['prev_page'] ?? '/feed.php'));
                exit;
            }
        }
    }
}

// класс для отображения ошибки рядом с полем
$alert_class = 'form__input-section--error';

print include_template('landing.php', [
    'errors' => $errors,
    'alert_class' => $alert_class,
    'post_data' => $post_data,
]);
