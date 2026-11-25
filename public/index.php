<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/Autoloader.php';

use App\Core\Router;
use App\Controllers\AuthController;

// Настройка безопасности сессий
ini_set('session.use_strict_mode', '1');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS'])
    && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);

// Запускаем сессию
session_start();

// Создаём защитный CSRF-токен
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

header('Content-Type: text/html; charset=UTF-8');

// Создаём маршрутизатор и контроллер
$router = new Router();
$authController = new AuthController();

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
        
        if (isset($_GET['registered'])) {
            echo '<p>Регистрация прошла успешно!</p>';
        }
        
        echo '<a href="/">На главную</a>';
    });
        
        // Маршруты регистрации
        $router->get('/register', [$authController, 'showRegister']);
        $router->post('/register', [$authController, 'register']);
        
        // Обрабатываем входящий запрос
        $router->dispatch(
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI']
            );