<?php

declare(strict_types=1);

// Параметры подключения к базе данных
return [
    'host' => getenv('DB_HOST') ?: 'db',
    'port' => getenv('DB_PORT') ?: '3306',
    'dbname' => getenv('DB_NAME') ?: 'gamevote',
    'username' => getenv('DB_USER') ?: 'gamevote_user',
    'password' => getenv('DB_PASSWORD'),
    'charset' => 'utf8mb4',
];