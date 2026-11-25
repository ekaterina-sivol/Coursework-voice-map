<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use PDOException;

class AuthController extends Controller
{
    // Отображение формы регистрации
    public function showRegister(): void
    {
        $this->view('register', [
            'errors' => [],
            'username' => '',
            'email' => '',
        ]);
    }

    // Обработка регистрации
    public function register(): void
    {
        // Проверяем защитный CSRF-токен
        $token = $_POST['csrf_token'] ?? '';
        
        if (
            !is_string($token)
            || empty($_SESSION['csrf_token'])
            || !hash_equals($_SESSION['csrf_token'], $token)
            ) {
                http_response_code(403);
                echo 'Недействительный запрос. Обновите страницу.';
                return;
            }
            
            // Проверяем типы полученных данных
            foreach (['username', 'email', 'password', 'password_confirm'] as $field) {
                if (!isset($_POST[$field]) || !is_string($_POST[$field])) {
                    http_response_code(400);
                    echo 'Некорректные данные запроса.';
                    return;
                }
            }
        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

        $errors = [];

        // Проверка имени пользователя
        if (!preg_match('/^[\p{L}\p{N}_]{3,50}$/u', $username)) {
            $errors[] = 'Имя должно содержать от 3 до 50 букв, цифр или символов подчёркивания.';
        }

        // Проверка электронной почты
        if (
            !filter_var($email, FILTER_VALIDATE_EMAIL)
            || strlen($email) > 255
        ) {
            $errors[] = 'Введите корректную электронную почту.';
        }

        // Проверка пароля
        if (strlen($password) < 8 || strlen($password) > 72
            || str_contains($password, "\0")) {
            $errors[] = 'Пароль должен содержать от 8 до 72 байт и не содержать нулевых символов.';
        }

        // Проверка совпадения паролей
        if ($password !== $passwordConfirm) {
            $errors[] = 'Пароли не совпадают.';
        }

        // Проверка уникальности данных
        if (empty($errors)) {
            $userModel = new User();

            if ($userModel->findByUsername($username) !== null) {
                $errors[] = 'Это имя пользователя уже занято.';
            }

            if ($userModel->findByEmail($email) !== null) {
                $errors[] = 'Эта электронная почта уже зарегистрирована.';
            }

            // Создание пользователя
            if (empty($errors)) {
                try {
                    $userModel->create(
                        $username,
                        $email,
                        $password
                    );

                    $this->redirect('/login?registered=1');

                } catch (PDOException $e) {
                    if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                        $errors[] = 'Имя или электронная почта уже используются.';
                    } else {
                        error_log($e->getMessage());
                        $errors[] = 'Не удалось зарегистрироваться. Попробуйте позже.';
                    }
                }
            }
        }

        // Повторное отображение формы при ошибках
        $this->view('register', [
            'errors' => $errors,
            'username' => $username,
            'email' => $email,
        ]);
    }
    
    // Отображение формы авторизации
    public function showLogin(): void
    {
        $this->view('login', [
            'errors' => [],
            'email' => '',
            'registered' => isset($_GET['registered']),
        ]);
    }
    
    // Обработка авторизации
    public function login(): void
    {
        // Проверяем защитный CSRF-токен
        $token = $_POST['csrf_token'] ?? '';
        
        if (
            !is_string($token)
            || empty($_SESSION['csrf_token'])
            || !hash_equals($_SESSION['csrf_token'], $token)
            ) {
                http_response_code(403);
                echo 'Недействительный запрос. Обновите страницу.';
                return;
            }
            
            // Проверяем типы полученных данных
            if (
                !isset($_POST['email'], $_POST['password'])
                || !is_string($_POST['email'])
                || !is_string($_POST['password'])
                ) {
                    http_response_code(400);
                    echo 'Некорректные данные запроса.';
                    return;
                }
                
                $email = trim($_POST['email']);
                $password = $_POST['password'];
                
                $errors = [];
                
                // Проверяем введённые данные
                if (
                    !filter_var($email, FILTER_VALIDATE_EMAIL)
                    || strlen($email) > 255
                    || strlen($password) < 8
                    || strlen($password) > 72
                    || str_contains($password, "\0")
                    ) {
                        $errors[] = 'Неверная электронная почта или пароль.';
                    }
                    
                    // Проверяем существование пользователя и пароль
                    if (empty($errors)) {
                        $userModel = new User();
                        $user = $userModel->findByEmail($email);
                        
                        if (
                            $user !== null
                            && password_verify($password, $user['password_hash'])
                            ) {
                                // Обновляем идентификатор сессии
                                session_regenerate_id(true);
                                
                                // Сохраняем данные авторизованного пользователя
                                $_SESSION['user_id'] = (int) $user['id'];
                                $_SESSION['username'] = $user['username'];
                                
                                // Обновляем защитный токен
                                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                                
                                // Перенаправляем на главную страницу
                                $this->redirect('/');
                            }
                            
                            $errors[] = 'Неверная электронная почта или пароль.';
                    }
                    
                    // Повторно отображаем форму при ошибках
                    $this->view('login', [
                        'errors' => $errors,
                        'email' => $email,
                        'registered' => false,
                    ]);
    }
}