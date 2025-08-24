<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест генератора противников</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test-form { margin: 20px 0; padding: 20px; border: 1px solid #ccc; }
        .result { margin: 20px 0; padding: 20px; background: #f5f5f5; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>🧪 Тест генератора противников</h1>
    
    <div class="test-form">
        <h2>Тест API</h2>
        <form method="POST">
            <p>
                <label>Уровень угрозы:</label>
                <select name="threat_level">
                    <option value="easy">Легкий</option>
                    <option value="medium" selected>Средний</option>
                    <option value="hard">Сложный</option>
                    <option value="deadly">Смертельный</option>
                </select>
            </p>
            <p>
                <label>Количество:</label>
                <input type="number" name="count" value="2" min="1" max="5">
            </p>
            <p>
                <label>Тип:</label>
                <select name="enemy_type">
                    <option value="">Любой</option>
                    <option value="humanoid">Гуманоиды</option>
                    <option value="beast">Звери</option>
                    <option value="undead">Нежить</option>
                </select>
            </p>
            <p>
                <label>Среда:</label>
                <select name="environment">
                    <option value="">Любая</option>
                    <option value="forest">Лес</option>
                    <option value="underdark">Подземелье</option>
                </select>
            </p>
            <button type="submit">Тестировать</button>
        </form>
    </div>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo '<div class="result">';
        echo '<h3>Результат теста:</h3>';
        
        // Подготавливаем данные для отправки
        $postData = [
            'threat_level' => $_POST['threat_level'] ?? 'medium',
            'count' => $_POST['count'] ?? 1,
            'enemy_type' => $_POST['enemy_type'] ?? '',
            'environment' => $_POST['environment'] ?? '',
            'use_ai' => 'on'
        ];
        
        // Отправляем запрос к API
        $ch = curl_init('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/api/generate-enemies.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            echo '<p class="error">❌ Ошибка CURL: ' . htmlspecialchars($error) . '</p>';
        } elseif ($httpCode !== 200) {
            echo '<p class="error">❌ HTTP ошибка: ' . $httpCode . '</p>';
        } else {
            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo '<p class="error">❌ Ошибка JSON: ' . json_last_error_msg() . '</p>';
                echo '<pre>' . htmlspecialchars($response) . '</pre>';
            } else {
                if ($data['success']) {
                    echo '<p class="success">✅ Генерация успешна!</p>';
                    echo '<p>Создано противников: ' . count($data['enemies']) . '</p>';
                    echo '<h4>Противники:</h4>';
                    foreach ($data['enemies'] as $enemy) {
                        echo '<div style="border: 1px solid #ddd; margin: 10px 0; padding: 10px;">';
                        echo '<strong>' . htmlspecialchars($enemy['name']) . '</strong><br>';
                        echo 'CR: ' . htmlspecialchars($enemy['challenge_rating']) . '<br>';
                        echo 'Тип: ' . htmlspecialchars($enemy['type']) . '<br>';
                        echo 'Размер: ' . htmlspecialchars($enemy['size']) . '<br>';
                        if (isset($enemy['tactics'])) {
                            echo 'Тактика: ' . htmlspecialchars($enemy['tactics']) . '<br>';
                        }
                        echo '</div>';
                    }
                } else {
                    echo '<p class="error">❌ Ошибка: ' . htmlspecialchars($data['error'] ?? 'Неизвестная ошибка') . '</p>';
                }
            }
        }
        
        echo '</div>';
    }
    ?>
    
    <div class="test-form">
        <h2>Прямой тест API</h2>
        <button onclick="testDirectAPI()">Тест прямого вызова API</button>
        <div id="directResult"></div>
    </div>

    <script>
    async function testDirectAPI() {
        const resultDiv = document.getElementById('directResult');
        resultDiv.innerHTML = 'Тестирование...';
        
        try {
            const formData = new FormData();
            formData.append('threat_level', 'medium');
            formData.append('count', '1');
            formData.append('enemy_type', 'humanoid');
            formData.append('use_ai', 'on');
            
            const response = await fetch('api/generate-enemies.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                resultDiv.innerHTML = '<p class="success">✅ Прямой API работает!</p><pre>' + JSON.stringify(data, null, 2) + '</pre>';
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
