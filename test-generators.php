<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест генераторов - DnD Copilot</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .test-section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .test-section h2 {
            color: #333;
            border-bottom: 2px solid #007cba;
            padding-bottom: 10px;
        }
        
        .test-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .test-btn {
            padding: 10px 20px;
            background: #007cba;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .test-btn:hover {
            background: #005a87;
        }
        
        .result-area {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            min-height: 100px;
            white-space: pre-wrap;
            font-family: monospace;
            font-size: 12px;
        }
        
        .success {
            color: #28a745;
        }
        
        .error {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <h1>🧪 Тест генераторов DnD Copilot</h1>
    
    <div class="test-section">
        <h2>⚔️ Тест генератора персонажей</h2>
        <div class="test-buttons">
            <button class="test-btn" onclick="testCharacterGenerator()">Тест генерации персонажа</button>
            <button class="test-btn" onclick="testCharacterAPI()">Тест API персонажей</button>
        </div>
        <div id="character-result" class="result-area">Результат появится здесь...</div>
    </div>
    
    <div class="test-section">
        <h2>👹 Тест генератора противников</h2>
        <div class="test-buttons">
            <button class="test-btn" onclick="testEnemyGenerator()">Тест генерации противников</button>
            <button class="test-btn" onclick="testEnemyAPI()">Тест API противников</button>
        </div>
        <div id="enemy-result" class="result-area">Результат появится здесь...</div>
    </div>
    
    <div class="test-section">
        <h2>🎲 Тест бросков костей</h2>
        <div class="test-buttons">
            <button class="test-btn" onclick="testDiceRoll()">Тест броска d20</button>
            <button class="test-btn" onclick="testMultipleDice()">Тест множественных бросков</button>
        </div>
        <div id="dice-result" class="result-area">Результат появится здесь...</div>
    </div>

    <script>
        // Тест генератора персонажей
        async function testCharacterGenerator() {
            const resultDiv = document.getElementById('character-result');
            resultDiv.textContent = 'Тестирование генератора персонажей...';
            
            try {
                const formData = new FormData();
                formData.append('race', 'human');
                formData.append('class', 'fighter');
                formData.append('level', '5');
                formData.append('alignment', 'lawful good');
                // AI теперь всегда включен по умолчанию
                
                const response = await fetch('api/generate-hybrid-npc.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.innerHTML = '<span class="success">✅ Успешно!</span>\n\n' + 
                                        JSON.stringify(data.npc, null, 2);
                } else {
                    resultDiv.innerHTML = '<span class="error">❌ Ошибка:</span>\n\n' + 
                                        (data.error || 'Неизвестная ошибка');
                }
            } catch (error) {
                resultDiv.innerHTML = '<span class="error">❌ Ошибка сети:</span>\n\n' + error.message;
            }
        }
        
        // Тест API персонажей
        async function testCharacterAPI() {
            const resultDiv = document.getElementById('character-result');
            resultDiv.textContent = 'Тестирование API персонажей...';
            
            try {
                const response = await fetch('api/dnd-api-working.php');
                const data = await response.text();
                
                resultDiv.innerHTML = '<span class="success">✅ API доступен</span>\n\n' + 
                                    'Размер ответа: ' + data.length + ' символов';
            } catch (error) {
                resultDiv.innerHTML = '<span class="error">❌ Ошибка API:</span>\n\n' + error.message;
            }
        }
        
        // Тест генератора противников
        async function testEnemyGenerator() {
            const resultDiv = document.getElementById('enemy-result');
            resultDiv.textContent = 'Тестирование генератора противников...';
            
            try {
                const formData = new FormData();
                formData.append('threat_level', 'medium');
                formData.append('count', '2');
                formData.append('enemy_type', 'humanoid');
                formData.append('environment', 'forest');
                // AI теперь всегда включен по умолчанию
                
                const response = await fetch('api/generate-enemies.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.innerHTML = '<span class="success">✅ Успешно!</span>\n\n' + 
                                        JSON.stringify(data.enemies, null, 2);
                } else {
                    resultDiv.innerHTML = '<span class="error">❌ Ошибка:</span>\n\n' + 
                                        (data.error || 'Неизвестная ошибка');
                }
            } catch (error) {
                resultDiv.innerHTML = '<span class="error">❌ Ошибка сети:</span>\n\n' + error.message;
            }
        }
        
        // Тест API противников
        async function testEnemyAPI() {
            const resultDiv = document.getElementById('enemy-result');
            resultDiv.textContent = 'Тестирование API противников...';
            
            try {
                const response = await fetch('https://www.dnd5eapi.co/api/monsters');
                const data = await response.json();
                
                resultDiv.innerHTML = '<span class="success">✅ D&D API доступен</span>\n\n' + 
                                    'Найдено монстров: ' + (data.count || 0) + '\n' +
                                    'Примеры: ' + data.results.slice(0, 5).map(m => m.name).join(', ');
            } catch (error) {
                resultDiv.innerHTML = '<span class="error">❌ Ошибка D&D API:</span>\n\n' + error.message;
            }
        }
        
        // Тест бросков костей
        async function testDiceRoll() {
            const resultDiv = document.getElementById('dice-result');
            resultDiv.textContent = 'Тестирование броска костей...';
            
            try {
                const formData = new FormData();
                formData.append('fast_action', 'dice_result');
                formData.append('dice', '1d20');
                formData.append('label', 'Тестовый бросок');
                
                const response = await fetch('index.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.text();
                
                resultDiv.innerHTML = '<span class="success">✅ Бросок выполнен</span>\n\n' + data;
            } catch (error) {
                resultDiv.innerHTML = '<span class="error">❌ Ошибка броска:</span>\n\n' + error.message;
            }
        }
        
        // Тест множественных бросков
        async function testMultipleDice() {
            const resultDiv = document.getElementById('dice-result');
            resultDiv.textContent = 'Тестирование множественных бросков...';
            
            try {
                const formData = new FormData();
                formData.append('fast_action', 'dice_result');
                formData.append('dice', '4d6');
                formData.append('label', 'Характеристики');
                
                const response = await fetch('index.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.text();
                
                resultDiv.innerHTML = '<span class="success">✅ Множественные броски выполнены</span>\n\n' + data;
            } catch (error) {
                resultDiv.innerHTML = '<span class="error">❌ Ошибка бросков:</span>\n\n' + error.message;
            }
        }
    </script>
</body>
</html>
