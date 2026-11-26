<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\VotingRound;
use App\Models\Vote;
use App\Models\Lobby;
use DomainException;
use PDOException;

class LobbyController extends Controller
{
    // Проверка авторизации пользователя
    private function requireAuth(): int
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        return (int) $_SESSION['user_id'];
    }

    // Проверка защитного CSRF-токена
    private function checkCsrf(): bool
    {
        $token = $_POST['csrf_token'] ?? '';

        return is_string($token)
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    // Отображение списка игровых лобби
    public function index(): void
    {
        $userId = $this->requireAuth();

        $lobbyModel = new Lobby();

        $this->view('lobbies', [
            'lobbies' => $lobbyModel->getAll(),
            'userId' => $userId,
        ]);
    }

    // Отображение формы создания лобби
    public function showCreate(): void
    {
        $this->requireAuth();

        $this->view('lobby_create', [
            'errors' => [],
            'name' => '',
            'maxPlayers' => 10,
        ]);
    }

    // Обработка создания нового лобби
    public function create(): void
    {
        $userId = $this->requireAuth();

        if (!$this->checkCsrf()) {
            http_response_code(403);
            echo 'Недействительный запрос.';
            return;
        }

        if (
            !isset($_POST['name'], $_POST['max_players'])
            || !is_string($_POST['name'])
            || !is_string($_POST['max_players'])
        ) {
            http_response_code(400);
            echo 'Некорректные данные запроса.';
            return;
        }

        $name = trim($_POST['name']);

        $maxPlayers = filter_var(
            $_POST['max_players'],
            FILTER_VALIDATE_INT
        );

        $errors = [];

        // Проверяем название лобби
        if (!preg_match('/^.{3,100}$/us', $name)) {
            $errors[] = 'Название должно содержать от 3 до 100 символов.';
        }

        // Проверяем вместимость лобби
        if (
            $maxPlayers === false
            || $maxPlayers < 2
            || $maxPlayers > 10
        ) {
            $errors[] = 'Количество игроков должно быть от 2 до 10.';
        }

        // Создаём лобби при отсутствии ошибок
        if (empty($errors)) {
            try {
                $lobbyModel = new Lobby();

                $lobbyId = $lobbyModel->create(
                    $name,
                    $userId,
                    $maxPlayers
                );

                $this->redirect('/lobby?id=' . $lobbyId);

            } catch (PDOException $e) {
                error_log($e->getMessage());

                $errors[] = 'Не удалось создать лобби. Попробуйте позже.';
            }
        }

        $this->view('lobby_create', [
            'errors' => $errors,
            'name' => $name,
            'maxPlayers' => $maxPlayers === false
                ? 10
                : $maxPlayers,
        ]);
    }

    // Отображение конкретного лобби
    public function show(): void
    {
        $userId = $this->requireAuth();

        $lobbyId = filter_var(
            $_GET['id'] ?? '',
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($lobbyId === false) {
            http_response_code(400);
            echo 'Некорректный идентификатор лобби.';
            return;
        }

        $roundModel = new VotingRound();
        $roundModel->finish($lobbyId);
        
        $lobbyModel = new Lobby();
        $lobby = $lobbyModel->findById($lobbyId);

        if ($lobby === null) {
            http_response_code(404);
            echo 'Лобби не найдено.';
            return;
        }

        $error = $_SESSION['lobby_error'] ?? '';
        
        $round = $roundModel->getLatest($lobbyId);
        
        $maps = [];
        $modes = [];
        $userMap = null;
        $userMode = null;
        
        if ($round !== null) {
            $voteModel = new Vote();
            
            $roundId = (int) $round['id'];
            
            $maps = $voteModel->getMapVotes($roundId);
            $modes = $voteModel->getModeVotes($roundId);
            
            $userMap = $voteModel->getUserMap($roundId, $userId);
            $userMode = $voteModel->getUserMode($roundId, $userId);
        }

        unset($_SESSION['lobby_error']);

        $this->view('lobby', [
            'lobby' => $lobby,
            'members' => $lobbyModel->getMembers($lobbyId),
            'isMember' => $lobbyModel->isMember(
                $lobbyId,
                $userId
            ),
            'isCreator' => (int) $lobby['creator_id'] === $userId,
            'userId' => $userId,
            'error' => $error,
            'round' => $round,
            'maps' => $maps,
            'modes' => $modes,
            'userMap' => $userMap,
            'userMode' => $userMode,
        ]);
    }

    // Присоединение пользователя к лобби
    public function join(): void
    {
        $userId = $this->requireAuth();

        if (!$this->checkCsrf()) {
            http_response_code(403);
            echo 'Недействительный запрос.';
            return;
        }

        $lobbyId = filter_var(
            $_POST['lobby_id'] ?? '',
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($lobbyId === false) {
            http_response_code(400);
            echo 'Некорректный идентификатор лобби.';
            return;
        }

        try {
            $roundModel = new VotingRound();
            $roundModel->finish($lobbyId);
            
            $lobbyModel = new Lobby();
            $lobbyModel->join($lobbyId, $userId);

        } catch (DomainException $e) {
            $_SESSION['lobby_error'] = $e->getMessage();

        } catch (PDOException $e) {
            error_log($e->getMessage());

            $_SESSION['lobby_error'] =
                'Не удалось присоединиться к лобби.';
        }

        $this->redirect('/lobby?id=' . $lobbyId);
    }
    
    // Запуск голосования
    public function startVoting(): void
    {
        $userId = $this->requireAuth();
        
        if (!$this->checkCsrf()) {
            http_response_code(403);
            echo 'Недействительный запрос.';
            return;
        }
        
        $lobbyId = filter_var(
            $_POST['lobby_id'] ?? '',
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
            );
        
        if ($lobbyId === false) {
            http_response_code(400);
            echo 'Некорректный идентификатор лобби.';
            return;
        }
        
        try {
            $round = new VotingRound();
            
            $round->finish($lobbyId);
            $round->start($lobbyId, $userId);
            
        } catch (DomainException $e) {
            $_SESSION['lobby_error'] = $e->getMessage();
            
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $_SESSION['lobby_error'] =
            'Не удалось запустить голосование.';
        }
        
        $this->redirect('/lobby?id=' . $lobbyId);
    }
    
    // Обработка голоса пользователя
    public function vote(): void
    {
        $userId = $this->requireAuth();
        
        if (!$this->checkCsrf()) {
            http_response_code(403);
            echo 'Недействительный запрос.';
            return;
        }
        
        $options = ['options' => ['min_range' => 1]];
        
        $lobbyId = filter_var(
            $_POST['lobby_id'] ?? '',
            FILTER_VALIDATE_INT,
            $options
            );
        
        $roundId = filter_var(
            $_POST['round_id'] ?? '',
            FILTER_VALIDATE_INT,
            $options
            );
        
        $optionId = filter_var(
            $_POST['option_id'] ?? '',
            FILTER_VALIDATE_INT,
            $options
            );
        
        $type = $_POST['type'] ?? '';
        
        if (
            $lobbyId === false
            || $roundId === false
            || $optionId === false
            || !in_array($type, ['map', 'mode'], true)
            ) {
                http_response_code(400);
                echo 'Некорректные данные голосования.';
                return;
            }
            
            try {
                $round = new VotingRound();
                $round->finish($lobbyId);
                
                $vote = new Vote();
                
                if ($type === 'map') {
                    $vote->voteForMap(
                        $lobbyId, $roundId, $userId, $optionId
                        );
                } else {
                    $vote->voteForMode(
                        $lobbyId, $roundId, $userId, $optionId
                        );
                }
                
            } catch (DomainException $e) {
                $_SESSION['lobby_error'] = $e->getMessage();
                
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $_SESSION['lobby_error'] =
                'Не удалось сохранить голос.';
            }
            
            $this->redirect('/lobby?id=' . $lobbyId);
    }
}