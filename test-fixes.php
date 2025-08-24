<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест исправлений генераторов</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #1a1a1a; color: #fff; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #333; border-radius: 8px; }
        .success { color: #4ade80; }
        .error { color: #f87171; }
        .result { margin: 10px 0; padding: 10px; background: #2a2a2a; border-radius: 4px; }
        button { padding: 10px 20px; background: #6366f1; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #4f46e5; }
    </style>
</head>
<body>
    <h1>🧪 Тест исправлений генераторов</h1>
    
    <div class="test-section">
        <h2>🎭 Тест генерации персонажей</h2>
        <button onclick="testCharacterGenerator()">Тест генерации персонажа</button>
        <div id="characterTestResult" class="result"></div>
    </div>
    
    <div class="test-section">
        <h2>👹 Тест генерации противников</h2>
        <button onclick="testEnemyGenerator()">Тест генерации противника</button>
        <div id="enemyTestResult" class="result"></div>
    </div>
    
    <div class="test-section">
        <h2>🔍 Тест фильтрации типов</h2>
        <button onclick="testTypeFiltering()">Тест фильтрации по типу "beast"</button>
        <div id="typeTestResult" class="result"></div>
    </div>

    <script>
    async function testCharacterGenerator() {
        const resultDiv = document.getElementById('characterTestResult');
        resultDiv.innerHTML = 'Тестирование генерации персонажа...';
        
        try {
            const formData = new FormData();
            formData.append('race', 'elf');
            formData.append('class', 'wizard');
            formData.append('level', '3');
            formData.append('alignment', 'neutral-good');
            formData.append('use_ai', 'on');
            
            const response = await fetch('api/generate-characters.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                resultDiv.innerHTML = '<p class="success">✅ Генерация персонажа работает!</p>' +
                    '<p><strong>Персонаж:</strong> ' + data.npc.name + ', ' + data.npc.race + ' ' + data.npc.class + ' ' + data.npc.level + ' уровня</p>' +
                    '<p><strong>Хиты:</strong> ' + data.npc.hit_points + '</p>' +
                    '<p><strong>КД:</strong> ' + data.npc.armor_class + '</p>';
            } else {
                resultDiv.innerHTML = '<p class="error">❌ Ошибка: ' + (data.error || 'Неизвестная ошибка') + '</p>';
            }
        } catch (error) {
            resultDiv.innerHTML = '<p class="error">❌ Ошибка сети: ' + error.message + '</p>';
        }
    }
    
    async function testEnemyGenerator() {
        const resultDiv = document.getElementById('enemyTestResult');
        resultDiv.innerHTML = 'Тестирование генерации противника...';
        
        try {
            const formData = new FormData();
            formData.append('threat_level', 'medium');
            formData.append('count', '1');
            formData.append('enemy_type', '');
            formData.append('environment', '');
            formData.append('use_ai', 'on');
            
            const response = await fetch('api/generate-enemies.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                const enemy = data.enemies[0];
                resultDiv.innerHTML = '<p class="success">✅ Генерация противника работает!</p>' +
                    '<p><strong>Противник:</strong> ' + enemy.name + '</p>' +
                    '<p><strong>CR:</strong> ' + enemy.challenge_rating + '</p>' +
                    '<p><strong>Тип:</strong> ' + enemy.type + '</p>' +
                    '<p><strong>Размер:</strong> ' + enemy.size + '</p>';
            } else {
                resultDiv.innerHTML = '<p class="error">❌ Ошибка: ' + (data.error || 'Неизвестная ошибка') + '</p>';
            }
        } catch (error) {
            resultDiv.innerHTML = '<p class="error">❌ Ошибка сети: ' + error.message + '</p>';
        }
    }
    
    async function testTypeFiltering() {
        const resultDiv = document.getElementById('typeTestResult');
        resultDiv.innerHTML = 'Тестирование фильтрации по типу "beast"...';
        
        try {
            const formData = new FormData();
            formData.append('threat_level', 'easy');
            formData.append('count', '3');
            formData.append('enemy_type', 'beast');
            formData.append('environment', '');
            formData.append('use_ai', 'on');
            
            const response = await fetch('api/generate-enemies.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                let result = '<p class="success">✅ Фильтрация по типу работает!</p>';
                result += '<p><strong>Сгенерировано зверей:</strong> ' + data.enemies.length + '</p>';
                
                data.enemies.forEach((enemy, index) => {
                    result += '<p><strong>' + (index + 1) + '.</strong> ' + enemy.name + ' (тип: ' + enemy.type + ', CR: ' + enemy.challenge_rating + ')</p>';
                });
                
                resultDiv.innerHTML = result;
            } else {
                resultDiv.innerHTML = '<p class="error">❌ Ошибка: ' + (data.error || 'Неизвестная ошибка') + '</p>';
            }
        } catch (error) {
            resultDiv.innerHTML = '<p class="error">❌ Ошибка сети: ' + error.message + '</p>';
        }
    }
    </script>
</body>
</html>
