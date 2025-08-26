<?php
require_once 'users.php';

// Проверяем авторизацию
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();
$users = loadUsers();

// Находим данные текущего пользователя
$userData = null;
foreach ($users as $user) {
    if (hash_equals($user['username'], $currentUser)) {
        $userData = $user;
        break;
    }
}

// Статистика приложения
$totalUsers = count($users);
$totalLogins = 0;
$activeUsers = 0;

foreach ($users as $user) {
    if (isset($user['login_count'])) {
        $totalLogins += $user['login_count'];
    }
    if (isset($user['last_login'])) {
        $lastLogin = strtotime($user['last_login']);
        if ($lastLogin > (time() - 86400)) { // Активны за последние 24 часа
            $activeUsers++;
        }
    }
}

// Статистика пользователя
$userLoginCount = $userData['login_count'] ?? 0;
$userCreatedAt = $userData['created_at'] ?? 'Неизвестно';
$userLastLogin = $userData['last_login'] ?? 'Никогда';

// Вычисляем время с регистрации
$daysSinceRegistration = 0;
if ($userCreatedAt !== 'Неизвестно') {
    $created = strtotime($userCreatedAt);
    $daysSinceRegistration = floor((time() - $created) / 86400);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Статистика - DnD Copilot</title>
    <style>
        :root {
            /* Светлая тема */
            --bg-primary: #e8d8b0;
            --bg-secondary: #f0e6c0;
            --bg-tertiary: #e0d0a0;
            --text-primary: #2d1b00;
            --text-secondary: #3d2a0a;
            --text-tertiary: #7c4a02;
            --accent-primary: #a67c52;
            --accent-secondary: #7c4a02;
            --border-primary: #a67c52;
            --shadow-primary: #0002;
        }
        
        [data-theme="dark"] {
            --bg-primary: #0a0a0a;
            --bg-secondary: #1a0a0a;
            --bg-tertiary: #2a0a0a;
            --text-primary: #ff4444;
            --text-secondary: #ff6666;
            --text-tertiary: #ff8888;
            --accent-primary: #ff3333;
            --accent-secondary: #cc2222;
            --border-primary: #ff0000;
            --shadow-primary: rgba(255, 0, 0, 0.3);
        }
        

        
        body {
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: var(--bg-primary);
            color: var(--text-primary);
            transition: all 0.3s ease;
        }
        
        .theme-switcher {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .theme-btn {
            background: var(--accent-primary);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            margin-left: 5px;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }
        
        .theme-btn:hover {
            background: var(--accent-secondary);
        }
        
        .theme-btn.active {
            background: var(--accent-secondary);
            box-shadow: 0 0 10px var(--accent-primary);
        }
        
        .stats-container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--bg-secondary);
            border-radius: 10px;
            box-shadow: 0 4px 20px var(--shadow-primary);
            overflow: hidden;
            border: 2px solid var(--border-primary);
        }
        
        .stats-header {
            background: var(--accent-primary);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .stats-header h1 {
            margin: 0;
            font-size: 2em;
        }
        
        .stats-content {
            padding: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--bg-tertiary);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid var(--border-primary);
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: var(--text-primary);
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: 1.1em;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section h2 {
            color: var(--text-primary);
            border-bottom: 2px solid var(--accent-primary);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .user-info {
            background: var(--bg-tertiary);
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--accent-primary);
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-primary);
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: bold;
            color: var(--text-primary);
        }
        
        .info-value {
            color: var(--text-secondary);
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--accent-primary);
            text-decoration: none;
            padding: 10px 20px;
            background: var(--bg-tertiary);
            border-radius: 5px;
            border: 1px solid var(--border-primary);
            transition: all 0.3s ease;
        }
        .back-link:hover {
            background: var(--accent-primary);
            color: white;
        }
        .achievement {
            background: var(--accent-secondary);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            text-align: center;
        }
        .achievement h3 {
            margin: 0 0 10px 0;
        }
    </style>
