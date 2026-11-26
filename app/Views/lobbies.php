<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игровые лобби — GameVote</title>
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

    <h1>Игровые лобби</h1>

    <p>
        Присоединяйся к другим игрокам или создай
        собственное лобби для голосования!
    </p>

    <a href="/lobby/create">Создать лобби</a>

    <?php
    $statuses = [
        'waiting' => 'Ожидание игроков',
        'voting' => 'Идёт голосование',
        'results' => 'Голосование завершено',
    ];
    ?>

    <?php if (empty($lobbies)): ?>

        <p>Пока нет игровых лобби. Создай первое!</p>

    <?php else: ?>

        <section class="lobby-list">

            <?php foreach ($lobbies as $lobby): ?>

                <article class="lobby-card">

                    <h2>
                        <?= htmlspecialchars(
                            $lobby['name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </h2>

                    <p>
                        Создатель:
                        <?= htmlspecialchars(
                            $lobby['creator_name'],
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

                    <p>
                        Статус:
                        <?= htmlspecialchars(
                            $statuses[$lobby['status']]
                                ?? 'Неизвестный статус',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>

                    <?php if ((int) $lobby['creator_id'] === $userId): ?>

                        <p>Ваше лобби</p>

                    <?php endif; ?>

                    <a href="/lobby?id=<?= (int) $lobby['id'] ?>">
                        Открыть лобби
                    </a>

                </article>

            <?php endforeach; ?>

        </section>

    <?php endif; ?>

</main>

</body>
</html>