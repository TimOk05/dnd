<?php
session_start();
require_once 'users.php';

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
    <title>Генераторы - DnD Copilot</title>
    <link rel="icon" type="image/svg+xml" href="./favicon.svg">
    <meta name="theme-color" content="#a67c52">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="DnD Copilot">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=UnifrakturCook:wght@700&family=IM+Fell+English+SC&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #a67c52;
            --secondary-color: #f8ecd0;
            --accent-color: #7c4a02;
            --text-color: #2c1810;
            --background-color: #f5f5f5;
            --card-background: #ffffff;
            --success-color: #28a745;
            --error-color: #dc3545;
            --warning-color: #ffc107;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            background: var(--card-background);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-family: 'UnifrakturCook', cursive;
            font-size: 2.5em;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1em;
            color: var(--accent-color);
        }

        .generators-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .generator-card {
            background: var(--card-background);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .generator-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }

        .generator-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .generator-icon {
            font-size: 2em;
            margin-right: 15px;
        }

        .generator-title {
            font-size: 1.5em;
            font-weight: bold;
            color: var(--primary-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--accent-color);
        }

        select, input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        select:focus, input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .generate-btn {
            width: 100%;
            padding: 15px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .generate-btn:hover {
            background: var(--accent-color);
        }

        .generate-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .results {
            margin-top: 30px;
        }

        .result-card {
            background: var(--card-background);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-left: 5px solid var(--primary-color);
        }

        .result-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 15px;
        }

        .result-title {
            font-size: 1.3em;
            font-weight: bold;
            color: var(--primary-color);
        }

        .result-type {
            background: var(--secondary-color);
            color: var(--accent-color);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }

        .result-content {
            line-height: 1.8;
        }

        .result-section {
            margin-bottom: 15px;
        }

        .result-section h4 {
            color: var(--accent-color);
            margin-bottom: 8px;
            font-size: 1.1em;
        }

        .collapsible {
            cursor: pointer;
            padding: 10px;
            background: var(--secondary-color);
            border-radius: 8px;
            margin-bottom: 10px;
            transition: background-color 0.3s ease;
        }

        .collapsible:hover {
            background: #e8d5b5;
        }

        .collapsible-content {
            display: none;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 10px;
        }

        .loading {
            text-align: center;
            padding: 30px;
            color: var(--accent-color);
        }

        .loading::after {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid var(--primary-color);
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
            margin-left: 10px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .error {
            background: #f8d7da;
            color: var(--error-color);
            padding: 15px;
            border-radius: 8px;
            border-left: 5px solid var(--error-color);
        }

        .success {
            background: #d4edda;
            color: var(--success-color);
            padding: 15px;
            border-radius: 8px;
            border-left: 5px solid var(--success-color);
        }

        .back-btn {
            display: inline-block;
            padding: 12px 24px;
            background: var(--accent-color);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 20px;
            transition: background-color 0.3s ease;
        }

        .back-btn:hover {
            background: #5a3a01;
        }

        @media (max-width: 768px) {
            .generators-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 10px;
            }
            
            .header h1 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">← Назад к главной</a>
        
        <div class="header">
            <h1>🎲 Генераторы DnD</h1>
            <p>Создавайте персонажей и противников для ваших приключений</p>
        </div>

        <div class="generators-grid">
            <!-- Генератор NPC -->
            <div class="generator-card">
                <div class="generator-header">
                    <div class="generator-icon">🗣️</div>
                    <div class="generator-title">Генератор NPC</div>
                </div>
                
                <form id="npcForm">
                    <div class="form-group">
                        <label for="npcRace">Раса:</label>
                        <select id="npcRace" name="race" required>
                            <option value="human">Человек</option>
                            <option value="elf">Эльф</option>
                            <option value="dwarf">Дварф</option>
                            <option value="halfling">Полурослик</option>
                            <option value="orc">Орк</option>
                            <option value="tiefling">Тифлинг</option>
                            <option value="dragonborn">Драконорожденный</option>
                            <option value="gnome">Гном</option>
                            <option value="half-elf">Полуэльф</option>
                            <option value="half-orc">Полуорк</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="npcClass">Класс:</label>
                        <select id="npcClass" name="class" required>
                            <option value="fighter">Воин</option>
                            <option value="wizard">Волшебник</option>
                            <option value="rogue">Плут</option>
                            <option value="cleric">Жрец</option>
                            <option value="ranger">Следопыт</option>
                            <option value="barbarian">Варвар</option>
                            <option value="bard">Бард</option>
                            <option value="druid">Друид</option>
                            <option value="monk">Монах</option>
                            <option value="paladin">Паладин</option>
                            <option value="sorcerer">Сорсерер</option>
                            <option value="warlock">Колдун</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="npcLevel">Уровень:</label>
                        <input type="number" id="npcLevel" name="level" value="1" min="1" max="20" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="npcAlignment">Мировоззрение:</label>
                        <select id="npcAlignment" name="alignment" required>
                            <option value="lawful good">Законно-добрый</option>
                            <option value="neutral good">Нейтрально-добрый</option>
                            <option value="chaotic good">Хаотично-добрый</option>
                            <option value="lawful neutral">Законно-нейтральный</option>
                            <option value="neutral">Нейтральный</option>
                            <option value="chaotic neutral">Хаотично-нейтральный</option>
                            <option value="lawful evil">Законно-злой</option>
                            <option value="neutral evil">Нейтрально-злой</option>
                            <option value="chaotic evil">Хаотично-злой</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="generate-btn">🎯 Сгенерировать NPC</button>
                </form>
            </div>

            <!-- Генератор противников -->
            <div class="generator-card">
                <div class="generator-header">
                    <div class="generator-icon">⚔️</div>
                    <div class="generator-title">Генератор противников</div>
                </div>
                
                <form id="enemyForm">
                    <div class="form-group">
                        <label for="enemyThreat">Уровень угрозы:</label>
                        <select id="enemyThreat" name="threat_level" required>
                            <option value="easy">Легкий</option>
                            <option value="medium" selected>Средний</option>
                            <option value="hard">Сложный</option>
                            <option value="deadly">Смертельный</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="enemyCount">Количество:</label>
                        <input type="number" id="enemyCount" name="count" value="1" min="1" max="10" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="enemyType">Тип противника:</label>
                        <select id="enemyType" name="enemy_type">
                            <option value="">Любой</option>
                            <option value="humanoid">Гуманоид</option>
                            <option value="beast">Зверь</option>
                            <option value="dragon">Дракон</option>
                            <option value="undead">Нежить</option>
                            <option value="fiend">Демон</option>
                            <option value="celestial">Небожитель</option>
                            <option value="construct">Конструкт</option>
                            <option value="elemental">Элементаль</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="enemyEnvironment">Окружение:</label>
                        <select id="enemyEnvironment" name="environment">
                            <option value="">Любое</option>
                            <option value="forest">Лес</option>
                            <option value="mountain">Горы</option>
                            <option value="desert">Пустыня</option>
                            <option value="swamp">Болото</option>
                            <option value="underdark">Подземелье</option>
                            <option value="urban">Город</option>
                            <option value="coastal">Побережье</option>
                            <option value="arctic">Арктика</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="generate-btn">⚔️ Сгенерировать противников</button>
                </form>
            </div>
        </div>

        <div id="results" class="results"></div>
    </div>

    <script>
    // Генерация NPC
    document.getElementById('npcForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const resultsDiv = document.getElementById('results');
        resultsDiv.innerHTML = '<div class="loading">🔄 Генерация NPC...</div>';
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('api/generate-npc.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success && data.npc) {
                const npc = data.npc;
                resultsDiv.innerHTML = `
                    <div class="result-card">
                        <div class="result-header">
                            <div class="result-title">${npc.name || 'Безымянный NPC'}</div>
                            <div class="result-type">NPC</div>
                        </div>
                        
                        <div class="result-content">
                            ${npc.description ? `
                                <div class="result-section">
                                    <h4>📜 Описание</h4>
                                    <p>${npc.description}</p>
                                </div>
                            ` : ''}
                            
                            ${npc.appearance ? `
                                <div class="result-section">
                                    <h4>👤 Внешность</h4>
                                    <p>${npc.appearance}</p>
                                </div>
                            ` : ''}
                            
                            ${npc.traits ? `
                                <div class="result-section">
                                    <h4>🎭 Черты характера</h4>
                                    <p>${npc.traits}</p>
                                </div>
                            ` : ''}
                            
                            ${npc.technical_params && npc.technical_params.length > 0 ? `
                                <div class="result-section">
                                    <div class="collapsible" onclick="toggleCollapsible(this)">
                                        <h4>⚔️ Технические параметры</h4>
                                    </div>
                                    <div class="collapsible-content">
                                        <ul>
                                            ${npc.technical_params.map(param => `<li>${param}</li>`).join('')}
                                        </ul>
                                    </div>
                                </div>
                            ` : ''}
                            
                            <div class="result-section">
                                <h4>📋 Параметры генерации</h4>
                                <p><strong>Раса:</strong> ${formData.get('race')} | <strong>Класс:</strong> ${formData.get('class')} | <strong>Уровень:</strong> ${formData.get('level')} | <strong>Мировоззрение:</strong> ${formData.get('alignment')}</p>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                resultsDiv.innerHTML = `
                    <div class="error">
                        <h3>❌ Ошибка генерации NPC</h3>
                        <p>${data.error || 'Неизвестная ошибка'}</p>
                    </div>
                `;
            }
        } catch (error) {
            resultsDiv.innerHTML = `
                <div class="error">
                    <h3>❌ Ошибка сети</h3>
                    <p>${error.message}</p>
                </div>
            `;
        }
    });

    // Генерация противников
    document.getElementById('enemyForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const resultsDiv = document.getElementById('results');
        resultsDiv.innerHTML = '<div class="loading">🔄 Генерация противников...</div>';
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('api/generate-enemies.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success && data.enemies) {
                let enemiesHtml = '';
                
                data.enemies.forEach((enemy, index) => {
                    enemiesHtml += `
                        <div class="result-card">
                            <div class="result-header">
                                <div class="result-title">${enemy.name}</div>
                                <div class="result-type">Противник ${index + 1}</div>
                            </div>
                            
                            <div class="result-content">
                                ${enemy.description ? `
                                    <div class="result-section">
                                        <h4>📜 Описание</h4>
                                        <p>${enemy.description}</p>
                                    </div>
                                ` : ''}
                                
                                ${enemy.tactics ? `
                                    <div class="result-section">
                                        <h4>🎯 Тактика</h4>
                                        <p>${enemy.tactics}</p>
                                    </div>
                                ` : ''}
                                
                                ${enemy.stats ? `
                                    <div class="result-section">
                                        <div class="collapsible" onclick="toggleCollapsible(this)">
                                            <h4>⚔️ Характеристики</h4>
                                        </div>
                                        <div class="collapsible-content">
                                            <pre>${enemy.stats}</pre>
                                        </div>
                                    </div>
                                ` : ''}
                                
                                <div class="result-section">
                                    <h4>📋 Параметры</h4>
                                    <p><strong>Уровень угрозы:</strong> ${data.threat_level} | <strong>Тип:</strong> ${enemy.type || 'Неизвестно'} | <strong>CR:</strong> ${enemy.challenge_rating || 'Неизвестно'}</p>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                resultsDiv.innerHTML = enemiesHtml;
            } else {
                resultsDiv.innerHTML = `
                    <div class="error">
                        <h3>❌ Ошибка генерации противников</h3>
                        <p>${data.error || 'Неизвестная ошибка'}</p>
                    </div>
                `;
            }
        } catch (error) {
            resultsDiv.innerHTML = `
                <div class="error">
                    <h3>❌ Ошибка сети</h3>
                    <p>${error.message}</p>
                </div>
            `;
        }
    });

    // Функция для сворачивания/разворачивания блоков
    function toggleCollapsible(element) {
        const content = element.nextElementSibling;
        if (content.style.display === 'block') {
            content.style.display = 'none';
        } else {
            content.style.display = 'block';
        }
    }
    </script>
</body>
</html>
