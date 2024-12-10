<?php

class UserModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function createUser($username, $password)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            $stmt->execute(['username' => $username, 'password' => $password]);
        } catch (PDOException $e) {
            echo 'Ошибка при добавлении пользователя: ' . $e->getMessage();
        }
    }

    public function checkUsernameExists($username)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        return $stmt->fetchColumn() > 0;
    }

    public function getUserByUsername($username)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function updateUser($userId, $newUsername, $newPassword)
    {
        $stmt = $this->pdo->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
        $stmt->execute([$newUsername, $newPassword, $userId]);
    }

    public function deleteUser($userId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM games WHERE user_id = ?");
        $stmt->execute([$userId]);

        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
    }

}