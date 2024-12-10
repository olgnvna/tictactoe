<?php
require_once __DIR__ . '/../models/UserModel.php';

class UserController
{
    private $pdo;
    protected $userModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->userModel = new UserModel($pdo);
    }

    public function register($username, $password, $isApi = false)
    {
        if ($this->userModel->checkUsernameExists($username)) {
            $message = 'Имя пользователя уже занято.';
            if ($isApi) {
                return ['success' => false, 'error' => $message];
            }
            $this->redirectWithMessage('/register', $message, true);
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $this->userModel->createUser($username, $hashedPassword);

        $message = 'Регистрация прошла успешно!';
        if ($isApi) {
            return ['success' => true, 'message' => $message];
        }
        $this->redirectWithMessage('/home', $message);
    }

    public function login($username, $password, $isApi = false)
    {
        $user = $this->userModel->getUserByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user['username'];

            $message = 'Вход выполнен успешно!';
            if ($isApi) {
                return ['success' => true, 'message' => $message, 'user_id' => $user['id'], 'username' => $user['username']];
            }
            $this->redirectWithMessage('/home', $message);
        }

        $message = 'Неверное имя пользователя или пароль.';
        if ($isApi) {
            return ['success' => false, 'error' => $message];
        }
        $this->redirectWithMessage('/login', $message, true);
    }

    private function redirectWithMessage($location, $message, $isError = false)
    {
        $key = $isError ? 'error' : 'success';
        $_SESSION[$key] = $message;
        echo "<script>alert('$message'); window.location.href='$location';</script>";
        exit();
    }
    
    public function updateUserInfo($userId, $newUsername, $newPassword) {
        $this->userModel->updateUser($userId, $newUsername, $newPassword);
    }

    public function deleteUserAccount($userId) {
        $this->userModel->deleteUser($userId);
    }
}
