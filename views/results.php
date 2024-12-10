<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kablammo&family=Montserrat+Alternates:wght@700&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="/assets/css/results_style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Профиль</title>
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
    <h1>Профиль пользователя</h1>
    <div class="container">
        <div id="noStatsMessage">Статистика отсутствует. Игры не найдены. </div>
        <table id="statsTable" style="display: none;">
            <thead>
                <tr>
                    <th>Общее количество игр</th>
                    <th>Победы X</th>
                    <th>Победы O</th>
                    <th>Ничьи</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td id="total_games"></td>
                    <td id="x_wins"></td>
                    <td id="o_wins"></td>
                    <td id="draws"></td>
                </tr>
            </tbody>
        </table>
        <div class="chart-container">
            <canvas id="statsChart" style="display: none;"></canvas>
        </div>
        <div class="actions">
            <button id="clearStatsButton" class="btn">Очистить статистику</button>
            <button id="deleteAccountButton" class="btn">Удалить учетную запись</button>
            <button id="updateInfoButton" class="btn">Обновить информацию</button>
        </div>
        <div id="updateModal" class="modal">
            <div class="modal-content">
                <span id="closeModal" class="close">&times;</span>
                <form id="updateForm">
                    <h2>Обновить информацию</h2>
                    <label for="username">Новое имя пользователя:</label>
                    <input type="text" id="username" name="username" required>
                    <label for="password">Новый пароль:</label>
                    <input type="password" id="password" name="password" required>
                    <button type="submit">Сохранить изменения</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        fetch('/api/user/stats')
            .then(response => response.json())
            .then(data => {
                if (data.error || data.total_games === 0) {
                    document.getElementById('noStatsMessage').style.display = 'block';
                    document.getElementById('statsTable').style.display = 'none';
                    document.getElementById('statsChart').style.display = 'none';
                } else {
                    document.getElementById('total_games').textContent = data.total_games;
                    document.getElementById('x_wins').textContent = data.x_wins;
                    document.getElementById('o_wins').textContent = data.o_wins;
                    document.getElementById('draws').textContent = data.draws;

                    document.getElementById('statsTable').style.display = 'table';
                    document.getElementById('statsChart').style.display = 'block';
                    document.getElementById('noStatsMessage').style.display = 'none';

                    const ctx = document.getElementById('statsChart').getContext('2d');
                    const statsChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Победы X', 'Победы O', 'Ничьи'],
                            datasets: [{
                                label: 'Статистика игр',
                                data: [
                                    data.x_wins,
                                    data.o_wins,
                                    data.draws
                                ],
                                backgroundColor: [
                                    'rgba(255, 222, 235, 0.7)', 
                                    'rgba(204, 240, 255, 0.7)', 
                                    'rgba(255, 245, 220, 0.7)' 
                                ],
                                borderColor: [
                                    'rgba(255, 222, 235, 1)',  
                                    'rgba(204, 240, 255, 1)',   
                                    'rgba(255, 245, 220, 1)' 
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        color: 'white'
                                    }
                                }
                            }
                        }
                    });
                }
            })
            .catch(error => console.error('Ошибка загрузки статистики пользователя:', error));

        document.getElementById('clearStatsButton').addEventListener('click', () => {
            fetch('/api/user/clear-games', { method: 'DELETE' })
                .then(response => response.json())
                .then(data => {
                alert(data.message || 'Статистика очищена.');
                    location.reload();
                })
                .catch(error => console.error('Ошибка очистки статистики:', error));
        });

        document.getElementById('deleteAccountButton').addEventListener('click', () => {
            if (confirm('Вы уверены, что хотите удалить учетную запись?')) {
                fetch('/api/user/delete', { method: 'DELETE' })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message || 'Учетная запись удалена.');
                        window.location.href = '/'; 
                    })
                    .catch(error => console.error('Ошибка удаления учетной записи:', error));
            }
        });

        document.getElementById('updateInfoButton').addEventListener('click', () => {
            const modal = document.getElementById('updateModal');
            modal.style.display = 'block';
        });

        document.getElementById('closeModal').addEventListener('click', () => {
            const modal = document.getElementById('updateModal');
            modal.style.display = 'none';
        });

        document.getElementById('updateForm').addEventListener('submit', (event) => {
            event.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            fetch('/api/user/update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            })
                .then(response => response.json())
                .then(data => {
                    alert(data.message || 'Информация обновлена.');
                    document.getElementById('updateModal').style.display = 'none';
                    window.location.href = '/'; 
                })
                .catch(error => console.error('Ошибка обновления информации:', error));
        });

    </script>
</body>
</html>