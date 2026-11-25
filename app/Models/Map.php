<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Map
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    // Получение списка всех игровых карт
    public function getAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, name, description, image_path
             FROM maps
             ORDER BY id'
        );

        return $stmt->fetchAll();
    }

    // Получение игровой карты по идентификатору
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, description, image_path
             FROM maps
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
        ]);

        $map = $stmt->fetch();

        return $map === false ? null : $map;
    }
}