<?php

use PHPUnit\Framework\TestCase;

class GameModelTest extends TestCase
{
    private $pdo;
    private $gameModel;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->exec("CREATE TABLE games (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            board TEXT,
            status TEXT,
            current_player TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        require_once __DIR__ . '/../../models/GameModel.php';
        $this->gameModel = new GameModel($this->pdo);
    }

    public function testCreateGame()
    {
        $userId = 1;

        $gameId = $this->gameModel->createGame($userId);

        $stmt = $this->pdo->prepare("SELECT * FROM games WHERE id = ?");
        $stmt->execute([$gameId]);
        $game = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($game);
        $this->assertEquals($userId, $game['user_id']);
        $this->assertEquals(json_encode([[null, null, null], [null, null, null], [null, null, null]]), $game['board']);
        $this->assertEquals('В процессе', $game['status']);
    }

    public function testResetGame()
    {
        $userId = 1;
        $gameId = $this->gameModel->createGame($userId);

        $this->gameModel->resetGame($gameId);

        $stmt = $this->pdo->prepare("SELECT * FROM games WHERE id = ?");
        $stmt->execute([$gameId]);
        $game = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(json_encode([[null, null, null], [null, null, null], [null, null, null]]), $game['board']);
        $this->assertEquals('В процессе', $game['status']);
        $this->assertEquals('X', $game['current_player']);
    }

    public function testFindActiveGame()
    {
        $userId = 1;
        $this->gameModel->createGame($userId);

        $activeGame = $this->gameModel->findActiveGame($userId);

        $this->assertNotEmpty($activeGame);
        $this->assertEquals($userId, $activeGame['user_id']);
        $this->assertEquals('В процессе', $activeGame['status']);
    }

    public function testFetchUserStats()
    {
        $userId = 1;

        $this->gameModel->createGame($userId);
        $this->pdo->exec("UPDATE games SET status = 'Победа X' WHERE id = 1");
        $this->gameModel->createGame($userId);
        $this->pdo->exec("UPDATE games SET status = 'Ничья' WHERE id = 2");

        $stats = $this->gameModel->fetchUserStats($userId);

        $this->assertEquals(2, $stats['total_games']);
        $this->assertEquals(1, $stats['x_wins']);
        $this->assertEquals(0, $stats['o_wins']);
        $this->assertEquals(1, $stats['draws']);
    }

    public function testUpdateGame()
    {
        $userId = 1;
        $gameId = $this->gameModel->createGame($userId);

        $newBoard = [
            ['X', null, 'O'],
            [null, 'X', null],
            ['O', null, 'X']
        ];
        $newStatus = 'Победа X';
        $nextPlayer = 'O';

        $this->gameModel->updateGame($gameId, $newBoard, $newStatus, $nextPlayer);

        $stmt = $this->pdo->prepare("SELECT * FROM games WHERE id = ?");
        $stmt->execute([$gameId]);
        $game = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(json_encode($newBoard), $game['board']);
        $this->assertEquals($newStatus, $game['status']);
        $this->assertEquals($nextPlayer, $game['current_player']);
    }

    public function testDeleteAllGamesByUser()
    {
        $userId = 1;
        $this->gameModel->createGame($userId);
        $this->gameModel->createGame($userId);

        $this->gameModel->deleteAllGamesByUser($userId);

        $stmt = $this->pdo->prepare("SELECT * FROM games WHERE user_id = ?");
        $stmt->execute([$userId]);
        $games = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertEmpty($games);
    }
}
