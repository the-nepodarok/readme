<?php

use Symfony\Component\Mailer\Transport;

require_once 'vendor/autoload.php';

// Конфигурация транспорта
$dsn = 'smtp://the_lost_number@mail.ru:2TQ0bCha36D4Q31eSG3j@smtp.mail.ru:465?encryption=ssl&auth_mode=login';
$transport = Transport::fromDsn($dsn);
