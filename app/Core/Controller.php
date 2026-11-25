<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

abstract class Controller
{
    // Отображение представления
    protected function view(
        string $view,
        array $data = []
    ): void {
        $viewPath = dirname(__DIR__) . '/Views/' . $view . '.php';

        // Проверяем существование файла представления
        if (!is_file($viewPath)) {
            throw new RuntimeException(
                'Представление не найдено: ' . $view
            );
        }

        // Передаём данные в представление
        extract($data, EXTR_SKIP);

        require $viewPath;
    }

    // Перенаправление пользователя
    protected function redirect(string $path): never
    {
        header('Location: ' . $path, true, 303);
        exit;
    }
}