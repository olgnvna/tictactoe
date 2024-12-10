<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kablammo&family=Montserrat+Alternates:wght@700&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="/assets/css/auth_style.css">
    <title>Вход</title>
</head>
<body>
    <header>
        <a href="/home" class="logo">Крестики-Нолики</a>
    </header>
    <h1>Вход</h1>
    <main>
        <form action="/login" method="POST" class="auth-form">
            <label for="username">Имя пользователя</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit" class="btn">Войти</button>
        </form>
        <p>Нет аккаунта? <a href="/register">Зарегистрируйтесь</a></p>
    </main>
</body>
</html>
