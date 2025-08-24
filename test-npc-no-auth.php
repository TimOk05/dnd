<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'api/dnd-api.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест генерации NPC без авторизации</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test-form { margin: 20px 0; padding: 20px; border: 1px solid #ccc; }
        .result { margin: 20px 0; padding: 20px; background: #f5f5f5; }
        .success { color: green; }
        .error { color: red; }
        .npc-block { border: 1px solid #ddd; margin: 10px 0; padding: 15px; background: white; }
        .npc-header { font-size: 1.2em; font-weight: bold; margin-bottom: 10px; }
        .npc-param { margin: 5px 0; }
    </style>
</head>
<body>
    <h1>🧪 Тест генерации NPC без авторизации</h1>
    
    <div class="test-form">
        <h2>Прямой тест DndApiManager</h2>
        <form method="POST">
            <p>
                <label>Раса:</label>
                <select name="race">
                    <option value="human">Человек</option>
                    <option value="elf">Эльф</option>
                    <option value="dwarf">Дварф</option>
                    <option value="halfling">Полурослик</option>
                    <option value="orc">Орк</option>
                </select>
            </p>
            <p>
                <label>Класс:</label>
                <select name="class">
                    <option value="fighter">Воин</option>
                    <option value="wizard">Волшебник</option>
                    <option value="rogue">Плут</option>
                    <option value="cleric">Жрец</option>
                    <option value="ranger">Следопыт</option>
                </select>
            </p>
            <p>
                <label>Уровень:</label>
                <input type="number" name="level" value="1" min="1" max="20">
            </p>
            <p>
                <label>Мировоззрение:</label>
                <select name="alignment">
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
            </p>
            <button type="submit">Тестировать генерацию NPC</button>
        </form>
    </div>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo '<div class="result">';
        echo '<h3>Результат теста:</h3>';
        
        try {
            // Инициализируем менеджер D&D API
            $dndApi = new DndApiManager();
            
            // Получаем параметры
            $params = [
                'race' => $_POST['race'] ?? 'human',
                'class' => $_POST['class'] ?? 'fighter',
                'level' => (int)($_POST['level'] ?? 1),
                'alignment' => $_POST['alignment'] ?? 'neutral',
                'background' => 'soldier'
            ];
            
            echo '<p><strong>Параметры:</strong></p>';
            echo '<pre>' . htmlspecialchars(json_encode($params, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
            
            // Генерируем NPC
            $npcData = $dndApi->generateNPC($params);
            
            if ($npcData) {
                echo '<p class="success">✅ Генерация успешна!</p>';
                echo '<div class="npc-block">';
                echo '<div class="npc-header">' . htmlspecialchars($npcData['name']) . '</div>';
                
                if (isset($npcData['description'])) {
                    echo '<div class="npc-param"><strong>Описание:</strong> ' . htmlspecialchars($npcData['description']) . '</div>';
                }
                
                if (isset($npcData['appearance'])) {
                    echo '<div class="npc-param"><strong>Внешность:</strong> ' . htmlspecialchars($npcData['appearance']) . '</div>';
                }
                
                if (isset($npcData['traits'])) {
                    echo '<div class="npc-param"><strong>Черты характера:</strong> ' . htmlspecialchars($npcData['traits']) . '</div>';
                }
                
                if (isset($npcData['technical_params']) && is_array($npcData['technical_params'])) {
                    echo '<div class="npc-param"><strong>Технические параметры:</strong></div>';
                    echo '<ul>';
                    foreach ($npcData['technical_params'] as $param) {
                        echo '<li>' . htmlspecialchars($param) . '</li>';
                    }
                    echo '</ul>';
                }
                
                echo '</div>';
                
                echo '<h4>Сырые данные:</h4>';
                echo '<pre>' . htmlspecialchars(json_encode($npcData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
            } else {
                echo '<p class="error">❌ Ошибка: Не удалось сгенерировать NPC</p>';
            }
            
        } catch (Exception $e) {
            echo '<p class="error">❌ Ошибка: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p><strong>Стек вызовов:</strong></p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        }
        
        echo '</div>';
    }
    ?>
    
    <div class="test-form">
        <h2>Тест API endpoint</h2>
        <button onclick="testAPIEndpoint()">Тест API endpoint</button>
        <div id="apiResult"></div>
    </div>

    <script>
    async function testAPIEndpoint() {
        const resultDiv = document.getElementById('apiResult');
        resultDiv.innerHTML = 'Тестирование...';
        
        try {
            const formData = new FormData();
            formData.append('race', 'human');
            formData.append('class', 'fighter');
            formData.append('level', '1');
            formData.append('alignment', 'neutral');
            formData.append('background', 'soldier');
            
            const response = await fetch('api/generate-npc.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                resultDiv.innerHTML = '<p class="success">✅ API endpoint работает!</p><pre>' + JSON.stringify(data, null, 2) + '</pre>';
            } else {
                resultDiv.innerHTML = '<p class="error">❌ Ошибка API: ' + (data.error || 'Неизвестная ошибка') + '</p>';
            }
        } catch (error) {
            resultDiv.innerHTML = '<p class="error">❌ Ошибка сети: ' + error.message + '</p>';
        }
    }
    </script>
</body>
</html>
