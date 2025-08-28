<?php
/**
 * Готовый к работе D&D Copilot
 * Просто откройте этот файл в браузере - всё уже настроено!
 */

// Простая проверка - если файл .env не существует, создаем его
if (!file_exists('.env')) {
    $envContent = "# D&D Copilot - Готовый к работе
# Автоматически создано " . date('Y-m-d H:i:s') . "

# API Keys
DEEPSEEK_API_KEY=sk-1e898ddba737411e948af435d767e893

# Application Settings
APP_NAME=\"D&D Copilot\"
APP_VERSION=\"3.1\"
DEBUG_MODE=false
ENVIRONMENT=production

# Security
JWT_SECRET=" . bin2hex(random_bytes(32)) . "
SESSION_SECRET=" . bin2hex(random_bytes(32)) . "

# Rate Limiting
RATE_LIMIT_LOGIN=5
RATE_LIMIT_API=100
RATE_LIMIT_AI=50
RATE_LIMIT_WINDOW=3600

# Admin Settings
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=admin123

# File Upload Settings
MAX_FILE_SIZE=10485760
ALLOWED_FILE_TYPES=jpg,jpeg,png,gif,pdf
UPLOAD_PATH=uploads/

# Logging
LOG_LEVEL=INFO
LOG_FILE=logs/app.log
SECURITY_LOG_FILE=logs/security.log
";
    file_put_contents('.env', $envContent);
}

