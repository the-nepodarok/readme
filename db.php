<?php

// Параметры для подключения к БД

$db = [
    'host' => 'localhost',
    'user' => 'root',
    'password' => '',
    'database' => 'readme',
];

$db_connection = mysqli_connect( // устанавливается соединение с БД
    $db['host'],
    $db['user'],
    $db['password'],
    $db['database']
);

if (!$db_connection) {
    echo mysqli_connect_error();
    exit();
}

mysqli_set_charset($db_connection, 'utf8'); // Установка кодировки по ум.
