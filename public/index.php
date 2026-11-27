<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/Autoloader.php';

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\LobbyController;

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

session_start();

// Создание защитного CSRF-токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

header('Content-Type: text/html; charset=UTF-8');

// Создание маршрутизатора и контроллеров
$router = new Router();
$homeController = new HomeController();
$authController = new AuthController();
$lobbyController = new LobbyController();

// Главная страница
$router->get('/', [$homeController, 'index']);

// Регистрация и авторизация
$router->get('/register', [$authController, 'showRegister']);
$router->post('/register', [$authController, 'register']);

$router->get('/login', [$authController, 'showLogin']);
$router->post('/login', [$authController, 'login']);

$router->post('/logout', [$authController, 'logout']);

// Игровые лобби
$router->get('/lobbies', [$lobbyController, 'index']);

$router->get('/lobby/create', [$lobbyController, 'showCreate']);
$router->post('/lobby/create', [$lobbyController, 'create']);

$router->get('/lobby', [$lobbyController, 'show']);
$router->post('/lobby/join', [$lobbyController, 'join']);

// Голосование
$router->post('/lobby/start', [$lobbyController, 'startVoting']);
$router->post('/lobby/vote', [$lobbyController, 'vote']);
$router->get('/lobby/status', [$lobbyController, 'votingStatus']);

// Обработка входящего HTTP-запроса
$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
    );