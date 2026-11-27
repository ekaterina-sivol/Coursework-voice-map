<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameVote — Главная</title>
    <link rel="stylesheet" href="/css/style.css?v=<?= filemtime(__DIR__ . '/../../public/css/style.css') ?>">
</head>
<body>

<header>
    <a href="/" class="logo">GameVote</a>

    <nav>
        <a href="/">Главная</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/lobbies">Игровые лобби</a>

            <span>
                <?= htmlspecialchars($_SESSION['username']) ?>
            </span>

            <form action="/logout" method="post">
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <button type="submit">Выйти</button>
            </form>
        <?php else: ?>
            <a href="/login">Войти</a>
            <a href="/register" class="button">Регистрация</a>
        <?php endif; ?>
    </nav>
</header>

<main>

    <section class="hero">

        <div class="hero-content">

            <span class="hero-label">GameVote</span>

            <h1>Голосование за карту<br>и игровой режим</h1>

            <p>
                Создавай лобби, приглашай друзей
                и вместе выбирайте карту и режим для игры.
            </p>

            <?php if (isset($_SESSION['user_id'])): ?>

                <a href="/lobbies" class="button">
                    К игровым лобби
                </a>

            <?php else: ?>

                <a href="/register" class="button">
                    Начать голосование
                </a>

                <a href="/login" class="button-secondary">
                    Войти
                </a>

            <?php endif; ?>

        </div>

        <img src="/images/gamepad1.png"
             alt="Игровой контроллер"
             class="hero-image">

    </section>

    <section class="home-features">

        <article>
            <strong><?= count($maps) ?></strong>
            <span>Игровых карт</span>
        </article>

        <article>
            <strong><?= count($modes) ?></strong>
            <span>Игровых режимов</span>
        </article>

        <article>
            <strong>2–10</strong>
            <span>Игроков в лобби</span>
        </article>

        <article>
            <strong>60 сек.</strong>
            <span>На голосование</span>
        </article>

    </section>

    

    <section class="home-gallery">

        <div class="gallery-heading">
            <h2>Выбери свой мир</h2>
            <p>
                От волшебных лесов до далёких планет —
                познакомься со всеми игровыми картами.
            </p>
        </div>

        <div class="gallery-grid">

            <?php foreach ($maps as $map): ?>

                <article class="gallery-card">

                    <img
                        src="<?= htmlspecialchars($map['image_path'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        alt="<?= htmlspecialchars($map['name'], ENT_QUOTES, 'UTF-8') ?>"
                        loading="lazy"
                    >

                    <div class="gallery-card-content">

                        <h3>
                            <?= htmlspecialchars($map['name'], ENT_QUOTES, 'UTF-8') ?>
                        </h3>

                        <p>
                            <?= htmlspecialchars($map['description'], ENT_QUOTES, 'UTF-8') ?>
                        </p>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    </section>

    <section class="home-gallery">

        <div class="gallery-heading">
            <h2>Найди любимый режим</h2>
            <p>
                Соревнуйся, исследуй, решай загадки
                и объединяйся с другими игроками.
            </p>
        </div>

        <div class="gallery-grid">

            <?php foreach ($modes as $mode): ?>

                <article class="gallery-card">

                    <img
                        src="<?= htmlspecialchars($mode['image_path'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        alt="<?= htmlspecialchars($mode['name'], ENT_QUOTES, 'UTF-8') ?>"
                        loading="lazy"
                    >

                    <div class="gallery-card-content">

                        <h3>
                            <?= htmlspecialchars($mode['name'], ENT_QUOTES, 'UTF-8') ?>
                        </h3>

                        <p>
                            <?= htmlspecialchars($mode['description'], ENT_QUOTES, 'UTF-8') ?>
                        </p>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    </section>

    <section class="home-final">

        <h2>Готов выбрать следующее приключение?</h2>

        <p>
            Собирай друзей и решайте вместе,
            какой будет ваша следующая игра!
        </p>

        <?php if (isset($_SESSION['user_id'])): ?>

            <a href="/lobbies" class="button">
                Перейти к голосованию
            </a>

        <?php else: ?>

            <a href="/register" class="button">
                Создать аккаунт
            </a>

        <?php endif; ?>

    </section>

</main>

<footer>
    <a href="/" class="logo">GameVote</a>
    <p>Система голосования за карту и игровой режим</p>
    <p>&copy; 2026 GameVote</p>
</footer>

</body>
</html>