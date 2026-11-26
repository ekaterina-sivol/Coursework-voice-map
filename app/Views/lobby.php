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

</main>

</body>
</html>