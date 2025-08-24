<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Быстрый тест - DnD Copilot</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .result { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
        .test-btn { padding: 10px 20px; margin: 5px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>🚀 Быстрый тест всех компонентов</h1>
    
    <div class="result info">
        <h2>Проверка файлов:</h2>
        <?php
        $files = [
            'manifest.json' => __DIR__ . '/manifest.json',
            'favicon.svg' => __DIR__ . '/favicon.svg',
            'sw.js' => __DIR__ . '/sw.js',
            'api/dnd-api.php' => __DIR__ . '/api/dnd-api.php'
        ];
        
        foreach ($files as $name => $path) {
            $exists = file_exists($path);
            $readable = is_readable($path);
            $size = $exists ? filesize($path) : 0;
            echo "<p><strong>$name:</strong> " . 
                 ($exists ? "✅ Существует" : "❌ Отсутствует") . 
                 ($readable ? " ✅ Читается" : " ❌ Не читается") . 
                 ($size > 0 ? " ($size байт)" : "") . 
                 "</p>";
        }
        ?>
    </div>
    
    <div class="result info">
        <h2>Тесты:</h2>
        <button class="test-btn" onclick="testManifest()">Тест manifest.json</button>
        <button class="test-btn" onclick="testFavicon()">Тест favicon.svg</button>
        <button class="test-btn" onclick="testServiceWorker()">Тест Service Worker</button>
        <button class="test-btn" onclick="testNPCGeneration()">Тест генерации NPC</button>
        <div id="testResults"></div>
    </div>
    
    <div class="result info">
        <h2>Прямые ссылки:</h2>
        <p><a href="./manifest.json" target="_blank">manifest.json</a></p>
        <p><a href="./favicon.svg" target="_blank">favicon.svg</a></p>
        <p><a href="./sw.js" target="_blank">sw.js</a></p>
        <p><a href="./api/generate-npc-test.php" target="_blank">API тест</a></p>
    </div>
    
    <script>
    function testManifest() {
        fetch('./manifest.json')
            .then(response => {
                if (response.ok) {
                    return response.text();
                } else {
                    throw new Error('HTTP ' + response.status);
                }
            })
            .then(text => {
                try {
                    const json = JSON.parse(text);
                    showResult('✅ manifest.json работает', 'success');
                } catch (e) {
                    showResult('❌ manifest.json - ошибка JSON: ' + e.message, 'error');
                }
            })
            .catch(error => {
                showResult('❌ manifest.json - ошибка загрузки: ' + error.message, 'error');
            });
    }
    
    function testFavicon() {
        fetch('./favicon.svg')
            .then(response => {
                if (response.ok) {
                    showResult('✅ favicon.svg работает', 'success');
                } else {
                    throw new Error('HTTP ' + response.status);
                }
            })
            .catch(error => {
                showResult('❌ favicon.svg - ошибка: ' + error.message, 'error');
            });
    }
    
    function testServiceWorker() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('./sw.js')
                .then(registration => {
                    showResult('✅ Service Worker зарегистрирован', 'success');
                })
                .catch(error => {
                    showResult('❌ Service Worker - ошибка: ' + error.message, 'error');
                });
        } else {
            showResult('❌ Service Worker не поддерживается', 'error');
        }
    }
    
    function testNPCGeneration() {
        const formData = new FormData();
        formData.append('race', 'human');
        formData.append('class', 'fighter');
        formData.append('level', '1');
        formData.append('alignment', 'neutral');
        formData.append('background', 'soldier');
        
        fetch('api/generate-npc-test.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showResult('✅ Генерация NPC работает', 'success');
            } else {
                showResult('❌ Генерация NPC - ошибка: ' + (data.error || 'Неизвестная ошибка'), 'error');
            }
        })
        .catch(error => {
            showResult('❌ Генерация NPC - ошибка сети: ' + error.message, 'error');
        });
    }
    
    function showResult(message, type) {
        const resultsDiv = document.getElementById('testResults');
        const resultDiv = document.createElement('div');
        resultDiv.className = 'result ' + type;
        resultDiv.textContent = message;
        resultsDiv.appendChild(resultDiv);
        
        setTimeout(() => {
            if (resultDiv.parentNode) {
                resultDiv.parentNode.removeChild(resultDiv);
            }
        }, 5000);
    }
    </script>
</body>
</html>
