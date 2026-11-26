<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/Autoloader.php';

use App\Core\Router;
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

$authController = new AuthController();
$lobbyController = new LobbyController();

// Главная страница
$router->get('/', function (): void {
    
    echo '<h1>GameVote</h1>';
    
    echo '<p>Система голосования за карту и режим в сетевой игре</p>';
    
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
        
        echo '<p><a href="/lobbies">Игровые лобби</a></p>';
        
        echo '<form action="/logout" method="POST">';
        
        echo '<input type="hidden" name="csrf_token" value="'
            . $token . '">';
            
            echo '<button type="submit">Выйти из аккаунта</button>';
            
            echo '</form>';
            
    } else {
        
        echo '<a href="/login">Вход</a> | ';
        
        echo '<a href="/register">Регистрация</a>';
    }
});
    
    // Маршруты регистрации и авторизации
    $router->get('/register', [$authController, 'showRegister']);
    
    $router->post('/register', [$authController, 'register']);
    
    $router->get('/login', [$authController, 'showLogin']);
    
    $router->post('/login', [$authController, 'login']);
    
    $router->post('/logout', [$authController, 'logout']);
    
    // Маршруты игровых лобби
    $router->get('/lobbies', [$lobbyController, 'index']);
    
    $router->get('/lobby/create', [$lobbyController, 'showCreate']);
    
    $router->post('/lobby/create', [$lobbyController, 'create']);
    
    $router->get('/lobby', [$lobbyController, 'show']);
    
    $router->post('/lobby/join', [$lobbyController, 'join']);
    
    $router->post('/lobby/start', [$lobbyController, 'startVoting']);
    
    // Обработка входящего HTTP-запроса
    $router->dispatch(
        $_SERVER['REQUEST_METHOD'],
        $_SERVER['REQUEST_URI']
        );