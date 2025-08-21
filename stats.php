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
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .stats-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .stats-header {
            background: #3498db;
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
            background: #ecf0f1;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .stat-label {
            color: #7f8c8d;
            font-size: 1.1em;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .user-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #2c3e50;
        }
        .info-value {
            color: #7f8c8d;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #3498db;
            text-decoration: none;
            padding: 10px 20px;
            background: #ecf0f1;
            border-radius: 5px;
        }
        .back-link:hover {
            background: #d5dbdb;
        }
        .achievement {
            background: #f39c12;
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
</body>
</html>
