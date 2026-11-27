<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игровые лобби — GameVote</title>
    <link rel="stylesheet" href="/css/style.css?v=<?= filemtime(__DIR__ . '/../../public/css/style.css') ?>">
</head>
<body>

<header>
    <a href="/" class="logo">GameVote</a>

    <nav>
        <a href="/">Главная</a>
        <a href="/lobbies">Игровые лобби</a>
    </nav>
</header>

<main>

    <section class="lobbies-banner">

        <div>
            <h1>Найди свою команду!</h1>

            <p>
                Присоединяйся к другим игрокам,
                выбирай любимые карты и режимы
                и отправляйся навстречу приключениям!
            </p>

            <a href="/lobby/create" class="button">
                + Создать лобби
            </a>
        </div>

        <img src="/images/gamepad1.png" alt="Игровой контроллер">

    </section>

    <?php
    $statuses = [
        'waiting' => 'Ожидание игроков',
        'voting' => 'Идёт голосование',
        'results' => 'Голосование завершено',
    ];
    ?>

    <div class="lobbies-heading">

        <div>
            <h2>Игровые лобби</h2>
            <p>Всего лобби: <?= count($lobbies) ?></p>
        </div>

        <a href="/lobbies" class="refresh-button">
            ↻ Обновить список
        </a>

    </div>

    <?php if (empty($lobbies)): ?>

        <div class="empty-lobbies">
            <h3>Пока нет игровых лобби</h3>
            <p>Создай первое лобби и пригласи других игроков!</p>
        </div>

    <?php else: ?>

        <section class="lobby-list">

            <?php foreach ($lobbies as $lobby): ?>

                <?php
                $isOwnLobby = (int) $lobby['creator_id'] === $userId;

                $fill = round(
                    $lobby['player_count'] /
                    max(1, $lobby['max_players']) * 100
                );
                ?>

                <article class="lobby-card <?= $isOwnLobby ? 'own-lobby' : '' ?>">

                    <div class="lobby-card-heading">

                        <div class="lobby-icon">🎮</div>

                        <div>
                            <h2>
                                <?= htmlspecialchars($lobby['name'], ENT_QUOTES, 'UTF-8') ?>
                            </h2>

                            <p class="lobby-creator">
                                Создатель:
                                <?= htmlspecialchars($lobby['creator_name'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>

                    </div>

                    <div class="lobby-status status-<?= htmlspecialchars($lobby['status'], ENT_QUOTES, 'UTF-8') ?>">

                        <span class="status-dot"></span>

                        <?= htmlspecialchars(
                            $statuses[$lobby['status']] ?? 'Неизвестный статус',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </div>

                    <div class="lobby-player-count">

                        <span>Игроки</span>

                        <strong>
                            <?= (int) $lobby['player_count'] ?>
                            /
                            <?= (int) $lobby['max_players'] ?>
                        </strong>

                    </div>

                    <div class="lobby-progress">
                        <div class="lobby-progress-fill"
                             style="width: <?= $fill ?>%">
                        </div>
                    </div>

                    <div class="lobby-owner">

                        <?php if ($isOwnLobby): ?>
                            Ваше лобби
                        <?php endif; ?>

                    </div>

                    <a href="/lobby?id=<?= (int) $lobby['id'] ?>" class="button">
                        Открыть лобби
                    </a>

                </article>

            <?php endforeach; ?>

        </section>

    <?php endif; ?>

</main>

</body>
</html>