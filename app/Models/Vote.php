<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use Throwable;
use DomainException;

class Vote
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    // Голосование за игровую карту
    public function voteForMap(
        int $lobbyId,
        int $roundId,
        int $userId,
        int $mapId
    ): void {
        $this->saveVote(
            $lobbyId,
            $roundId,
            $userId,
            $mapId,
            'map'
        );
    }

    // Голосование за игровой режим
    public function voteForMode(
        int $lobbyId,
        int $roundId,
        int $userId,
        int $modeId
    ): void {
        $this->saveVote(
            $lobbyId,
            $roundId,
            $userId,
            $modeId,
            'mode'
        );
    }

    // Сохранение или изменение голоса
    private function saveVote(
        int $lobbyId,
        int $roundId,
        int $userId,
        int $optionId,
        string $type
    ): void {

        $this->pdo->beginTransaction();

        try {

            // Блокируем запись лобби
            $stmt = $this->pdo->prepare(
                'SELECT status
                 FROM lobbies
                 WHERE id = :id
                 FOR UPDATE'
            );

            $stmt->execute([
                'id' => $lobbyId,
            ]);

            $lobby = $stmt->fetch();

            if (
                $lobby === false
                || $lobby['status'] !== 'voting'
            ) {
                throw new DomainException(
                    'Голосование сейчас недоступно.'
                );
            }

            // Проверяем участие пользователя в лобби
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

            if ($stmt->fetchColumn() === false) {
                throw new DomainException(
                    'Голосовать могут только участники лобби.'
                );
            }

            // Проверяем принадлежность и состояние раунда
            $stmt = $this->pdo->prepare(
                'SELECT id
                 FROM voting_rounds
                 WHERE id = :round_id
                   AND lobby_id = :lobby_id
                   AND status = \'active\'
                   AND ends_at > NOW()'
            );

            $stmt->execute([
                'round_id' => $roundId,
                'lobby_id' => $lobbyId,
            ]);

            if ($stmt->fetchColumn() === false) {
                throw new DomainException(
                    'Время голосования истекло или раунд недоступен.'
                );
            }

            // Определяем таблицы в зависимости от типа голоса
            if ($type === 'map') {

                $optionTable = 'maps';
                $voteTable = 'map_votes';
                $optionColumn = 'map_id';

            } elseif ($type === 'mode') {

                $optionTable = 'game_modes';
                $voteTable = 'mode_votes';
                $optionColumn = 'mode_id';

            } else {

                throw new DomainException(
                    'Неизвестный тип голосования.'
                );
            }

            // Проверяем существование выбранного варианта
            $stmt = $this->pdo->prepare(
                "SELECT id
                 FROM {$optionTable}
                 WHERE id = :id"
            );

            $stmt->execute([
                'id' => $optionId,
            ]);

            if ($stmt->fetchColumn() === false) {
                throw new DomainException(
                    'Выбранный вариант не существует.'
                );
            }

            // Добавляем голос или изменяем предыдущий
            // Повторно проверяем время непосредственно при записи
            $stmt = $this->pdo->prepare(
                "INSERT INTO {$voteTable}
                    (
                        round_id,
                        user_id,
                        {$optionColumn}
                    )
                 SELECT
                    id,
                    :user_id,
                    :option_id
                 FROM voting_rounds
                 WHERE id = :round_id
                   AND status = 'active'
                   AND ends_at > NOW()
                 ON DUPLICATE KEY UPDATE
                    {$optionColumn} = :new_option_id,
                    voted_at = NOW()"
            );

            $stmt->execute([
                'round_id' => $roundId,
                'user_id' => $userId,
                'option_id' => $optionId,
                'new_option_id' => $optionId,
            ]);

            // При отсутствии изменений проверяем,
            // не завершилось ли голосование
            if ($stmt->rowCount() === 0) {

                $stmt = $this->pdo->prepare(
                    'SELECT id
                     FROM voting_rounds
                     WHERE id = :round_id
                       AND status = \'active\'
                       AND ends_at > NOW()'
                );

                $stmt->execute([
                    'round_id' => $roundId,
                ]);

                if ($stmt->fetchColumn() === false) {
                    throw new DomainException(
                        'Время голосования истекло.'
                    );
                }
            }

            $this->pdo->commit();

        } catch (Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }
}