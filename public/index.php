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
    
    // Проверяем авторизацию пользователя
    if (isset($_SESSION['user_id'])) {
        $username = htmlspecialchars(
            $_SESSION['username'],
            ENT_QUOTES,
            'UTF-8'
            );
        
        $token = htmlspecialchars(
            $_SESSION['csrf_token'],
            ENT_QUOTES,
            'UTF-8'
            );
        
        echo '<h2>Добро пожаловать, ' . $username . '!</h2>';
        echo '<p>Вы успешно вошли в аккаунт.</p>';
        
        echo '<form action="/logout" method="POST">';
        echo '<input type="hidden" name="csrf_token" value="' . $token . '">';
        echo '<button type="submit">Выйти из аккаунта</button>';
        echo '</form>';
    } else {
        echo '<a href="/login">Вход</a> | ';
        echo '<a href="/register">Регистрация</a>';
    }
});
    
    // Маршруты регистрации
    $router->get('/register', [$authController, 'showRegister']);
    $router->post('/register', [$authController, 'register']);
    
    // Маршруты авторизации
    $router->get('/login', [$authController, 'showLogin']);
    $router->post('/login', [$authController, 'login']);
    
    // Маршрут выхода из аккаунта
    $router->post('/logout', [$authController, 'logout']);
    
    // Обрабатываем входящий запрос
    $router->dispatch(
        $_SERVER['REQUEST_METHOD'],
        $_SERVER['REQUEST_URI']
        );