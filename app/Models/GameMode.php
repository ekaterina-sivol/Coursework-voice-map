<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class GameMode
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    // Получение списка всех игровых режимов
    public function getAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, name, description, image_path
             FROM game_modes
             ORDER BY id'
        );

        return $stmt->fetchAll();
    }

    // Получение игрового режима по идентификатору
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, description, image_path
             FROM game_modes
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $mode = $stmt->fetch();

        return $mode === false ? null : $mode;
    }
}