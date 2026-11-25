<?php

declare(strict_types=1);

header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameVote</title>
</head>
<body>

    <h1>GameVote</h1>

    <p>Система голосования за карту и режим в сетевой игре</p>

    <h2>Проверка серверного окружения</h2>

    <p>PHP успешно запущен.</p>

    <p>Версия PHP: <?= PHP_VERSION ?></p>

    <p>Расширение PDO MySQL:
        <?= extension_loaded('pdo_mysql') ? 'Подключено' : 'Не подключено' ?>
    </p>

</body>
</html>