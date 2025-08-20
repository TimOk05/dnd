<?php
session_start();

// Простые данные администратора (без базы данных)
$ADMIN_USERNAME = 'admin';
$ADMIN_PASSWORD = 'TimOkdndAdm1n';

// Обработка входа
if ($_POST['action'] === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === $ADMIN_USERNAME && $password === $ADMIN_PASSWORD) {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = $ADMIN_USERNAME;
        $_SESSION['role'] = 'admin';
        $_SESSION['authenticated'] = true;
        
        header('Location: index.php');
        exit;
    } else {
        $error = 'Неверное имя пользователя или пароль';
    }
}

// Если уже авторизован, перенаправляем
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - DnD Copilot</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=UnifrakturCook:wght@700&family=IM+Fell+English+SC&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #f8ecd0;
            --bg-secondary: #fffbe6;
            --bg-tertiary: #f3e1b6;
            --text-primary: #2d1b00;
            --text-secondary: #3d2a0a;
            --text-tertiary: #7c4a02;
            --border-primary: #a67c52;
            --border-secondary: #7c4a02;
            --accent-primary: #a67c52;
            --accent-secondary: #7c4a02;
            --accent-success: #2bb07b;
            --accent-danger: #b71c1c;
            --shadow-primary: #0002;
            --shadow-secondary: #0006;
            --bg-image: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1500&q=80');
        }
        
        [data-theme="dark"] {
            --bg-primary: #0f0f23;
            --bg-secondary: #1a1a2e;
            --bg-tertiary: #16213e;
            --text-primary: #e8e8e8;
            --text-secondary: #d1d1d1;
            --text-tertiary: #b8a9c9;
            --border-primary: #7209b7;
            --border-secondary: #560bad;
            --accent-primary: #7209b7;
            --accent-secondary: #560bad;
            --accent-success: #06ffa5;
            --accent-danger: #ff006e;
            --shadow-primary: #0004;
            --shadow-secondary: #0008;
            --bg-image: url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=1500&q=80');
        }
        
        body {
            background: var(--bg-image) no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            margin: 0;
            font-family: 'Roboto', 'IM Fell English SC', serif;
            color: var(--text-primary);
            font-size: 1.05em;
            transition: all 0.3s ease;
        }
        
        .container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .auth-card {
            background: var(--bg-primary) url('https://www.transparenttextures.com/patterns/old-mathematics.png');
            border: 8px solid var(--border-primary);
            border-radius: 24px;
            box-shadow: 0 8px 32px var(--shadow-secondary), 0 0 0 12px rgba(210, 180, 140, 0.3);
            max-width: 450px;
            width: 100%;
            padding: 40px 30px;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .auth-card:before,
        .auth-card:after {
            content: '';
            position: absolute;
            width: 54px;
            height: 54px;
            background: url('https://cdn-icons-png.flaticon.com/512/616/616494.png') no-repeat center/contain;
            opacity: 0.12;
        }
        
        .auth-card:before {
            left: -30px;
            top: -30px;
        }
        
        .auth-card:after {
            right: -30px;
            bottom: -30px;
            transform: scaleX(-1);
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .auth-title {
            font-family: 'UnifrakturCook', cursive;
            font-size: 2.2em;
            color: var(--text-tertiary);
            margin-bottom: 10px;
            letter-spacing: 2px;
            text-shadow: 0 2px 0 rgba(255, 255, 255, 0.5), 0 0 8px rgba(166, 124, 82, 0.7);
        }
        
        .auth-subtitle {
            color: var(--text-secondary);
            font-size: 1.1em;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 1em;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-primary);
            border-radius: 10px;
            font-size: 1em;
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-family: inherit;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--accent-secondary);
            background: var(--bg-tertiary);
            box-shadow: 0 0 15px rgba(166, 124, 82, 0.3);
        }
        
        .form-button {
            width: 100%;
            padding: 14px 20px;
            background: var(--accent-primary);
            color: var(--bg-secondary);
            border: 2px solid var(--accent-secondary);
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }
        
        .form-button:hover {
            background: var(--accent-secondary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--shadow-secondary);
        }
        
        .error-message {
            background: rgba(183, 28, 28, 0.1);
            border: 1px solid var(--accent-danger);
            color: var(--accent-danger);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95em;
        }
        
        .success-message {
            background: rgba(43, 176, 123, 0.1);
            border: 1px solid var(--accent-success);
            color: var(--accent-success);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95em;
        }
        
        .admin-info {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-primary);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 0.9em;
            color: var(--text-secondary);
        }
        
        .admin-info strong {
            color: var(--text-primary);
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 10px;
            }
            
            .auth-card {
                padding: 30px 20px;
            }
            
            .auth-title {
                font-size: 1.8em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">DnD Copilot</h1>
                <p class="auth-subtitle">Вход в систему</p>
            </div>
            
            <div class="admin-info">
                <strong>Данные для входа:</strong><br>
                <strong>Логин:</strong> admin<br>
                <strong>Пароль:</strong> TimOkdndAdm1n
            </div>
            
            <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="action" value="login">
                
                <div class="form-group">
                    <label class="form-label" for="username">Имя пользователя</label>
                    <input type="text" id="username" name="username" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Пароль</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                </div>
                
                <button type="submit" class="form-button">
                    Войти
                </button>
            </form>
        </div>
    </div>
</body>
</html>
