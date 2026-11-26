<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameVote — Главная</title>
    <link rel="stylesheet" href="/css/style.css">
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
                <a href="/lobbies" class="button">К игровым лобби</a>
            <?php else: ?>
                <a href="/register" class="button">Начать играть</a>
                <a href="/login" class="button-secondary">Войти</a>
            <?php endif; ?>
        </div>
        
        <img src="/images/gamepad1.png"
     alt="Игровой контроллер"
     class="hero-image">
     
    </section>

    <section class="how-it-works">
        <h2>Как это работает?</h2>

        <div class="steps">
            <article class="step-card">
                <span class="step-number">01</span>
                <h3>Создай лобби</h3>
                <p>Собери команду от 2 до 10 игроков.</p>
            </article>

            <article class="step-card">
                <span class="step-number">02</span>
                <h3>Сделай выбор</h3>
                <p>Проголосуй за любимую карту и игровой режим.</p>
            </article>

            <article class="step-card">
                <span class="step-number">03</span>
                <h3>Узнай результат</h3>
                <p>Через 60 секунд система определит победителей.</p>
            </article>
        </div>
    </section>
</main>

<footer>
    <p>GameVote &copy; 2026</p>
</footer>

</body>
</html>