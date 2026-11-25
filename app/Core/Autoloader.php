<?php

declare(strict_types=1);

// Автоматическая загрузка классов приложения
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';

    // Проверяем пространство имён класса
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    // Получаем относительное имя класса
    $relativeClass = substr($class, strlen($prefix));

    // Формируем путь к PHP-файлу
    $file = dirname(__DIR__) . '/'
        . str_replace('\\', '/', $relativeClass)
        . '.php';

    // Подключаем файл, если он существует
    if (is_file($file)) {
        require_once $file;
    }
});