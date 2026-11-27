<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игровое лобби — GameVote</title>
    <link rel="stylesheet" href="/css/style.css?v=<?= filemtime(__DIR__ . '/../../public/css/style.css') ?>">
</head>
<?php
$isVoting = $isMember
    && $lobby['status'] === 'voting'
    && $round !== null
    && $round['status'] === 'active';
?>

<body class="<?= $isVoting ? 'voting-page' : '' ?>">

<header>
    <a href="/" class="logo">GameVote</a>

    <nav>
        <a href="/">Главная</a>
        <a href="/lobbies">Игровые лобби</a>
    </nav>
</header>

<main>

    <?php
    $statuses = [
        'waiting' => 'Ожидание игроков',
        'voting' => 'Идёт голосование',
        'results' => 'Голосование завершено',
    ];
    ?>

    <section class="lobby-info">

        <h1>
            <?= htmlspecialchars(
                $lobby['name'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </h1>

        <p>
            Создатель:
            <?= htmlspecialchars(
                $lobby['creator_name'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            Статус:
            <?= htmlspecialchars(
                $statuses[$lobby['status']]
                    ?? 'Неизвестный статус',
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            Игроки:
            <?= (int) $lobby['player_count'] ?>
            /
            <?= (int) $lobby['max_players'] ?>
        </p>

    </section>

    <?php if (!empty($error)): ?>

        <div class="error">
            <?= htmlspecialchars(
                $error,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </div>

    <?php endif; ?>

    <section class="lobby-members">

        <h2>Участники лобби</h2>

        <ul>

            <?php foreach ($members as $member): ?>

                <li>

                    <?= htmlspecialchars(
                        $member['username'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                    <?php if (
                        (int) $member['id']
                        === (int) $lobby['creator_id']
                    ): ?>

                        <span> — Создатель</span>

                    <?php endif; ?>

                    <?php if ((int) $member['id'] === $userId): ?>

                        <span> — Вы</span>

                    <?php endif; ?>

                </li>

            <?php endforeach; ?>

        </ul>

    </section>

    <section class="lobby-actions">

        <?php if (!$isMember): ?>

            <?php if ($lobby['status'] === 'voting'): ?>

                <p>
                    Сейчас идёт голосование.
                    Присоединение временно недоступно.
                </p>

            <?php elseif (
                (int) $lobby['player_count']
                >= (int) $lobby['max_players']
            ): ?>

                <p>В лобби нет свободных мест.</p>

            <?php else: ?>

                <form action="/lobby/join" method="POST">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(
                            $_SESSION['csrf_token'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >

                    <input
                        type="hidden"
                        name="lobby_id"
                        value="<?= (int) $lobby['id'] ?>"
                    >

                    <button type="submit">
                        Присоединиться
                    </button>

                </form>

            <?php endif; ?>

        <?php else: ?>

            <p>Вы являетесь участником лобби.</p>

            <?php if ($isCreator): ?>

                <p>Вы создатель этого лобби.</p>
                
                <?php if ($lobby['status'] !== 'voting'): ?>

    <?php if ((int) $lobby['player_count'] >= 2): ?>

        <form action="/lobby/start" method="POST">

            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

            <input type="hidden" name="lobby_id"
                   value="<?= (int) $lobby['id'] ?>">

            <button type="submit">
                <?= $lobby['status'] === 'results'
                    ? 'Начать новое голосование'
                    : 'Начать голосование' ?>
            </button>

        </form>

    <?php else: ?>

        <p>Для начала голосования необходимо минимум два игрока.</p>

    <?php endif; ?>

<?php else: ?>

    <p>Голосование уже началось.</p>

<?php endif; ?>

            <?php else: ?>

                <?php if ($lobby['status'] === 'waiting'): ?>

                    <p>Ожидайте начала голосования.</p>

                <?php endif; ?>

            <?php endif; ?>

        <?php endif; ?>

    </section>

    <a href="/lobbies">
        Вернуться к списку лобби
    </a>
    
    <?php if (
    $isMember
    && $lobby['status'] === 'voting'
    && $round !== null
    && $round['status'] === 'active'
): ?>

    <section class="voting">

    <div class="voting-heading">
        <h1>Выбери своё приключение!</h1>
        <p>Выберите карту и игровой режим для следующей игры.</p>
    </div>

    <div class="voting-toolbar">

        <h2>Голосование №<?= (int) $round['round_number'] ?></h2>

        <p>
    Осталось времени:
    <span id="voting-timer"
          data-seconds="<?= (int) $round['remaining_seconds'] ?>">
        <?= (int) $round['remaining_seconds'] ?>
    </span> сек.
</p>

            <div class="voting-tabs">
        <button type="button" id="maps-tab"
                onclick="showVotingTab('maps')">
            Игровые карты
        </button>

        <button type="button" id="modes-tab"
                onclick="showVotingTab('modes')">
            Игровые режимы
        </button>
    </div>

    <p class="voting-selection">
        Карта: <?= $userMap !== null ? 'выбрана' : 'не выбрана' ?>
        ·
        Режим: <?= $userMode !== null ? 'выбран' : 'не выбран' ?>
    </p>

    </div>

    <div id="maps-panel">
        <h3>Выберите карту</h3>

        <div class="map-list">

            <?php foreach ($maps as $map): ?>

                <article class="map-card">

                    <?php if (!empty($map['image_path'])): ?>
                        <img
                            src="<?= htmlspecialchars($map['image_path'], ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($map['name'], ENT_QUOTES, 'UTF-8') ?>"
                            class="map-image"
                        >
                    <?php endif; ?>

                    <h4>
                        <?= htmlspecialchars($map['name'], ENT_QUOTES, 'UTF-8') ?>
                    </h4>

                    <p>
                        <?= htmlspecialchars($map['description'], ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <p>Голосов: <span id="map-votes-<?= (int) $map['id'] ?>"><?= (int) $map['votes'] ?></span></p>

                    <div class="vote-progress">
                        <div class="vote-fill"
                             id="map-progress-<?= (int) $map['id'] ?>"
                             style="width: <?= round($map['votes'] / max(1, $lobby['player_count']) * 100) ?>%">
                        </div>
                    </div>

                    <form action="/lobby/vote" method="POST">

                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                        <input type="hidden" name="lobby_id"
                               value="<?= (int) $lobby['id'] ?>">

                        <input type="hidden" name="round_id"
                               value="<?= (int) $round['id'] ?>">

                        <input type="hidden" name="type" value="map">

                        <input type="hidden" name="option_id"
                               value="<?= (int) $map['id'] ?>">

                        <button type="submit"
                            <?= $userMap === (int) $map['id'] ? 'disabled' : '' ?>>

                            <?= $userMap === (int) $map['id']
                                ? 'Ваш выбор'
                                : 'Голосовать' ?>

                        </button>

                    </form>

                </article>

            <?php endforeach; ?>

        </div>

            </div>

    <div id="modes-panel" hidden>
        <h3>Выберите игровой режим</h3>

        <div class="mode-list">

            <?php foreach ($modes as $mode): ?>

                <article class="mode-card">

                    <?php if (!empty($mode['image_path'])): ?>
                        <img
                            src="<?= htmlspecialchars($mode['image_path'], ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($mode['name'], ENT_QUOTES, 'UTF-8') ?>"
                            class="map-image"
                        >
                    <?php endif; ?>

                    <h4>
                        <?= htmlspecialchars($mode['name'], ENT_QUOTES, 'UTF-8') ?>
                    </h4>

                    <p>
                        <?= htmlspecialchars($mode['description'], ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <p>Голосов: <span id="mode-votes-<?= (int) $mode['id'] ?>"><?= (int) $mode['votes'] ?></span></p>

                    <div class="vote-progress">
                        <div class="vote-fill"
                             id="mode-progress-<?= (int) $mode['id'] ?>"
                             style="width: <?= round($mode['votes'] / max(1, $lobby['player_count']) * 100) ?>%">
                        </div>
                    </div>

                    <form action="/lobby/vote" method="POST">

                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                        <input type="hidden" name="lobby_id"
                               value="<?= (int) $lobby['id'] ?>">

                        <input type="hidden" name="round_id"
                               value="<?= (int) $round['id'] ?>">

                        <input type="hidden" name="type" value="mode">

                        <input type="hidden" name="option_id"
                               value="<?= (int) $mode['id'] ?>">

                        <button type="submit"
                            <?= $userMode === (int) $mode['id'] ? 'disabled' : '' ?>>

                            <?= $userMode === (int) $mode['id']
                                ? 'Ваш выбор'
                                : 'Голосовать' ?>

                        </button>

                    </form>

                </article>

            <?php endforeach; ?>

        </div>

    </div>

    </section>

<?php endif; ?>

<?php if (
    $round !== null
    && $round['status'] === 'completed'
): ?>

    <section class="voting-results" id="voting-results">

    <h2>Голосование завершено!</h2>

    <p class="result-subtitle">
        Результаты голосования №<?= (int) $round['round_number'] ?>
    </p>

    <div class="result-list">

        <article class="result-card">

            <h3>Победившая карта</h3>

            <?php foreach ($maps as $map): ?>

                <?php if ((int) $map['id'] === (int) $round['winning_map_id']): ?>

                    <?php if (!empty($map['image_path'])): ?>
                        <img
                            src="<?= htmlspecialchars($map['image_path'], ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($map['name'], ENT_QUOTES, 'UTF-8') ?>"
                            class="result-image"
                        >
                    <?php endif; ?>

                    <h4>
                        <?= htmlspecialchars($map['name'], ENT_QUOTES, 'UTF-8') ?>
                    </h4>

                    <p>
                        <?= htmlspecialchars($map['description'], ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <p class="result-votes">
                        Голосов:
                        <span id="map-votes-<?= (int) $map['id'] ?>">
                            <?= (int) $map['votes'] ?>
                        </span>
                    </p>

                <?php endif; ?>

            <?php endforeach; ?>

        </article>

        <article class="result-card">

            <h3>Победивший режим</h3>

            

            <?php foreach ($modes as $mode): ?>

                <?php if ((int) $mode['id'] === (int) $round['winning_mode_id']): ?>

                    <?php if (!empty($mode['image_path'])): ?>
                        <img
                            src="<?= htmlspecialchars($mode['image_path'], ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($mode['name'], ENT_QUOTES, 'UTF-8') ?>"
                            class="result-image"
                        >
                    <?php endif; ?>

                    <h4>
                        <?= htmlspecialchars($mode['name'], ENT_QUOTES, 'UTF-8') ?>
                    </h4>

                    <p>
                        <?= htmlspecialchars($mode['description'], ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <p class="result-votes">
                        Голосов:
                        <span id="mode-votes-<?= (int) $mode['id'] ?>">
                            <?= (int) $mode['votes'] ?>
                        </span>
                    </p>

                <?php endif; ?>

            <?php endforeach; ?>

        </article>

       </div>

    <button type="button"
            id="close-results"
            class="close-results"
            hidden>
        Вернуться в лобби
    </button>

</section>

<?php endif; ?>

<section class="voting-history">

    <h2>История голосований</h2>

    <?php if (empty($history)): ?>

        <p>Завершённых голосований пока нет.</p>

    <?php else: ?>

        <table>
            <thead>
                <tr>
                    <th>Раунд</th>
                    <th>Карта</th>
                    <th>Режим</th>
                    <th>Дата завершения</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($history as $item): ?>
                    <tr>
                        <td><?= (int) $item['round_number'] ?></td>

                        <td>
                            <?= htmlspecialchars($item['map_name']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($item['mode_name']) ?>
                        </td>

                       <td>
    <time class="local-time"
          datetime="<?= htmlspecialchars(
              str_replace(' ', 'T', $item['completed_at']) . 'Z',
              ENT_QUOTES,
              'UTF-8'
          ) ?>">
        <?= htmlspecialchars($item['completed_at']) ?>
    </time>
</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

</section>

</main>

<script>
    const timer = document.getElementById('voting-timer');

    if (timer) {
        const deadline = Date.now() +
            Number(timer.dataset.seconds) * 1000;

        function updateTimer() {
            const seconds = Math.max(
                0,
                Math.ceil((deadline - Date.now()) / 1000)
            );

            timer.textContent = seconds;

            if (seconds === 0) {
                clearInterval(interval);

                setTimeout(function () {
                    window.location.reload();
                }, 1000);
            }
        }

        const interval = setInterval(updateTimer, 1000);

        updateTimer();
    }
    
    const lobbyId = <?= (int) $lobby['id'] ?>;
const lobbyStatus = <?= json_encode($lobby['status']) ?>;
const roundId = <?= (int) ($round['id'] ?? 0) ?>;
const playerCount = <?= (int) $lobby['player_count'] ?>;

async function updateVoting() {
    try {
        const response = await fetch(
            '/lobby/status?id=' + lobbyId,
            { cache: 'no-store' }
        );

        if (!response.ok) {
            return;
        }

        const data = await response.json();

        if (
            data.status !== lobbyStatus ||
            data.round_id !== roundId ||
            data.player_count !== playerCount
        ) {
            window.location.reload();
            return;
        }

                // Обновляем голоса за карты
        data.maps.forEach(function (map) {
            const element = document.getElementById(
                'map-votes-' + map.id
            );

            if (element) {
                element.textContent = map.votes;
            }

            const progress = document.getElementById(
                'map-progress-' + map.id
            );

            if (progress) {
                progress.style.width =
                    (map.votes / Math.max(1, playerCount) * 100) + '%';
            }
        });

        // Обновляем голоса за игровые режимы
        data.modes.forEach(function (mode) {
            const element = document.getElementById(
                'mode-votes-' + mode.id
            );

            if (element) {
                element.textContent = mode.votes;
            }

            const progress = document.getElementById(
                'mode-progress-' + mode.id
            );

            if (progress) {
                progress.style.width =
                    (mode.votes / Math.max(1, playerCount) * 100) + '%';
            }
        });
    } catch (error) {
        console.error('Ошибка обновления голосования', error);
    }
}

setInterval(updateVoting, 2000);

// Сохраняем положение страницы при голосовании
const scrollKey = 'voting-scroll-' + lobbyId;

document.querySelectorAll('form[action="/lobby/vote"]').forEach(function (form) {
    form.addEventListener('submit', function () {
        sessionStorage.setItem(scrollKey, window.scrollY);
    });
});

// Восстанавливаем положение после перезагрузки
const savedScroll = sessionStorage.getItem(scrollKey);

if (savedScroll !== null) {
    sessionStorage.removeItem(scrollKey);

    window.addEventListener('load', function () {
        window.scrollTo(0, Number(savedScroll));
    });
}
    
// Переключение карт и игровых режимов
const tabKey = 'voting-tab-' + lobbyId + '-' + roundId;

function showVotingTab(tab) {
    const mapsPanel = document.getElementById('maps-panel');
    const modesPanel = document.getElementById('modes-panel');

    if (!mapsPanel || !modesPanel) {
        return;
    }

    mapsPanel.hidden = tab !== 'maps';
    modesPanel.hidden = tab !== 'modes';

    document.getElementById('maps-tab')
        .classList.toggle('active', tab === 'maps');

    document.getElementById('modes-tab')
        .classList.toggle('active', tab === 'modes');

    sessionStorage.setItem(tabKey, tab);
}

if (timer) {
    const savedTab = sessionStorage.getItem(tabKey);

    showVotingTab(savedTab === 'modes' ? 'modes' : 'maps');
}

// Отображаем время в часовом поясе пользователя
document.querySelectorAll('.local-time').forEach(function (element) {
    const date = new Date(element.dateTime);

    element.textContent = date.toLocaleString('ru-RU');
});

// Полноэкранное отображение результатов голосования
const results = document.getElementById('voting-results');
const closeResults = document.getElementById('close-results');

const resultKey = 'voting-result-' + lobbyId + '-' + roundId
    + '-<?= (int) $userId ?>';

if (results && closeResults && <?= $isMember ? 'true' : 'false' ?>) {

    if (sessionStorage.getItem(resultKey) !== 'closed') {
        results.classList.add('results-fullscreen');
        document.body.classList.add('results-open');
        closeResults.hidden = false;
    }

    function closeResultScreen() {
        results.classList.remove('results-fullscreen');
        document.body.classList.remove('results-open');

        closeResults.hidden = true;

        sessionStorage.setItem(resultKey, 'closed');
    }

    closeResults.addEventListener('click', closeResultScreen);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' &&
            results.classList.contains('results-fullscreen')) {
            closeResultScreen();
        }
    });
}

</script>

</body>
</html>