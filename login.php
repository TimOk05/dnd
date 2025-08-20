<?php
require_once 'config.php';
require_once 'auth.php';

session_start();

// Если пользователь уже авторизован, перенаправляем на главную
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Инициализация базы данных при первом запуске
if (!isset($_SESSION['db_initialized'])) {
    if (initDatabase()) {
        $_SESSION['db_initialized'] = true;
    } else {
        // Если база данных не инициализирована, показываем ошибку
        die('Ошибка: База данных не настроена. Проверьте настройки в config.php');
    }
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
        
        .form-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .auth-switch {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-primary);
        }
        
        .auth-switch a {
            color: var(--accent-primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .auth-switch a:hover {
            color: var(--accent-secondary);
            text-decoration: underline;
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
        
        .form-toggle {
            display: none;
        }
        
        .form-toggle:target {
            display: block;
        }
        
        .password-requirements {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-primary);
            border-radius: 8px;
            padding: 12px;
            margin-top: 10px;
            font-size: 0.9em;
            color: var(--text-secondary);
        }
        
        .password-requirements ul {
            margin: 5px 0 0 0;
            padding-left: 20px;
        }
        
        .password-requirements li {
            margin-bottom: 3px;
        }
        
        .loading {
            display: none;
            text-align: center;
            margin: 10px 0;
        }
        
        .loading::after {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid var(--border-primary);
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
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
            <!-- Форма входа -->
            <div id="login-form">
                <div class="auth-header">
                    <h1 class="auth-title">DnD Copilot</h1>
                    <p class="auth-subtitle">Вход в систему</p>
                </div>
                
                <div id="login-messages"></div>
                
                <form id="loginForm">
                    <div class="form-group">
                        <label class="form-label" for="login-username">Имя пользователя или Email</label>
                        <input type="text" id="login-username" name="username" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="login-password">Пароль</label>
                        <input type="password" id="login-password" name="password" class="form-input" required>
                    </div>
                    
                    <button type="submit" class="form-button" id="login-btn">
                        Войти
                    </button>
                </form>
                
                <div class="loading" id="login-loading"></div>
                
                <div class="auth-switch">
                    <p>Нет аккаунта? <a href="#register-form" onclick="showRegister()">Зарегистрироваться</a></p>
                </div>
            </div>
            
            <!-- Форма регистрации -->
            <div id="register-form" style="display: none;">
                <div class="auth-header">
                    <h1 class="auth-title">DnD Copilot</h1>
                    <p class="auth-subtitle">Регистрация</p>
                </div>
                
                <div id="register-messages"></div>
                
                <form id="registerForm">
                    <div class="form-group">
                        <label class="form-label" for="register-username">Имя пользователя</label>
                        <input type="text" id="register-username" name="username" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="register-email">Email</label>
                        <input type="email" id="register-email" name="email" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="register-password">Пароль</label>
                        <input type="password" id="register-password" name="password" class="form-input" required>
                        <div class="password-requirements">
                            <strong>Требования к паролю:</strong>
                            <ul>
                                <li>Минимум 8 символов</li>
                                <li>Хотя бы одна заглавная буква</li>
                                <li>Хотя бы одна строчная буква</li>
                                <li>Хотя бы одна цифра</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="register-confirm-password">Подтвердите пароль</label>
                        <input type="password" id="register-confirm-password" name="confirm_password" class="form-input" required>
                    </div>
                    
                    <button type="submit" class="form-button" id="register-btn">
                        Зарегистрироваться
                    </button>
                </form>
                
                <div class="loading" id="register-loading"></div>
                
                <div class="auth-switch">
                    <p>Уже есть аккаунт? <a href="#login-form" onclick="showLogin()">Войти</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function showMessage(containerId, message, type) {
            const container = document.getElementById(containerId);
            container.innerHTML = `<div class="${type}-message">${message}</div>`;
        }
        
        function clearMessage(containerId) {
            document.getElementById(containerId).innerHTML = '';
        }
        
        function showLoading(formId) {
            document.getElementById(formId + '-loading').style.display = 'block';
            document.getElementById(formId + '-btn').disabled = true;
        }
        
        function hideLoading(formId) {
            document.getElementById(formId + '-loading').style.display = 'none';
            document.getElementById(formId + '-btn').disabled = false;
        }
        
        function showRegister() {
            document.getElementById('login-form').style.display = 'none';
            document.getElementById('register-form').style.display = 'block';
            clearMessage('login-messages');
        }
        
        function showLogin() {
            document.getElementById('register-form').style.display = 'none';
            document.getElementById('login-form').style.display = 'block';
            clearMessage('register-messages');
        }
        
        // Обработка формы входа
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'login');
            
            showLoading('login');
            clearMessage('login-messages');
            
            fetch('auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideLoading('login');
                
                if (data.success) {
                    showMessage('login-messages', 'Вход выполнен успешно! Перенаправление...', 'success');
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1000);
                } else {
                    showMessage('login-messages', data.errors.join('<br>'), 'error');
                }
            })
            .catch(error => {
                hideLoading('login');
                showMessage('login-messages', 'Ошибка соединения. Попробуйте позже.', 'error');
            });
        });
        
        // Обработка формы регистрации
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'register');
            
            showLoading('register');
            clearMessage('register-messages');
            
            fetch('auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideLoading('register');
                
                if (data.success) {
                    showMessage('register-messages', data.message, 'success');
                    setTimeout(() => {
                        showLogin();
                    }, 2000);
                } else {
                    showMessage('register-messages', data.errors.join('<br>'), 'error');
                }
            })
            .catch(error => {
                hideLoading('register');
                showMessage('register-messages', 'Ошибка соединения. Попробуйте позже.', 'error');
            });
        });
    </script>
</body>
</html>
