<?php
use PHPUnit\Framework\TestCase;

class GameControllerTest extends TestCase
{
    private $pdo;
    private $gameModelMock;
    private $gameController;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        require_once __DIR__ . '/../../models/GameModel.php';

        $this->gameModelMock = $this->createMock(GameModel::class);

        require_once __DIR__ . '/../../controllers/GameController.php';
        $this->gameController = new GameController($this->pdo);
        $this->gameController->model = $this->gameModelMock;
    }

    public function testApiStartNewGame()
    {
        $userId = 1;
        $gameId = 42;

        $this->gameModelMock->method('createGame')->willReturn($gameId);
        $this->gameModelMock->method('resetGame')->with($gameId);

        $response = $this->gameController->apiStartNewGame($userId);

        $this->assertTrue($response['success']);
        $this->assertEquals("/game?id=$gameId", $response['redirect']);
    }

    public function testApiPlayTurnGameNotFound()
    {
        $data = ['game_id' => 1, 'x' => 0, 'y' => 0, 'player' => 'X'];
        $userId = 1;

        $this->gameModelMock->method('getGame')->willReturn(null);

        $response = $this->gameController->apiPlayTurn($data, $userId);

        $this->assertFalse($response['success']);
        $this->assertEquals('Игра не найдена.', $response['error']);
    }

    public function testApiPlayTurnGameOver()
    {
        $data = ['game_id' => 1, 'x' => 0, 'y' => 0, 'player' => 'X'];
        $userId = 1;
        
        $game = ['status' => 'Завершена', 'board' => json_encode([[null, null, null], [null, null, null], [null, null, null]])];

        $this->gameModelMock->method('getGame')->willReturn($game);

        $response = $this->gameController->apiPlayTurn($data, $userId);

        $this->assertFalse($response['success']);
        $this->assertEquals('Игра завершена. Статус: Завершена', $response['error']);
    }

    public function testGetUserStats()
    {
        $userId = 1;
        $stats = ['games_played' => 10, 'games_won' => 5, 'games_lost' => 5];

        $this->gameModelMock->method('fetchUserStats')->willReturn($stats);

        $response = $this->gameController->getUserStats($userId);

        $this->assertEquals($stats, $response);
    }

    public function testGetActiveGame()
    {
        $userId = 1;
        $game = ['game_id' => 1, 'status' => 'В процессе'];

        $this->gameModelMock->method('findActiveGame')->willReturn($game);

        $response = $this->gameController->getActiveGame($userId);

        $this->assertEquals($game, $response);
    }

    public function testClearUserGames()
    {
        $userId = 1;

        $this->gameModelMock->method('deleteAllGamesByUser')->willReturn(true);

        $response = $this->gameController->clearUserGames($userId);

        $this->assertTrue($response);
    }

    public function testStartNewGame()
    {
        $userId = 1;
        $gameId = 42;

        $this->gameModelMock->method('createGame')->willReturn($gameId);
        $this->gameModelMock->method('resetGame')->with($gameId);

        $response = $this->gameController->startNewGame($userId);

        $this->assertEquals($gameId, $response);
    }

    public function testPlayTurnGameNotFound()
    {
        $gameId = 1;
        $x = 0;
        $y = 0;
        $player = 'X';

        $this->gameModelMock->method('getGame')->willReturn(null);

        $response = $this->gameController->playTurn($gameId, $x, $y, $player);

        $this->assertEquals("Игра не найдена или завершена.", $response);
    }
}
?>
