<?php

// Параметры для подключения к БД

$db = [
    'host' => 'localhost',
    'user' => 'root',
    'password' => '',
    'database' => 'readme',
];

$db_connection = mysqli_connect($db['host'],
    $db['user'],
    $db['password'],
    $db['database']); // устанавливается соединение с БД

mysqli_set_charset($db_connection, 'utf8'); // Установка кодировки по ум.

if (!$db_connection) {
    $err = mysqli_connect_error();
    echo $err;
    exit();
}
