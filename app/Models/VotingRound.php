<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use Throwable;
use DomainException;

class VotingRound
{
    private PDO $pdo;

    // Продолжительность голосования в секундах
    private const VOTING_DURATION = 60;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    // Запуск нового раунда голосования
    public function start(int $lobbyId, int $userId): int
    {
        $this->pdo->beginTransaction();

        try {

            // Получаем и блокируем запись лобби
            $stmt = $this->pdo->prepare(
                'SELECT id, creator_id, status
                 FROM lobbies
                 WHERE id = :id
                 FOR UPDATE'
            );

            $stmt->execute(['id' => $lobbyId]);

            $lobby = $stmt->fetch();

            if ($lobby === false) {
                throw new DomainException(
                    'Лобби не найдено.'
                );
            }

            // Только создатель может запускать голосование
            if ((int) $lobby['creator_id'] !== $userId) {
                throw new DomainException(
                    'Только создатель лобби может начать голосование.'
                );
            }

            // Запрещаем запуск нескольких раундов одновременно
            if ($lobby['status'] === 'voting') {
                throw new DomainException(
                    'Голосование уже запущено.'
                );
            }
            
            // Проверяем минимальное количество участников
            $stmt = $this->pdo->prepare(
                'SELECT COUNT(*)
     FROM lobby_members
     WHERE lobby_id = :lobby_id'
                );
            
            $stmt->execute([
                'lobby_id' => $lobbyId,
            ]);
            
            $playerCount = (int) $stmt->fetchColumn();
            
            if ($playerCount < 2) {
                throw new DomainException(
                    'Для начала голосования необходимо минимум два игрока.'
                    );
            }

            // Определяем номер следующего раунда
            $stmt = $this->pdo->prepare(
                'SELECT COALESCE(MAX(round_number), 0) + 1
                 FROM voting_rounds
                 WHERE lobby_id = :lobby_id'
            );

            $stmt->execute([
                'lobby_id' => $lobbyId,
            ]);

            $roundNumber = (int) $stmt->fetchColumn();

            // Создаём раунд с серверным временем окончания
            $stmt = $this->pdo->prepare(
                'INSERT INTO voting_rounds
                    (
                        lobby_id,
                        round_number,
                        status,
                        started_at,
                        ends_at
                    )
                 VALUES
                    (
                        :lobby_id,
                        :round_number,
                        \'active\',
                        NOW(),
                        DATE_ADD(
                            NOW(),
                            INTERVAL :duration SECOND
                        )
                    )'
            );

            $stmt->bindValue(
                ':lobby_id',
                $lobbyId,
                PDO::PARAM_INT
            );

            $stmt->bindValue(
                ':round_number',
                $roundNumber,
                PDO::PARAM_INT
            );

            $stmt->bindValue(
                ':duration',
                self::VOTING_DURATION,
                PDO::PARAM_INT
            );

            $stmt->execute();

            $roundId = (int) $this->pdo->lastInsertId();

            // Изменяем статус игрового лобби
            $stmt = $this->pdo->prepare(
                'UPDATE lobbies
                 SET status = \'voting\'
                 WHERE id = :id'
            );

            $stmt->execute([
                'id' => $lobbyId,
            ]);

            $this->pdo->commit();

            return $roundId;

        } catch (Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    // Получение последнего раунда голосования
    public function getLatest(int $lobbyId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT *,
                GREATEST(
                    TIMESTAMPDIFF(
                        SECOND,
                        NOW(),
                        ends_at
                    ),
                    0
                ) AS remaining_seconds
             FROM voting_rounds
             WHERE lobby_id = :lobby_id
             ORDER BY round_number DESC
             LIMIT 1'
        );

        $stmt->execute([
            'lobby_id' => $lobbyId,
        ]);

        $round = $stmt->fetch();

        return $round === false ? null : $round;
    }
    
    // Завершение голосования после истечения времени
    public function finish(int $lobbyId): void
    {
        $this->pdo->beginTransaction();
        
        try {
            // Блокируем лобби на время подсчёта
            $stmt = $this->pdo->prepare(
                'SELECT id FROM lobbies
             WHERE id = :id FOR UPDATE'
                );
            
            $stmt->execute(['id' => $lobbyId]);
            
            // Ищем раунд, время которого истекло
            $stmt = $this->pdo->prepare(
                "SELECT id FROM voting_rounds
             WHERE lobby_id = :lobby_id
               AND status = 'active'
               AND ends_at <= NOW()
             LIMIT 1"
                );
            
            $stmt->execute(['lobby_id' => $lobbyId]);
            
            $roundId = $stmt->fetchColumn();
            
            if ($roundId === false) {
                $this->pdo->commit();
                return;
            }
            
            $roundId = (int) $roundId;
            
            // Определяем победителей
            $vote = new Vote();
            
            $winningMap = $this->chooseWinner(
                $vote->getMapVotes($roundId)
                );
            
            $winningMode = $this->chooseWinner(
                $vote->getModeVotes($roundId)
                );
            
            // Сохраняем результаты раунда
            $stmt = $this->pdo->prepare(
                "UPDATE voting_rounds
             SET status = 'completed',
                 winning_map_id = :map_id,
                 winning_mode_id = :mode_id,
                 completed_at = NOW()
             WHERE id = :id"
                );
            
            $stmt->execute([
                'map_id' => $winningMap,
                'mode_id' => $winningMode,
                'id' => $roundId,
            ]);
            
            // Переводим лобби в состояние результатов
            $stmt = $this->pdo->prepare(
                "UPDATE lobbies
             SET status = 'results'
             WHERE id = :id"
                );
            
            $stmt->execute(['id' => $lobbyId]);
            
            $this->pdo->commit();
            
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            
            throw $e;
        }
    }
    
    // Выбор победителя по количеству голосов
    private function chooseWinner(array $options): int
    {
        if (empty($options)) {
            throw new DomainException(
                'Нет доступных вариантов голосования.'
                );
        }
        
        $maxVotes = max(array_column($options, 'votes'));
        
        $leaders = [];
        
        foreach ($options as $option) {
            if ((int) $option['votes'] === (int) $maxVotes) {
                $leaders[] = (int) $option['id'];
            }
        }
        
        return $leaders[array_rand($leaders)];
    }
    
    public function getHistory(int $lobbyId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT vr.round_number, vr.completed_at,
                m.name AS map_name,
                gm.name AS mode_name
         FROM voting_rounds vr
         JOIN maps m ON m.id = vr.winning_map_id
         JOIN game_modes gm ON gm.id = vr.winning_mode_id
         WHERE vr.lobby_id = :lobby_id
           AND vr.status = 'completed'
         ORDER BY vr.round_number DESC"
            );
        
        $stmt->execute(['lobby_id' => $lobbyId]);
        
        return $stmt->fetchAll();
    }
}