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
}