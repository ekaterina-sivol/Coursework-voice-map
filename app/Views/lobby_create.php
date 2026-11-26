<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание лобби — GameVote</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<header>
    <a href="/" class="logo">GameVote</a>

    <nav>
        <a href="/">Главная</a>
        <a href="/lobbies">Игровые лобби</a>
    </nav>
</header>

<main class="create-card">

    <h1>Создание игрового лобби</h1>

    <p>
        Создай своё лобби, пригласи игроков
        и выбери следующее приключение!
    </p>

    <?php if (!empty($errors)): ?>

        <div class="form-errors">
            <ul>
                <?php foreach ($errors as $error): ?>

                    <li>
                        <?= htmlspecialchars(
                            $error,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </li>

                <?php endforeach; ?>
            </ul>
        </div>

    <?php endif; ?>

    <form action="/lobby/create" method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(
                $_SESSION['csrf_token'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >

        <div class="form-group">

            <label for="name">
                Название лобби
            </label>

            <input
                type="text"
                id="name"
                name="name"
                minlength="3"
                maxlength="100"
                placeholder="Название вашего лобби"
                value="<?= htmlspecialchars(
                    $name,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                required
            >

        </div>

        <div class="form-group">

            <label for="max_players">
                Максимальное количество игроков
            </label>

            <select
                id="max_players"
                name="max_players"
                required
            >

                <?php for ($i = 2; $i <= 10; $i++): ?>

                    <option
                        value="<?= $i ?>"
                        <?= $maxPlayers === $i ? 'selected' : '' ?>
                    >
                        <?= $i ?>
                    </option>

                <?php endfor; ?>

            </select>

        </div>

        <button type="submit">
            Создать лобби
        </button>

        <a href="/lobbies">
            Отмена
        </a>

    </form>

</main>

</body>
</html>