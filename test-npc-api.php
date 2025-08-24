<?php
session_start();
require_once 'users.php';
require_once 'api/format-npc.php';

// Проверяем авторизацию
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест генерации NPC - DnD Copilot</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=UnifrakturCook:wght@700&family=IM+Fell+English+SC&display=swap" rel="stylesheet">
    <style>
        :root {
            --block-gap: 10px;
            --block-padding: 8px 12px;
            --bg-primary: #f8ecd0;
            --bg-secondary: #fffbe6;
            --bg-tertiary: #f3e1b6;
            --bg-quaternary: #e7d3a8;
            --bg-quinary: #ffe7b3;
            --bg-senary: #f8ecd0;
            --text-primary: #2d1b00;
            --text-secondary: #3d2a0a;
            --text-tertiary: #7c4a02;
            --text-quaternary: #4e260e;
            --text-quinary: #1b4e2d;
            --text-senary: #7a6c4a;
            --border-primary: #a67c52;
            --border-secondary: #7c4a02;
            --border-tertiary: #e6d3a8;
            --accent-primary: #a67c52;
            --accent-secondary: #7c4a02;
            --accent-success: #2bb07b;
            --accent-info: #4a90e2;
            --accent-warning: #ffd700;
            --accent-danger: #b71c1c;
            --shadow-primary: #0002;
            --shadow-secondary: #0006;
            --shadow-tertiary: #0001;
            --bg-image: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1500&q=80');
        }
        
        [data-theme="dark"] {
            --bg-primary: #0f0f23;
            --bg-secondary: #1a1a2e;
            --bg-tertiary: #16213e;
            --bg-quaternary: #0f3460;
            --bg-quinary: #533483;
            --bg-senary: #7209b7;
            --text-primary: #e8e8e8;
            --text-secondary: #d1d1d1;
            --text-tertiary: #b8a9c9;
            --text-quaternary: #c7b3d3;
            --text-quinary: #a8d5ba;
            --text-senary: #b8c5d1;
            --border-primary: #7209b7;
            --border-secondary: #560bad;
            --border-tertiary: #480ca8;
            --accent-primary: #8b5cf6;
            --accent-secondary: #7c3aed;
            --accent-success: #10b981;
            --accent-info: #3b82f6;
            --accent-warning: #f59e0b;
            --accent-danger: #ef4444;
            --shadow-primary: #0004;
            --shadow-secondary: #0008;
            --shadow-tertiary: #0002;
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
        
        .parchment {
            background: var(--bg-primary) url('https://www.transparenttextures.com/patterns/old-mathematics.png');
            border: 8px solid var(--border-primary);
            border-radius: 24px;
            box-shadow: 0 8px 32px var(--shadow-secondary), 0 0 0 12px rgba(210, 180, 140, 0.3);
            max-width: 900px;
            margin: 60px auto 36px auto;
            padding: 32px 24px 18px 24px;
            position: relative;
            transition: all 0.3s ease;
        }
        
        h1 {
            margin-top: 50px;
            margin-bottom: 20px;
            text-align: center;
            font-family: 'UnifrakturCook', cursive;
            font-size: 2.5em;
            color: var(--text-primary);
            text-shadow: 2px 2px 4px var(--shadow-primary);
            letter-spacing: 2px;
        }
        
        .back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            background: var(--accent-primary);
            color: #ffffff;
            border: 2px solid var(--accent-secondary);
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 0.9em;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-weight: 600;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
        }
        
        .back-link:hover {
            background: var(--accent-secondary);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px var(--shadow-secondary);
        }
        
        .user-info {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 12px;
            border-radius: 8px;
            backdrop-filter: blur(5px);
        }
        
        .welcome-text {
            color: var(--text-tertiary);
            font-weight: bold;
            font-size: 0.95em;
        }
        
        .logout-btn {
            background: var(--accent-danger);
            color: #ffffff;
            border: 2px solid var(--accent-danger);
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 0.85em;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .logout-btn:hover {
            background: var(--bg-secondary);
            color: var(--accent-danger);
            transform: translateY(-1px);
        }
        
        .theme-toggle {
            position: absolute;
            top: 20px;
            right: 80px;
        }
        
        .theme-btn {
            background: var(--accent-primary);
            color: var(--bg-secondary);
            border: 2px solid var(--accent-secondary);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            font-size: 1.2em;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .theme-btn:hover {
            background: var(--accent-secondary);
            transform: scale(1.1);
        }
        
        .info-section {
            background: var(--bg-secondary);
            border: 2px solid var(--border-primary);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px var(--shadow-secondary);
        }
        
        .info-section h2 {
            color: var(--text-tertiary);
            margin-top: 0;
            font-size: 1.4em;
        }
        
        .info-section p {
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        .api-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .api-status.available {
            background: var(--accent-success);
            color: var(--bg-secondary);
        }
        
        .api-status.unavailable {
            background: var(--accent-danger);
            color: var(--bg-secondary);
        }
        
        .api-status.testing {
            background: var(--accent-warning);
            color: var(--text-primary);
        }
        
        @media (max-width: 768px) {
            .parchment {
                margin: 20px 10px;
                padding: 20px 15px;
            }
            
            h1 {
                font-size: 1.8em;
                margin-top: 30px;
            }
            
            .back-link {
                position: relative;
                top: auto;
                left: auto;
                margin-bottom: 20px;
                display: inline-block;
            }
            
            .user-info {
                position: relative;
                top: auto;
                right: auto;
                margin-bottom: 20px;
                justify-content: center;
            }
            
            .theme-toggle {
                position: relative;
                top: auto;
                right: auto;
                margin-bottom: 20px;
                text-align: center;
            }
        }
    </style>
    <?php echo getNpcGenerationStyles(); ?>
</head>
<body>
    <div class="parchment">
        <a href="index.php" class="back-link">← Назад</a>
        
        <div class="theme-toggle">
            <button id="theme-toggle" class="theme-btn" title="Переключить тему">
                <span class="theme-icon">🌙</span>
            </button>
        </div>
        
        <div class="user-info">
            <span class="welcome-text">Добро пожаловать, <?php echo htmlspecialchars($currentUser); ?>!</span>
            <button class="logout-btn" onclick="logout()">Выйти</button>
        </div>
        
        <h1>Тест генерации NPC</h1>
        
        <div class="info-section">
            <h2>Новая система генерации NPC <span class="api-status testing">Тестирование</span></h2>
            <p>Эта страница демонстрирует новую систему генерации NPC с использованием внешних D&D API. Система решает проблемы с нестабильной генерацией через AI, используя структурированные данные от официальных D&D API.</p>
            
            <h3>Преимущества новой системы:</h3>
            <ul>
                <li><strong>Надёжность:</strong> Использует официальные данные D&D 5e API</li>
                <li><strong>Структурированность:</strong> Всегда возвращает правильно форматированные данные</li>
                <li><strong>Быстрота:</strong> Не зависит от внешних AI сервисов</li>
                <li><strong>Точность:</strong> Соответствует правилам D&D 5e</li>
                <li><strong>Гибкость:</strong> Поддержка всех рас, классов и мировоззрений</li>
            </ul>
            
            <h3>Поддерживаемые API:</h3>
            <ul>
                <li><strong>D&D 5e API</strong> - основная база данных (dnd5eapi.co)</li>
                <li><strong>Open5e API</strong> - альтернативный источник данных</li>
                <li><strong>Custom NPC Generator</strong> - для расширенной генерации</li>
            </ul>
        </div>
        
        <?php echo createNpcGenerationForm(); ?>
    </div>
    
    <?php echo getNpcGenerationScript(); ?>
    
    <script>
        // Переключатель темы
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('theme-toggle');
            const themeIcon = themeToggle.querySelector('.theme-icon');
            const body = document.body;
            
            // Загружаем сохраненную тему
            const savedTheme = localStorage.getItem('theme') || 'light';
            body.setAttribute('data-theme', savedTheme);
            updateThemeIcon(savedTheme);
            
            themeToggle.addEventListener('click', function() {
                const currentTheme = body.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                body.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateThemeIcon(newTheme);
            });
            
            function updateThemeIcon(theme) {
                themeIcon.textContent = theme === 'dark' ? '☀️' : '🌙';
            }
        });
        
        // Функция выхода
        function logout() {
            if (confirm('Вы уверены, что хотите выйти?')) {
                fetch('users.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=logout'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'login.php';
                    }
                })
                .catch(error => {
                    console.error('Ошибка выхода:', error);
                    window.location.href = 'login.php';
                });
            }
        }
        
        // Функция для сворачивания технических параметров
        function toggleTechnicalParams(header) {
            const content = header.nextElementSibling;
            const icon = header.querySelector('.toggle-icon');
            
            if (content.classList.contains('collapsed')) {
                content.classList.remove('collapsed');
                header.classList.remove('collapsed');
                icon.textContent = '▲';
            } else {
                content.classList.add('collapsed');
                header.classList.add('collapsed');
                icon.textContent = '▼';
            }
        }
    </script>
</body>
</html>
