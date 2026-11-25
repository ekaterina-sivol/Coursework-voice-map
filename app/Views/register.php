<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация — GameVote</title>
</head>
<body>

    <header>
        <a href="/">GameVote</a>
    </header>

    <main>
        <h1>Создание аккаунта</h1>

        <p>Присоединяйся к GameVote и выбирай своё следующее приключение!</p>

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

        <form action="/register" method="POST">

            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-group">
                <label for="username">Имя пользователя</label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
                    minlength="3"
                    maxlength="50"
                    autocomplete="username"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">Электронная почта</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                    maxlength="255"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password_confirm">
                    Подтверждение пароля
                </label>

                <input
                    type="password"
                    id="password_confirm"
                    name="password_confirm"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >
            </div>

            <button type="submit">Зарегистрироваться</button>

        </form>

        <p>
            Уже есть аккаунт?
            <a href="/login">Войти</a>
        </p>

        <a href="/">Вернуться на главную</a>
    </main>

</body>
</html>