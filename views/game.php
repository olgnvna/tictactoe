<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Крестики-нолики</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kablammo&family=Montserrat+Alternates:wght@700&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="/assets/css/game_style.css">
</head>
<body>
    <header>
        <nav>
            <ul class="menu">
                <?php if (isset($_SESSION['user'])): ?>
                    <li><a href="/home">Главная</a></li>
                    <li><a href="/game">Игра</a></li>
                    <li><a href="/results">Профиль</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>
        <div class="container">
            <h1>Крестики-нолики</h1>
            <div class="board_back">
                <?php if (isset($board) && isset($status)): ?>
                    <table class="board" id="board">
                        <?php foreach ($board as $rowIndex => $row): ?>
                            <tr>
                                <?php foreach ($row as $colIndex => $cell): ?>
                                    <td>
                                        <?php if ($cell === null && $status === 'В процессе'): ?>
                                            <button onclick="playTurn(<?= $rowIndex ?>, <?= $colIndex ?>)" class="cell empty"></button>
                                            <?php else: ?>
                                                <div class="cell <?= $cell === 'X' ? 'x' : ($cell === 'O' ? 'o' : '') ?>">
                                                    <?= htmlspecialchars($cell ?: '-') ?>
                                                </div>
                                            <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </div>
            <p>Статус игры: <?= htmlspecialchars($status) ?></p>
        </div>

        <div class="game-controls">
            <button class="btn" onclick="startNewGame()">Начать заново</button>
            <a href="/home"><button class="btn">На главную</button></a>
        </div>
    </main>

    <script>
        function playTurn(x, y) {
            fetch('/api/game/play', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    game_id: <?= json_encode($game['id']) ?>,
                    x: x,
                    y: y,
                    player: 'X' 
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.board && data.status) {
                    updateBoard(data.board);
                    updateStatus(data.status);
                }
            });
        }

        function updateBoard(newBoard) {
            const boardElement = document.getElementById('board');
            newBoard.forEach((row, rowIndex) => {
                row.forEach((cell, colIndex) => {
                    const cellElement = boardElement.rows[rowIndex].cells[colIndex];
                    if (cell === null) {
                        cellElement.innerHTML = `<button onclick="playTurn(${rowIndex}, ${colIndex})" class="cell empty"></button>`;
                    } else if (cell === 'X') {
                        cellElement.innerHTML = `<div class="cell x">${cell}</div>`;
                    } else if (cell === 'O') {
                        cellElement.innerHTML = `<div class="cell o">${cell}</div>`;
                    }
                });
            });
        }

        function updateStatus(newStatus) {
            const statusElement = document.querySelector('p');
            statusElement.textContent = `Статус игры: ${newStatus}`;

            if (newStatus.includes('Победа') || newStatus.includes('Ничья')) {
                showFireworks();
            }
        }

        function startNewGame() {
            fetch('/api/game/start', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ new_game: true })
            })
            .then(response => response.json())
            .then(data => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            });
        }

        function showFireworks() {
            const fireworksContainer = document.getElementById('fireworks');
            fireworksContainer.classList.remove('hidden');

            function createFirework() {
                const firework = document.createElement('div');
                firework.classList.add('firework');
                const x = `${Math.random() * 200 - 100}%`;
                const y = `${Math.random() * 200 - 100}%`;

                firework.style.setProperty('--x', x);
                firework.style.setProperty('--y', y);
                firework.style.left = `${Math.random() * 100}%`;
                firework.style.top = `${Math.random() * 100}%`;
                firework.style.background = `hsl(${Math.random() * 360}, 100%, 50%)`;

                fireworksContainer.appendChild(firework);

                setTimeout(() => firework.remove(), 1000);
            }

            const interval = setInterval(() => {
                for (let i = 0; i < 5; i++) { 
                    createFirework();
                }
            }, 100);

            setTimeout(() => {
                clearInterval(interval);

                setTimeout(() => {
                    fireworksContainer.classList.add('hidden');
                    setTimeout(() => {
                        fireworksContainer.innerHTML = ''; 
                    }, 2000); 
                }, 1000);
            }, 5000);
        }
    </script>
    <div id="fireworks" class="hidden"></div>
</body>
</html>
