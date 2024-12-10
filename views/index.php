<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Крестики-нолики</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kablammo&family=Montserrat+Alternates:wght@700&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <ul class="menu">
                <?php if (isset($_SESSION['user'])): ?>
                    <li><a href="/home">Главная</a></li>
                    <li><a href="/game">Игра</a></li>
                    <li><a href="/results">Профиль</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Крестики-нолики</h1>
        <img src="/assets/img/tictactoe.png" alt="Tic Tac Toe" class="tic-tac-toe-image">
        <?php if (isset($_SESSION['user'])): ?>
            <p>Вы вошли как: <?= htmlspecialchars($_SESSION['user']) ?></p>
            <div class="buttons">
                <a href="/game" class="btn">Играть</a>
                <a href="/logout" class="btn">Выйти</a>
            </div>
        <?php else: ?>
            <div class="buttons">
                <a href="/login" class="btn">Войти</a>
                <a href="/register" class="btn">Зарегистрироваться</a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
