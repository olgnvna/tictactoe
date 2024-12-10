<?php
require_once __DIR__ . '/../models/GameModel.php'; 


class GameController {
    private $db;

    public function __construct(PDO $db) {
        $this->model = new GameModel($db);
    }

    public function apiStartNewGame($userId) {
        $gameId = $this->startNewGame($userId); 
        return [
            'success' => true,
            'redirect' => "/game?id=$gameId"
        ];
    }

    public function apiPlayTurn($data, $userId) {
        if (!isset($data['game_id'], $data['x'], $data['y'], $data['player'])) {
            return [
                'success' => false,
                'error' => 'Потеряные необходимые параметры.'
            ];
        }
    
        $game = $this->getGameById($data['game_id']);
        
        if (!$game) {
            return [
                'success' => false,
                'error' => 'Игра не найдена.'
            ];
        }
    
        if ($game['status'] !== "В процессе") {
            return [
                'success' => false,
                'board' => json_decode($game['board'], true),
                'error' => 'Игра завершена. Статус: ' . $game['status']
            ];
        }
    
        $status = $this->playTurn($data['game_id'], (int)$data['x'], (int)$data['y'], $data['player']);
        $game = $this->getGameById($data['game_id']);  
    
        return [
            'success' => true,
            'board' => json_decode($game['board'], true),
            'status' => $status
        ];
    }
    
    public function getUserStats($userId) {
        return $this->model->fetchUserStats($userId);
    }
    

    public function startGame($userId) {
        return $this->model->createGame($userId);
    }

    public function startNewGame($userId) {
        $gameId = $this->model->createGame($userId);
        $this->model->resetGame($gameId);
    
        $game = $this->model->getGame($gameId); 
    
        return $gameId;
    }
    
    public function getActiveGame($userId) {
        $game = $this->model->findActiveGame($userId);
        return $game;
    }    

    public function playTurn($gameId, $x, $y) {
        $game = $this->model->getGame($gameId);
    
        if (!$game || $game['status'] !== "В процессе") {
            return "Игра не найдена или завершена.";
        }
    
        $board = json_decode($game['board'], true);
        $currentPlayer = $game['current_player'];
    
        if ($board[$x][$y] !== null) {
            return "Ячейка уже занята.";
        }
    
        $board[$x][$y] = $currentPlayer;
        $status = $this->checkGameStatus($board, $currentPlayer);
        $nextPlayer = $currentPlayer === 'X' ? 'O' : 'X';
        $this->model->updateGame($gameId, $board, $status, $nextPlayer);
    
        return $status;
    }     

    public function getGameById($gameId) {
        return $this->model->getGame($gameId);
    }
    

    private function checkGameStatus($board, $player) {
        for ($i = 0; $i < 3; $i++) {
            if ($board[$i][0] === $player && $board[$i][1] === $player && $board[$i][2] === $player) return "Победа $player";
            if ($board[0][$i] === $player && $board[1][$i] === $player && $board[2][$i] === $player) return "Победа $player";
        }
        if ($board[0][0] === $player && $board[1][1] === $player && $board[2][2] === $player) return "Победа $player";
        if ($board[0][2] === $player && $board[1][1] === $player && $board[2][0] === $player) return "Победа $player";

        foreach ($board as $row) {
            if (in_array(null, $row)) return "В процессе";
        }

        return "Ничья";
    }

    public function clearUserGames($userId) {
        return $this->model->deleteAllGamesByUser($userId);
    }
    
}