</head>
<body>
    <!-- Переключатель тем -->
    <div class="theme-switcher">
        <button class="theme-btn active" onclick="setTheme('light')">☀️</button>
        <button class="theme-btn" onclick="setTheme('dark')">🌙</button>
    </div>
    
    <div class="stats-container">
        <div class="stats-header">
            <h1>📊 Статистика</h1>
            <p>Ваша активность и статистика приложения</p>
        </div>
        
        <div class="stats-content">
            <a href="index.php" class="back-link">← Вернуться к приложению</a>
            
            <!-- Статистика пользователя -->
            <div class="section">
                <h2>👤 Ваша статистика</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $userLoginCount; ?></div>
                        <div class="stat-label">Количество входов</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $daysSinceRegistration; ?></div>
                        <div class="stat-label">Дней с регистрации</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">
                            <?php echo $userLoginCount > 0 ? round($userLoginCount / max(1, $daysSinceRegistration), 1) : 0; ?>
                        </div>
                        <div class="stat-label">Входов в день</div>
                    </div>
                </div>
                
                <div class="user-info">
                    <div class="info-row">
                        <span class="info-label">Имя пользователя:</span>
                        <span class="info-value"><?php echo htmlspecialchars($currentUser); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Дата регистрации:</span>
                        <span class="info-value"><?php echo htmlspecialchars($userCreatedAt); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Последний вход:</span>
                        <span class="info-value"><?php echo htmlspecialchars($userLastLogin); ?></span>
                    </div>
                </div>
                
                <!-- Достижения -->
                <?php if ($userLoginCount >= 10): ?>
                    <div class="achievement">
                        <h3>🏆 Постоянный пользователь</h3>
                        <p>Вы вошли в систему более 10 раз!</p>
                    </div>
                <?php endif; ?>
                
                <?php if ($daysSinceRegistration >= 7): ?>
                    <div class="achievement">
                        <h3>📅 Неделя с нами</h3>
                        <p>Вы используете приложение уже неделю!</p>
                    </div>
                <?php endif; ?>
                
                <?php if ($userLoginCount >= 5 && $daysSinceRegistration >= 3): ?>
                    <div class="achievement">
                        <h3>🎯 Активный игрок</h3>
                        <p>Вы регулярно используете DnD Copilot!</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Статистика приложения -->
            <div class="section">
                <h2>🌐 Статистика приложения</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $totalUsers; ?></div>
                        <div class="stat-label">Всего пользователей</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $activeUsers; ?></div>
                        <div class="stat-label">Активных за 24 часа</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $totalLogins; ?></div>
                        <div class="stat-label">Всего входов</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">
                            <?php echo $totalUsers > 0 ? round($totalLogins / $totalUsers, 1) : 0; ?>
                        </div>
                        <div class="stat-label">Среднее входов на пользователя</div>
                    </div>
                </div>
            </div>
            
            <!-- Советы -->
            <div class="section">
                <h2>💡 Советы по использованию</h2>
                <div class="user-info">
                    <p><strong>🎲 Бросок костей:</strong> Используйте F1 для быстрого доступа к броскам костей</p>
                    <p><strong>🗣️ Генерация NPC:</strong> Нажмите F2 для создания новых персонажей</p>
                    <p><strong>⚡ Инициатива:</strong> F3 поможет управлять инициативой в бою</p>
                    <p><strong>💬 Чат:</strong> Ctrl+Enter для быстрой отправки сообщений</p>
                    <p><strong>🌙 Тема:</strong> Переключайте между светлой и темной темой</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Функция для переключения тем
        function setTheme(theme) {
            // Убираем активный класс со всех кнопок
            document.querySelectorAll('.theme-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Добавляем активный класс к выбранной кнопке
            event.target.classList.add('active');
            
            // Устанавливаем тему
            document.documentElement.setAttribute('data-theme', theme);
            
            // Сохраняем выбор в localStorage
            localStorage.setItem('theme', theme);
        }
        
        // Загружаем сохраненную тему при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            setTheme(savedTheme);
            
            // Устанавливаем правильную активную кнопку
            document.querySelectorAll('.theme-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            const themeButtons = {
                'light': document.querySelector('.theme-btn:nth-child(1)'),
                'dark': document.querySelector('.theme-btn:nth-child(2)')
            };
            
            if (themeButtons[savedTheme]) {
                themeButtons[savedTheme].classList.add('active');
            }
        });
    </script>
</body>
</html>
