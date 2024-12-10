<?php
session_start();

$config = require __DIR__ . '/config/bootstrap.php';
$userController = $config['userController'];
$gameController = $config['gameController'];
$pdo = $config['pdo'];

$request = $_SERVER['REQUEST_URI'];
$route = parse_url($request, PHP_URL_PATH);

if (str_starts_with($route, '/api')) {
    require_once __DIR__ . '/config/api.php';  
    exit();
}

switch ($route) {
    case '/':
    case '/home':
        require_once __DIR__ . '/views/index.php';
        break;

    case '/login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $userController->login($username, $password);
        } else {
            require_once __DIR__ . '/views/login.php';
        }
        break;

    case '/register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $userController->register($username, $password);
        } else {
            require_once __DIR__ . '/views/register.php';
        }
        break;

    case '/game':
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }
        $game = $gameController->getActiveGame($_SESSION['user_id']);
        if (!$game) {
            $gameId = $gameController->startGame($_SESSION['user_id']);
            header("Location: /game?id=$gameId");
            exit();
        }
        $board = json_decode($game['board'], true);
        $status = $game['status'];
        require_once __DIR__ . '/views/game.php';
        break;

    case '/results':
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }
        $userId = $_SESSION['user_id'];
        $stats = $gameController->getUserStats($userId);
        require_once __DIR__ . '/views/results.php';
        break;

    case '/logout':
        session_unset();
        session_destroy();
        header('Location: /home');
        exit();

    default:
        http_response_code(404);
        echo "404 - Страница не найдена";
        break;
}
