<?php
// Простая версия D&D Copilot - без ошибок!

// Создаем папки если их нет
$dirs = ['cache', 'logs', 'uploads'];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Создаем простой .env файл
if (!file_exists('.env')) {
    $env = "# D&D Copilot - Простая версия
DEEPSEEK_API_KEY=sk-1e898ddba737411e948af435d767e893
APP_NAME=\"D&D Copilot\"
DEBUG_MODE=false
";
    file_put_contents('.env', $env);
}

// Создаем пользователей
if (!file_exists('users.json')) {
    $users = [
        [
            'id' => '1',
            'username' => 'admin',
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin'
        ],
        [
            'id' => '2', 
            'username' => 'demo',
            'password_hash' => password_hash('demo123', PASSWORD_DEFAULT),
            'role' => 'user'
        ]
    ];
    file_put_contents('users.json', json_encode($users));
}

// Запускаем сессию
@session_start();

// Обработка входа
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $users = json_decode(file_get_contents('users.json'), true);
    foreach ($users as $user) {
        if ($user['username'] === $username && password_verify($password, $user['password_hash'])) {
            $_SESSION['user'] = $user;
            break;
        }
    }
}

// Обработка выхода
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$isLoggedIn = isset($_SESSION['user']);
$currentUser = $_SESSION['user']['username'] ?? '';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D&D Copilot - Простой запуск</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1 {
            text-align: center;
            color: #4a5568;
            margin-bottom: 30px;
        }
        .login-form {
            max-width: 400px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #4a5568;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .demo-accounts {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        .account {
            background: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            font-family: monospace;
        }
        .dashboard {
            text-align: center;
        }
        .status {
            background: #bee3f8;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .feature {
            background: #f7fafc;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        .feature h3 {
            color: #4a5568;
            margin-bottom: 10px;
        }
        .feature p {
            color: #718096;
            line-height: 1.6;
        }
        .actions {
            margin-top: 30px;
        }
        .actions a, .actions button {
            display: inline-block;
            margin: 0 10px;
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-danger {
            background: #e53e3e;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎲 D&D Copilot</h1>
        
        <?php if (!$isLoggedIn): ?>
            <!-- Форма входа -->
            <div class="login-form">
                <h2 style="text-align: center; margin-bottom: 30px;">Вход в систему</h2>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Имя пользователя:</label>
                        <input type="text" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Пароль:</label>
                        <input type="password" name="password" required>
                    </div>
                    
                    <button type="submit" name="login" class="btn">Войти</button>
                </form>
                
                <div class="demo-accounts">
                    <h3>📋 Демо-аккаунты:</h3>
                    <div class="account">👤 Администратор: admin / admin123</div>
                    <div class="account">👤 Демо-пользователь: demo / demo123</div>
                </div>
            </div>
        <?php else: ?>
            <!-- Панель управления -->
            <div class="dashboard">
                <h2>🎉 Добро пожаловать, <?php echo htmlspecialchars($currentUser); ?>!</h2>
                
                <div class="status">
                    <h3>📊 Статус системы</h3>
                    <p>✅ Все системы готовы к работе!</p>
                    <p>✅ Файлы настроены автоматически</p>
                    <p>✅ Демо-пользователи созданы</p>
                </div>
                
                <div class="features">
                    <div class="feature">
                        <h3>🎲 Генерация персонажей</h3>
                        <p>Создавайте уникальных персонажей с помощью ИИ</p>
                    </div>
                    
                    <div class="feature">
                        <h3>⚔️ Система боя</h3>
                        <p>Управляйте инициативой и отслеживайте HP</p>
                    </div>
                    
                    <div class="feature">
                        <h3>🤖 ИИ-ассистент</h3>
                        <p>Задавайте вопросы по D&D и получайте советы</p>
                    </div>
                    
                    <div class="feature">
                        <h3>📝 Заметки</h3>
                        <p>Ведите заметки о сессиях и персонажах</p>
                    </div>
                </div>
                
                <div class="actions">
                    <a href="index.php" class="btn-primary">🚀 Открыть приложение</a>
                    
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="logout" class="btn-danger">🚪 Выйти</button>
                    </form>
                </div>
                
                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    <div style="margin-top: 20px; padding: 15px; background: #fef5e7; border-radius: 8px; border-left: 4px solid #ed8936;">
                        <h3 style="color: #c05621; margin-bottom: 10px;">👑 Администратор</h3>
                        <p style="color: #744210;">У вас есть права администратора</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
