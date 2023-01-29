<?php
session_start();

// Перенаправление аутентифицированного пользователя
if (isset($_SESSION['user'])) {
    header('Location: /');
    exit;
}

require_once 'helpers.php';
require_once 'utils.php';
require_once 'db_config.php';

// массив с данными страницы
$params = array(
    'page_title' => 'регистрация',
);

$reg_data = []; // массив для заполнения данными из формы
$errors = []; // массив для заполнения ошибками полей формы

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // фильтрация данных формы
    $reg_data = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

    // обязательные поля
    $required_fields = array(
        'email' => 'Электронная почта',
        'login' => 'Логин',
        'password' => 'Пароль',
        'password-repeat' => 'Повтор пароля',
    );

    // обработка пустых обязательных полей
    check_if_empty($errors, $required_fields, $reg_data);

    // валидация e-mail
    if ($reg_data['email']) {
        $email_input = validate_email($errors, $reg_data['email']);
        if ($email_input and check_email($db_connection, $errors, $email_input, true)) {
            $email = $email_input;
        }
    }

    // проверка повторного ввода пароля
    if ($reg_data['password'] and $reg_data['password'] !== $reg_data['password-repeat']) {
        $err_type = 'Несовпадение данных';
        $err_heading = 'Пароль';
        $err_text = 'Ввведённые пароли не совпадают';
        fill_errors($errors, 'password-repeat', $err_type, $err_heading, $err_text);
    }

    // валидация и загрузка изображения
    if ($_FILES[NEW_USER_IMG_NAME]['name']) {
        $file = upload_image($errors, NEW_USER_IMG_NAME);
    }

    // добавление нового пользователя
    if (empty($errors)) {

        // подготовка запроса
        $sql = 'INSERT INTO user (
                      user_email,
                      user_name,
                      user_password,
                      user_avatar
                   )
                VALUES (?, ?, ?, ?)'; // 4 поля
        $stmt = mysqli_prepare($db_connection, $sql);

        // хэширование пароля
        $password = password_hash($reg_data['password'], PASSWORD_DEFAULT);

        // данные для подстановки
        $query_vars = array(
            $email,
            $reg_data['login'],
            $password,
            $file ?? NULL,
        );

        // выполнение подготовленного выражения
        mysqli_stmt_bind_param($stmt, 'ssss', ...$query_vars);
        mysqli_stmt_execute($stmt);

        // переадресация на главную страницу
        header('Location: /');
        exit;
    }
}

// класс для отображения ошибки рядом с полем
$alert_class = 'form__input-section--error';

// подключение шаблона для отображения блоков с ошибками заполнения справа от формы
if ($errors) {
    $error_list = include_template('form_error-list.php', [
        'errors' => $errors,
    ]);
}

$main_content = include_template('registration_template.php', [
    'errors' => $errors,
    'alert_class' => $alert_class,
    'error_list' => $error_list ?? '',
    'reg_data' => $reg_data,
]);

print build_page('layout.php', $params, $main_content);
