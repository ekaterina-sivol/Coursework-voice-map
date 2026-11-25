<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    // Регистрация GET-маршрута
    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    // Регистрация POST-маршрута
    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    // Добавление маршрута
    private function addRoute(
        string $method,
        string $path,
        callable $handler
    ): void {
        $this->routes[$method][$path] = $handler;
    }

    // Обработка входящего HTTP-запроса
    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);

        // Получаем путь без параметров запроса
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = rtrim($path, '/') ?: '/';

        // Проверяем наличие маршрута
        if (isset($this->routes[$method][$path])) {
            $handler = $this->routes[$method][$path];
            $handler();
            return;
        }

        // Проверяем, существует ли путь для другого HTTP-метода
        $allowedMethods = [];

        foreach ($this->routes as $routeMethod => $routes) {
            if (isset($routes[$path])) {
                $allowedMethods[] = $routeMethod;
            }
        }

        if (!empty($allowedMethods)) {
            http_response_code(405);
            header('Allow: ' . implode(', ', $allowedMethods));
            echo 'Метод запроса не поддерживается.';
            return;
        }

        // Маршрут не найден
        http_response_code(404);
        echo 'Страница не найдена.';
    }
}