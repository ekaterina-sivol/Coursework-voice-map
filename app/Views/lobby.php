<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игровое лобби — GameVote</title>
</head>
<body>

<header>
    <a href="/">GameVote</a>

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

        <div class="form-errors">
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

        <h2>Голосование №<?= (int) $round['round_number'] ?></h2>

        <p>
            Осталось времени:
            <?= (int) $round['remaining_seconds'] ?> сек.
        </p>

        <h3>Выберите карту</h3>

        <div class="map-list">

            <?php foreach ($maps as $map): ?>

                <article class="map-card">

                    <h4>
                        <?= htmlspecialchars($map['name'], ENT_QUOTES, 'UTF-8') ?>
                    </h4>

                    <p>
                        <?= htmlspecialchars($map['description'], ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <p>Голосов: <?= (int) $map['votes'] ?></p>

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

        <h3>Выберите игровой режим</h3>

        <div class="mode-list">

            <?php foreach ($modes as $mode): ?>

                <article class="mode-card">

                    <h4>
                        <?= htmlspecialchars($mode['name'], ENT_QUOTES, 'UTF-8') ?>
                    </h4>

                    <p>
                        <?= htmlspecialchars($mode['description'], ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <p>Голосов: <?= (int) $mode['votes'] ?></p>

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

    </section>

<?php endif; ?>

<?php if (
    $round !== null
    && $round['status'] === 'completed'
): ?>

    <section class="voting-results">

        <h2>
            Результаты голосования №<?= (int) $round['round_number'] ?>
        </h2>

        <h3>Победившая карта</h3>

        <?php foreach ($maps as $map): ?>

            <?php if ((int) $map['id'] === (int) $round['winning_map_id']): ?>

                <p>
                    <?= htmlspecialchars($map['name'], ENT_QUOTES, 'UTF-8') ?>
                </p>

                <p>Голосов: <?= (int) $map['votes'] ?></p>

            <?php endif; ?>

        <?php endforeach; ?>

        <h3>Победивший режим</h3>

        <?php foreach ($modes as $mode): ?>

            <?php if ((int) $mode['id'] === (int) $round['winning_mode_id']): ?>

                <p>
                    <?= htmlspecialchars($mode['name'], ENT_QUOTES, 'UTF-8') ?>
                </p>

                <p>Голосов: <?= (int) $mode['votes'] ?></p>

            <?php endif; ?>

        <?php endforeach; ?>

    </section>

<?php endif; ?>

</main>

</body>
</html>