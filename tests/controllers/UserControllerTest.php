<?php
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase
{
    private $pdo;
    private $userController;
    private $userModel;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            password TEXT NOT NULL
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS games (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            board TEXT,
            status TEXT,
            current_player TEXT
        )");

        require_once __DIR__ . '/../../models/UserModel.php';
        require_once __DIR__ . '/../../controllers/UserController.php';
        $this->userModel = new UserModel($this->pdo);
        $this->userController = new UserController($this->pdo);
    }

    public function testRegisterSuccess()
    {
        $username = 'testuser';
        $password = 'password123';

        $response = $this->userController->register($username, $password, true);

        $this->assertTrue($response['success']);
        $this->assertEquals('Регистрация прошла успешно!', $response['message']);

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($user);
        $this->assertEquals($username, $user['username']);
    }

    public function testRegisterUserAlreadyExists()
    {
        $this->userController->register('testuser', 'password123', true);
        $response = $this->userController->register('testuser', 'newpassword456', true);

        $this->assertFalse($response['success']);
        $this->assertEquals('Имя пользователя уже занято.', $response['error']);
    }

    public function testDeleteUser()
    {
        $this->userController->register('testuser', 'password123', true);

        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute(['testuser']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $userId = $user['id'];

        $this->userController->deleteUserAccount($userId);

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $deletedUser = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEmpty($deletedUser);
    }

    public function testLoginSuccess()
    {
        $username = 'testuser';
        $password = 'password123';
        $this->userController->register($username, $password, true);

        $response = $this->userController->login($username, $password, true);

        $this->assertTrue($response['success']);
        $this->assertEquals('Вход выполнен успешно!', $response['message']);
        $this->assertEquals($username, $response['username']);
        $this->assertArrayHasKey('user_id', $response);
    }

    public function testLoginInvalidCredentials()
    {
        $username = 'testuser';
        $password = 'password123';
        $this->userController->register($username, $password, true);

        $response = $this->userController->login($username, 'wrongpassword', true);

        $this->assertFalse($response['success']);
        $this->assertEquals('Неверное имя пользователя или пароль.', $response['error']);
    }
    
    public function testLoginApiSuccess()
    {
        $username = 'testuser';
        $password = 'password123';
        $this->userController->register($username, $password, true);

        $response = $this->userController->login($username, $password, true);

        $this->assertTrue($response['success']);
        $this->assertEquals('Вход выполнен успешно!', $response['message']);
        $this->assertArrayHasKey('user_id', $response);
        $this->assertArrayHasKey('username', $response);
    }

    public function testLoginApiInvalidCredentials()
    {
        $username = 'testuser';
        $password = 'password123';
        $this->userController->register($username, $password, true);

        $response = $this->userController->login($username, 'wrongpassword', true);

        $this->assertFalse($response['success']);
        $this->assertEquals('Неверное имя пользователя или пароль.', $response['error']);
    }

    protected function tearDown(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS users");
        $this->pdo->exec("DROP TABLE IF EXISTS games");
    }
}
?>
