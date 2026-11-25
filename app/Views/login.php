<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — GameVote</title>
</head>
<body>

    <header>
        <a href="/">GameVote</a>
    </header>

    <main>
        <h1>Вход в аккаунт</h1>

        <p>Войди в GameVote и присоединяйся к приключениям!</p>

        <?php if ($registered): ?>
            <div class="success" role="status">
                Регистрация прошла успешно! Теперь войдите в аккаунт.
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="errors" role="alert">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li>
                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST">

            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-group">
                <label for="email">Электронная почта</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                    maxlength="255"
                    autocomplete="username"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit">Войти</button>

        </form>

        <p>
            Ещё нет аккаунта?
            <a href="/register">Зарегистрироваться</a>
        </p>

        <a href="/">Вернуться на главную</a>
    </main>

</body>
</html>