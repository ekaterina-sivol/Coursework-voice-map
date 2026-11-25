<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class User
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    // Поиск пользователя по электронной почте
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM users WHERE email = :email'
        );

        $stmt->execute([
            'email' => $email,
        ]);

        $user = $stmt->fetch();

        return $user === false ? null : $user;
    }

    // Поиск пользователя по имени
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM users WHERE username = :username'
        );

        $stmt->execute([
            'username' => $username,
        ]);

        $user = $stmt->fetch();

        return $user === false ? null : $user;
    }

    // Поиск пользователя по идентификатору
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, username, email, created_at
             FROM users WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $user = $stmt->fetch();

        return $user === false ? null : $user;
    }

    // Создание нового пользователя
    public function create(
        string $username,
        string $email,
        string $password
    ): int {
        // Хешируем пароль перед сохранением
        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $stmt = $this->pdo->prepare(
            'INSERT INTO users (username, email, password_hash)
             VALUES (:username, :email, :password_hash)'
        );

        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}