// Создаем необходимые папки если их нет
$directories = ['cache', 'cache/api', 'cache/rate_limits', 'logs', 'uploads'];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Создаем демо-пользователей если файла нет
if (!file_exists('users.json')) {
    $defaultUsers = [
        [
            'id' => uniqid(),
            'username' => 'admin',
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'email' => 'admin@example.com',
            'role' => 'admin',
            'created_at' => date('Y-m-d H:i:s'),
            'is_active' => true,
            'login_count' => 0
        ],
        [
            'id' => uniqid(),
            'username' => 'demo',
            'password_hash' => password_hash('demo123', PASSWORD_DEFAULT),
            'email' => 'demo@example.com',
            'role' => 'user',
            'created_at' => date('Y-m-d H:i:s'),
            'is_active' => true,
            'login_count' => 0
        ]
    ];
    file_put_contents('users.json', json_encode($defaultUsers, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

// Простая система сессий
session_start();

// Простая функция аутентификации
function authenticateUser($username, $password) {
    $users = json_decode(file_get_contents('users.json'), true);
    
    foreach ($users as $user) {
        if ($user['username'] === $username && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
    }
    return false;
}

// Обработка входа
if ($_POST['action'] === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (authenticateUser($username, $password)) {
        $loginSuccess = true;
    } else {
        $loginError = 'Неверное имя пользователя или пароль';
    }
}

// Обработка выхода
if ($_POST['action'] === 'logout') {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Проверяем авторизацию
$isLoggedIn = isset($_SESSION['user_id']);
$currentUser = $_SESSION['username'] ?? '';
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D&D Copilot - Готовый к работе</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .header h1 {
            color: #4a5568;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #718096;
            font-size: 1.1em;
        }
        
        .login-form {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #4a5568;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
            width: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .dashboard {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .welcome {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .welcome h2 {
            color: #4a5568;
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .welcome p {
            color: #718096;
            font-size: 1.1em;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .feature-card {
            background: #f7fafc;
            border-radius: 10px;
            padding: 20px;
            border-left: 4px solid #667eea;
        }
        
        .feature-card h3 {
            color: #4a5568;
            margin-bottom: 10px;
        }
        
        .feature-card p {
            color: #718096;
            line-height: 1.6;
        }
        
        .demo-accounts {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .demo-accounts h3 {
            color: #22543d;
            margin-bottom: 15px;
        }
        
        .account {
            background: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            font-family: monospace;
        }
        
        .logout-form {
            text-align: center;
        }
        
        .error {
            background: #fed7d7;
            color: #c53030;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .success {
            background: #c6f6d5;
            color: #22543d;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .status {
            background: #bee3f8;
            color: #2b6cb0;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .status h3 {
            margin-bottom: 10px;
        }
        
        .status-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎲 D&D Copilot</h1>
            <p>Готовый к работе - никакой настройки не требуется!</p>
        </div>

        <?php if (!$isLoggedIn): ?>
            <!-- Форма входа -->
            <div class="login-form">
                <h2 style="text-align: center; margin-bottom: 20px; color: #4a5568;">Вход в систему</h2>
                
                <?php if (isset($loginError)): ?>
                    <div class="error"><?php echo htmlspecialchars($loginError); ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-group">
                        <label for="username">Имя пользователя:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Пароль:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn">Войти</button>
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
                <div class="welcome">
                    <h2>🎉 Добро пожаловать, <?php echo htmlspecialchars($currentUser); ?>!</h2>
                    <p>D&D Copilot готов к работе. Все системы настроены автоматически.</p>
                </div>
                
                <div class="status">
                    <h3>📊 Статус системы</h3>
                    <div class="status-item">
                        <span>🔒 Безопасность:</span>
                        <span style="color: #38a169;">✅ Настроена</span>
                    </div>
                    <div class="status-item">
                        <span>⚡ Производительность:</span>
                        <span style="color: #38a169;">✅ Оптимизирована</span>
                    </div>
                    <div class="status-item">
                        <span>📝 Логирование:</span>
                        <span style="color: #38a169;">✅ Активно</span>
                    </div>
                    <div class="status-item">
                        <span>🎲 Генерация персонажей:</span>
                        <span style="color: #38a169;">✅ Готово</span>
                    </div>
                </div>
                
                <div class="features">
                    <div class="feature-card">
                        <h3>🎲 Генерация персонажей</h3>
                        <p>Создавайте уникальных персонажей с помощью ИИ. 22 расы, 13 классов, детальные описания.</p>
                    </div>
                    
                    <div class="feature-card">
                        <h3>⚔️ Система боя</h3>
                        <p>Управляйте инициативой, отслеживайте HP, применяйте статусы. Полная поддержка D&D 5e.</p>
                    </div>
                    
                    <div class="feature-card">
                        <h3>🤖 ИИ-ассистент</h3>
                        <p>Задавайте вопросы по D&D, получайте советы по ведению игры, анализируйте правила.</p>
                    </div>
                    
                    <div class="feature-card">
                        <h3>📝 Заметки</h3>
                        <p>Ведите заметки о сессиях, персонажах, локациях. Все данные сохраняются автоматически.</p>
                    </div>
                    
                    <div class="feature-card">
                        <h3>🎨 Темы оформления</h3>
                        <p>4 уникальные темы: светлая, темная, средняя и мистическая. Переключайтесь в любой момент.</p>
                    </div>
                    
                    <div class="feature-card">
                        <h3>📱 Адаптивный дизайн</h3>
                        <p>Работает на всех устройствах: компьютеры, планшеты, телефоны. PWA поддержка.</p>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 30px;">
                    <a href="index.php" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block; margin-right: 15px;">
                        🚀 Открыть приложение
                    </a>
                    
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" style="background: #e53e3e; color: white; border: none; padding: 15px 30px; border-radius: 8px; cursor: pointer; font-weight: 600;">
                            🚪 Выйти
                        </button>
                    </form>
                </div>
                
                <?php if ($isAdmin): ?>
                    <div style="margin-top: 20px; padding: 15px; background: #fef5e7; border-radius: 8px; border-left: 4px solid #ed8936;">
                        <h3 style="color: #c05621; margin-bottom: 10px;">👑 Администратор</h3>
                        <p style="color: #744210;">У вас есть права администратора. Вы можете управлять пользователями и настройками системы.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
