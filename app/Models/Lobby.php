<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use Throwable;

class Lobby
{
    private PDO $pdo;
    
    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }
    
    // Создание нового игрового лобби
    public function create(
        string $name,
        int $creatorId,
        int $maxPlayers
        ): int {
            $this->pdo->beginTransaction();
            
            try {
                // Добавляем лобби в базу данных
                $stmt = $this->pdo->prepare(
                    'INSERT INTO lobbies
                    (name, creator_id, max_players)
                 VALUES
                    (:name, :creator_id, :max_players)'
                    );
                
                $stmt->execute([
                    'name' => $name,
                    'creator_id' => $creatorId,
                    'max_players' => $maxPlayers,
                ]);
                
                $lobbyId = (int) $this->pdo->lastInsertId();
                
                // Добавляем создателя в список участников
                $stmt = $this->pdo->prepare(
                    'INSERT INTO lobby_members
                    (lobby_id, user_id)
                 VALUES
                    (:lobby_id, :user_id)'
                    );
                
                $stmt->execute([
                    'lobby_id' => $lobbyId,
                    'user_id' => $creatorId,
                ]);
                
                $this->pdo->commit();
                
                return $lobbyId;
                
            } catch (Throwable $e) {
                $this->pdo->rollBack();
                
                throw $e;
            }
    }
    
    // Получение списка игровых лобби
    public function getAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT
                l.*,
                u.username AS creator_name,
                (
                    SELECT COUNT(*)
                    FROM lobby_members lm
                    WHERE lm.lobby_id = l.id
                ) AS player_count
             FROM lobbies l
             JOIN users u ON u.id = l.creator_id
             ORDER BY l.created_at DESC, l.id DESC'
            );
        
        return $stmt->fetchAll();
    }
    
    // Получение конкретного лобби
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                l.*,
                u.username AS creator_name,
                (
                    SELECT COUNT(*)
                    FROM lobby_members lm
                    WHERE lm.lobby_id = l.id
                ) AS player_count
             FROM lobbies l
             JOIN users u ON u.id = l.creator_id
             WHERE l.id = :id'
            );
        
        $stmt->execute([
            'id' => $id,
        ]);
        
        $lobby = $stmt->fetch();
        
        return $lobby === false ? null : $lobby;
    }
    
    // Получение списка участников лобби
    public function getMembers(int $lobbyId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                u.id,
                u.username,
                lm.joined_at
             FROM lobby_members lm
             JOIN users u ON u.id = lm.user_id
             WHERE lm.lobby_id = :lobby_id
             ORDER BY lm.joined_at, u.id'
            );
        
        $stmt->execute([
            'lobby_id' => $lobbyId,
        ]);
        
        return $stmt->fetchAll();
    }
    
    // Проверка участия пользователя в лобби
    public function isMember(
        int $lobbyId,
        int $userId
        ): bool {
            $stmt = $this->pdo->prepare(
                'SELECT 1
             FROM lobby_members
             WHERE lobby_id = :lobby_id
               AND user_id = :user_id'
                );
            
            $stmt->execute([
                'lobby_id' => $lobbyId,
                'user_id' => $userId,
            ]);
            
            return $stmt->fetchColumn() !== false;
    }
    // Присоединение пользователя к игровому лобби
    public function join(int $lobbyId, int $userId): void
    {
        $this->pdo->beginTransaction();
        
        try {
            // Получаем и блокируем запись лобби
            $stmt = $this->pdo->prepare(
                'SELECT id, status, max_players
                 FROM lobbies
                 WHERE id = :id
                 FOR UPDATE'
                );
            
            $stmt->execute([
                'id' => $lobbyId,
            ]);
            
            $lobby = $stmt->fetch();
            
            // Проверяем существование лобби
            if ($lobby === false) {
                throw new \DomainException(
                    'Лобби не найдено.'
                    );
            }
            
            // Проверяем, является ли пользователь участником
            if ($this->isMember($lobbyId, $userId)) {
                throw new \DomainException(
                    'Вы уже являетесь участником этого лобби.'
                    );
            }
            
            // Запрещаем присоединение во время голосования
            if ($lobby['status'] === 'voting') {
                throw new \DomainException(
                    'Нельзя присоединиться во время голосования.'
                    );
            }
            
            // Получаем количество участников
            $stmt = $this->pdo->prepare(
                'SELECT COUNT(*)
                 FROM lobby_members
                 WHERE lobby_id = :lobby_id'
                );
            
            $stmt->execute([
                'lobby_id' => $lobbyId,
            ]);
            
            $playerCount = (int) $stmt->fetchColumn();
            
            // Проверяем наличие свободных мест
            if ($playerCount >= (int) $lobby['max_players']) {
                throw new \DomainException(
                    'В лобби нет свободных мест.'
                    );
            }
            
            // Добавляем нового участника
            $stmt = $this->pdo->prepare(
                'INSERT INTO lobby_members
                    (lobby_id, user_id)
                 VALUES
                    (:lobby_id, :user_id)'
                );
            
            $stmt->execute([
                'lobby_id' => $lobbyId,
                'user_id' => $userId,
            ]);
            
            $this->pdo->commit();
            
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            
            throw $e;
        }
    }
}