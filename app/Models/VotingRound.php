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
}