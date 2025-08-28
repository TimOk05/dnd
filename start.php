<?php
// Максимально простая версия - только HTML и базовый PHP

// Простая проверка PHP
$php_works = true;
$error_message = '';

// Пытаемся создать папки
try {
    if (!is_dir('cache')) {
        mkdir('cache', 0755);
    }
    if (!is_dir('logs')) {
        mkdir('logs', 0755);
    }
    if (!is_dir('uploads')) {
        mkdir('uploads', 0755);
    }
} catch (Exception $e) {
    $error_message = 'Не удалось создать папки: ' . $e->getMessage();
}

// Простая сессия
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Обработка входа
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Простая проверка
    if (($username === 'admin' && $password === 'admin123') || 
        ($username === 'demo' && $password === 'demo123')) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = ($username === 'admin') ? 'admin' : 'user';
    }
}

// Обработка выхода
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
$currentUser = $_SESSION['username'] ?? '';
$isAdmin = $_SESSION['role'] ?? '' === 'admin';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D&D Copilot - Запуск</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #667eea;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .login-form {
            max-width: 300px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }
        .btn:hover {
            background: #5a67d8;
        }
        .demo-accounts {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
        }
        .account {
            background: white;
            padding: 8px;
            border-radius: 3px;
            margin-bottom: 8px;
            font-family: monospace;
        }
        .dashboard {
            text-align: center;
        }
        .status {
            background: #bee3f8;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .actions {
            margin-top: 30px;
        }
        .actions a, .actions button {
            display: inline-block;
            margin: 0 10px;
            padding: 12px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-danger {
            background: #e53e3e;
            color: white;
            border: none;
            cursor: pointer;
        }
        .error {
            background: #fed7d7;
            color: #c53030;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎲 D&D Copilot</h1>
        
        <?php if ($error_message): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
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
                <h2>🎉 Добро пожаловать, <?php echo htmlspecialchars($currentUser); ?>!</h2>
                
                <div class="status">
                    <h3>📊 Статус системы</h3>
                    <p>✅ PHP работает корректно</p>
                    <p>✅ Сессии настроены</p>
                    <p>✅ Готов к работе</p>
                </div>
                
                <div class="actions">
                    <a href="index.php" class="btn-primary">🚀 Открыть приложение</a>
                    
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="logout" class="btn-danger">🚪 Выйти</button>
                    </form>
                </div>
                
                <?php if ($isAdmin): ?>
                    <div style="margin-top: 20px; padding: 15px; background: #fef5e7; border-radius: 5px; border-left: 4px solid #ed8936;">
                        <h3 style="color: #c05621; margin-bottom: 10px;">👑 Администратор</h3>
                        <p style="color: #744210;">У вас есть права администратора</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
