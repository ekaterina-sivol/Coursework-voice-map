<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/Autoloader.php';

use App\Core\Router;

header('Content-Type: text/html; charset=UTF-8');

// Создаём маршрутизатор
$router = new Router();

// Главная страница
$router->get('/', function (): void {
    echo '<h1>GameVote</h1>';
    echo '<p>Система голосования за карту и режим в сетевой игре</p>';
    echo '<a href="/login">Вход</a> | ';
    echo '<a href="/register">Регистрация</a>';
});
    
    // Страница авторизации
    $router->get('/login', function (): void {
        echo '<h1>Вход в GameVote</h1>';
        echo '<a href="/">На главную</a>';
    });
        
        // Страница регистрации
        $router->get('/register', function (): void {
            echo '<h1>Регистрация в GameVote</h1>';
            echo '<a href="/">На главную</a>';
        });
            
            // Обрабатываем входящий запрос
            $router->dispatch(
                $_SERVER['REQUEST_METHOD'],
                $_SERVER['REQUEST_URI']
                );