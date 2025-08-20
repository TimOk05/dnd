<?php
require_once 'config.php';

// Включаем отображение ошибок для диагностики
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добро пожаловать - DnD Copilot</title>
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
        
        .welcome-card {
            background: var(--bg-primary) url('https://www.transparenttextures.com/patterns/old-mathematics.png');
            border: 8px solid var(--border-primary);
            border-radius: 24px;
            box-shadow: 0 8px 32px var(--shadow-secondary), 0 0 0 12px rgba(210, 180, 140, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px 30px;
            position: relative;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .welcome-card:before,
        .welcome-card:after {
            content: '';
            position: absolute;
            width: 54px;
            height: 54px;
            background: url('https://cdn-icons-png.flaticon.com/512/616/616494.png') no-repeat center/contain;
            opacity: 0.12;
        }
        
        .welcome-card:before {
            left: -30px;
            top: -30px;
        }
        
        .welcome-card:after {
            right: -30px;
            bottom: -30px;
            transform: scaleX(-1);
        }
        
        .welcome-title {
            font-family: 'UnifrakturCook', cursive;
            font-size: 2.5em;
            color: var(--text-tertiary);
            margin-bottom: 20px;
            letter-spacing: 2px;
            text-shadow: 0 2px 0 rgba(255, 255, 255, 0.5), 0 0 8px rgba(166, 124, 82, 0.7);
        }
        
        .welcome-subtitle {
            color: var(--text-secondary);
            font-size: 1.2em;
            margin-bottom: 30px;
        }
        
        .welcome-description {
            color: var(--text-secondary);
            font-size: 1em;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .welcome-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .feature {
            background: var(--bg-secondary);
            border: 2px solid var(--border-primary);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        
        .feature-icon {
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .feature-title {
            font-weight: 600;
            color: var(--text-tertiary);
            margin-bottom: 8px;
        }
        
        .feature-description {
            font-size: 0.9em;
            color: var(--text-secondary);
        }
        
        .welcome-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .welcome-btn {
            padding: 14px 24px;
            background: var(--accent-primary);
            color: var(--bg-secondary);
            border: 2px solid var(--accent-secondary);
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .welcome-btn:hover {
            background: var(--accent-secondary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--shadow-secondary);
        }
        
        .welcome-btn.secondary {
            background: var(--bg-secondary);
            color: var(--accent-primary);
            border-color: var(--accent-primary);
        }
        
        .welcome-btn.secondary:hover {
            background: var(--accent-primary);
            color: var(--bg-secondary);
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 10px;
            }
            
            .welcome-card {
                padding: 30px 20px;
            }
            
            .welcome-title {
                font-size: 2em;
            }
            
            .welcome-features {
                grid-template-columns: 1fr;
            }
            
            .welcome-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="welcome-card">
            <h1 class="welcome-title">DnD Copilot</h1>
            <p class="welcome-subtitle">Ваш верный помощник в мире D&D</p>
            
            <p class="welcome-description">
                Полнофункциональное веб-приложение для мастера D&D с системой регистрации пользователей, 
                генерацией NPC, управлением инициативой и AI-чатом.
            </p>
            
            <div class="welcome-features">
                <div class="feature">
                    <div class="feature-icon">🗣️</div>
                    <div class="feature-title">Генерация NPC</div>
                    <div class="feature-description">Создание уникальных персонажей с выбором расы, класса и уровня</div>
                </div>
                
                <div class="feature">
                    <div class="feature-icon">⚡</div>
                    <div class="feature-title">Система инициативы</div>
                    <div class="feature-description">Управление боевыми ходами с красивым интерфейсом</div>
                </div>
                
                <div class="feature">
                    <div class="feature-icon">🎲</div>
                    <div class="feature-title">Бросок костей</div>
                    <div class="feature-description">Поддержка всех стандартных костей D&D</div>
                </div>
                
                <div class="feature">
                    <div class="feature-icon">🤖</div>
                    <div class="feature-title">AI Чат</div>
                    <div class="feature-description">Интеграция с DeepSeek API для генерации контента</div>
                </div>
            </div>
            
            <div class="welcome-actions">
                <a href="setup.php" class="welcome-btn">🚀 Начать установку</a>
                <a href="status.php" class="welcome-btn secondary">🔍 Проверить статус</a>
            </div>
        </div>
    </div>
</body>
</html>
