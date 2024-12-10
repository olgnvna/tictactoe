<?php
header('Content-Type: application/json');

$config = require __DIR__ . '/bootstrap.php';
$userController = $config['userController'];
$gameController = $config['gameController'];

switch ($route) {
    case '/api/login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['username'], $data['password'])) {
                $username = $data['username'];
                $password = $data['password'];
                $result = $userController->login($username, $password, true);
                echo json_encode($result);
                exit();
            }
        }
        http_response_code(400);
        echo json_encode(['error' => 'Неизвестный запрос!']);
        exit();

    case '/api/register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['username'], $data['password'])) {
                $result = $userController->register($data['username'], $data['password'], true);
                echo json_encode($result);
                exit();
            }
        }
        http_response_code(400);
        echo json_encode(['error' => 'Неизвестный запрос!']);
        exit();

    case '/api/game/start':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            $response = $gameController->apiStartNewGame($_SESSION['user_id']);
            echo json_encode($response);
            exit();
        }
        http_response_code(400);
        echo json_encode(['error' => 'Неизвестный запрос!']);
        exit();

    case '/api/game/play':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $gameController->apiPlayTurn($data, $_SESSION['user_id']);
            echo json_encode($response);
            exit();
        }
        http_response_code(400);
        echo json_encode(['error' => 'Неизвестный запрос!']);
        exit();

    case '/api/user/stats':
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $stats = $gameController->getUserStats($userId);

            if ($stats) {
                echo json_encode($stats);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Статистика не найдена!']);
            }
            exit();
        }
        http_response_code(400);
        echo json_encode(['error' => 'Неизвестный запрос!']);
        exit();
    
    case '/api/user/update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);
    
            if (isset($data['username'], $data['password'])) {
                $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
                $userController->updateUserInfo($userId, $data['username'], $hashedPassword);

                echo json_encode(['message' => 'Информация пользователя успешно обновлена!']);
                session_unset(); 
                session_destroy();
                exit();
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Некорректные данные!']);
                exit();
            }
        }
        http_response_code(400);
        echo json_encode(['error' => 'Некорректный запрос!']);
        exit();
    
    case '/api/user/delete':
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $userController->deleteUserAccount($userId);
            
            session_unset();
            session_destroy();
    
            echo json_encode(['message' => 'Учетная запись удалена!']);
            exit();
        }
        http_response_code(400);
        echo json_encode(['error' => 'Некорректный запрос!']);
        exit();

    case '/api/user/clear-games':
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $gameController->clearUserGames($userId);
            echo json_encode(['message' => 'Все игры пользователя успешно очищены!']);
            exit();
        }
        http_response_code(400);
        echo json_encode(['error' => 'Некорректный запрос!']);
        exit();            

    default:
        http_response_code(404);
        echo json_encode(['error' => 'API endpoint not found']);
        exit();
}
