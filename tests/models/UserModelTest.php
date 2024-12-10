<?php
use PHPUnit\Framework\TestCase;

class UserModelTest extends TestCase
{
    private $pdo;
    private $userModel;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->exec("CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT,
            password TEXT
        )");

        $this->pdo->exec("CREATE TABLE games (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            board TEXT,
            status TEXT,
            current_player TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        require_once __DIR__ . '/../../models/UserModel.php';
        $this->userModel = new UserModel($this->pdo);
    }


    public function testCreateUser()
    {
        $username = "testuser";
        $password = "password123";
        
        $this->userModel->createUser($username, password_hash($password, PASSWORD_BCRYPT));

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($user);
        $this->assertEquals($username, $user['username']);
    }

    public function testCheckUsernameExists()
    {
        $username = "testuser";
        $password = "password123";

        $this->userModel->createUser($username, password_hash($password, PASSWORD_BCRYPT));

        $exists = $this->userModel->checkUsernameExists($username);
        $this->assertTrue($exists);

        $exists = $this->userModel->checkUsernameExists("nonexistentuser");
        $this->assertFalse($exists);
    }

    public function testGetUserByUsername()
    {
        $username = "testuser";
        $password = "password123";

        $this->userModel->createUser($username, password_hash($password, PASSWORD_BCRYPT));

        $user = $this->userModel->getUserByUsername($username);
        
        $this->assertNotEmpty($user);
        $this->assertEquals($username, $user['username']);
    }

    public function testUpdateUser()
    {
        $username = "testuser";
        $password = "password123";

        $this->userModel->createUser($username, password_hash($password, PASSWORD_BCRYPT));

        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $userId = $user['id'];

        $newUsername = "newtestuser";
        $newPassword = "newpassword123";
        $this->userModel->updateUser($userId, $newUsername, password_hash($newPassword, PASSWORD_BCRYPT));

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals($newUsername, $updatedUser['username']);
        $this->assertNotEquals(password_hash($password, PASSWORD_BCRYPT), $updatedUser['password']);
        $this->assertTrue(password_verify($newPassword, $updatedUser['password']));
    }

    public function testDeleteUser()
    {
        $username = "testuser";
        $password = "password123";

        $this->userModel->createUser($username, password_hash($password, PASSWORD_BCRYPT));

        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $userId = $user['id'];

        $this->userModel->deleteUser($userId);

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $deletedUser = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEmpty($deletedUser);
    }
}
?>
