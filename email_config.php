<?php

use Symfony\Component\Mailer\Transport;

require_once 'vendor/autoload.php';

// Конфигурация транспорта
$dsn = 'smtp://readme_blog_noreply@list.ru:TXHUaUiH9EpyYaR2qkF1@smtp.mail.ru:465?encryption=ssl&auth_mode=login';
$transport = Transport::fromDsn($dsn);
