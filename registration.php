<?php
require_once 'helpers.php';
require_once 'utils.php';
require_once 'db.php';

// массив с данными страницы и пользователя
$params = array(
    'is_auth' => 0,
    'page_title' => 'регистрация',
);

$post_data = []; // массив для заполнения данными из формы
$errors = []; // массив для заполнения ошибками полей формы

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // фильтрация данных формы
    $post_data = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

    // обязательные поля
    $required_fields = array(
        'email' => 'Электронная почта',
        'login' => 'Логин',
        'password' => 'Пароль',
        'password-repeat' => 'Повтор пароля',
    );

    // обработка пустых обязательных полей
    foreach ($required_fields as $key => $value) {
        if (empty($post_data[$key])) {
            fill_errors($errors, $key, 'Пустое поле', $value, 'Это поле должно быть заполнено');
        }
    }

    // валидация e-mail
    if ($post_data['email']) {
        $err_heading = 'Электронная почта';

        if (filter_var($post_data['email'], FILTER_VALIDATE_EMAIL)) {
            $email = mysqli_real_escape_string($db_connection, $post_data['email']);
            $query = "SELECT id FROM user WHERE user_email = '$email'";
            $result = get_data_from_db($db_connection, $query, 'one');

            if ($result > 0) {
                $err_type = 'Пользователь уже существует';
                $err_text = 'Пользователь с такой электронной почтой уже существует';
            }
        } else {
            $err_type = 'Некорректный email-адрес';
            $err_text = 'Введите корректный адрес электронной почты';
        }

        // заполнить массив с ошибками, если таковые возникли
        if (isset($err_text)) {
            fill_errors($errors, 'email', $err_type, $err_heading, $err_text);
        }
    }

    // проверка повторного ввода пароля
    if ($post_data['password'] and $post_data['password'] !== $post_data['password-repeat']) {
        $err_type = 'Несовпадение данных';
        $err_heading = 'Пароль';
        $err_text = 'Ввведённые пароли не совпадают';
        fill_errors($errors, 'password-repeat', $err_type, $err_heading, $err_text);
    }

    if (!empty($_FILES['userpic-file']['name'])) {
        $file = upload_image('userpic-file', $errors);
    }

    if (empty($errors)) {

        // подготовка запроса для добавления нового пользователя
        $sql = 'INSERT INTO user (
                          user_reg_dt,
                          user_email,
                          user_name,
                          user_password,
                          user_avatar
                       )
                       VALUES (NOW(), ?, ?, ?, ?)'; // 4 поля
        $stmt = mysqli_prepare($db_connection, $sql);

        // хэширование пароля
        $password = password_hash($post_data['password'], PASSWORD_DEFAULT);

        // данные для подстановки
        $query_vars = array(
            $post_data['email'],
            $post_data['login'],
            $password,
            $file ?? NULL,
        );

        // выполнение подготовленного выражения
        mysqli_stmt_bind_param($stmt, 'ssss', ...$query_vars);
        mysqli_stmt_execute($stmt);

        // переадресация на главную страницу
        header('Location: index.php');
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
    'post_data' => $post_data,
]);

print build_page('layout.php', $params, $main_content);
