<?php

class GameModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createGame($userId) {
        $emptyBoard = json_encode([
            [null, null, null],
            [null, null, null],
            [null, null, null],
        ]);
        $stmt = $this->db->prepare("INSERT INTO games (user_id, board, status) VALUES (?, ?, 'В процессе')");
        if ($stmt->execute([$userId, $emptyBoard])) {
            return $this->db->lastInsertId();
        } else {
            throw new Exception("Error executing query: " . implode(", ", $stmt->errorInfo()));
        }
    }

    public function resetGame($gameId) {
        $emptyBoard = json_encode([
            [null, null, null],
            [null, null, null],
            [null, null, null],
        ]);
    
        $stmt = $this->db->prepare("UPDATE games SET board = ?, status = 'В процессе', current_player = 'X' WHERE id = ?");
        if ($stmt->execute([$emptyBoard, $gameId])) {
            return true;
        } else {
            throw new Exception("Error executing query: " . implode(", ", $stmt->errorInfo()));
        }
    }

    public function getGame($gameId) {
        $stmt = $this->db->prepare("SELECT * FROM games WHERE id = ?");
        $stmt->execute([$gameId]);
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            throw new Exception("Game with ID $gameId not found.");
        }
    }

    public function findActiveGame($userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM games 
            WHERE user_id = ? 
              AND status = 'В процессе'
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchUserStats($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) AS total_games,
                SUM(CASE WHEN status LIKE 'Победа X%' THEN 1 ELSE 0 END) AS x_wins,
                SUM(CASE WHEN status LIKE 'Победа O%' THEN 1 ELSE 0 END) AS o_wins,
                SUM(CASE WHEN status = 'Ничья' THEN 1 ELSE 0 END) AS draws
            FROM games
            WHERE user_id = ?
        ");
        
        if ($stmt->execute([$userId])) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            throw new Exception("Error executing query: " . implode(", ", $stmt->errorInfo()));
        }
    }

    public function updateGame($gameId, $board, $status, $nextPlayer) {
        $boardJson = json_encode($board);
        $stmt = $this->db->prepare("UPDATE games SET board = ?, status = ?, current_player = ? WHERE id = ?");
        if ($stmt->execute([$boardJson, $status, $nextPlayer, $gameId])) {
            return true;
        } else {
            throw new Exception("Error executing query: " . implode(", ", $stmt->errorInfo()));
        }
    }

    public function deleteAllGamesByUser($userId) {
        $stmt = $this->db->prepare("DELETE FROM games WHERE user_id = ?");
        if ($stmt->execute([$userId])) {
            return true;
        } else {
            throw new Exception("Error executing query: " . implode(", ", $stmt->errorInfo()));
        }
    }
}
