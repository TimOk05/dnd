<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Статус API - DnD Copilot</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .status { margin: 20px 0; padding: 15px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .test-btn { padding: 10px 20px; margin: 5px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .test-btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>🔧 Статус API и исправления</h1>
    
    <div class="status info">
        <h2>Исправленные проблемы:</h2>
        <ul>
            <li>✅ Исправлен путь к manifest.json (добавлен ./)</li>
            <li>✅ Создан правильный SVG favicon</li>
            <li>✅ Добавлена регистрация Service Worker</li>
            <li>✅ Исправлены пути в sw.js</li>
            <li>✅ Исправлен расчет хитов по правилам D&D 5e</li>
            <li>✅ Добавлен перевод характеристик на русский</li>
            <li>✅ Исправлена обработка бонусов рас</li>
            <li>✅ Обновлен .htaccess для разрешения доступа к JSON файлам</li>
        </ul>
    </div>
    
    <div class="status info">
        <h2>Проверка файлов:</h2>
        <ul>
            <li>manifest.json: <?php echo file_exists('manifest.json') ? '✅ Существует' : '❌ Отсутствует'; ?></li>
            <li>favicon.svg: <?php echo file_exists('favicon.svg') ? '✅ Существует' : '❌ Отсутствует'; ?></li>
            <li>sw.js: <?php echo file_exists('sw.js') ? '✅ Существует' : '❌ Отсутствует'; ?></li>
            <li>api/dnd-api.php: <?php echo file_exists('api/dnd-api.php') ? '✅ Существует' : '❌ Отсутствует'; ?></li>
            <li>api/generate-npc.php: <?php echo file_exists('api/generate-npc.php') ? '✅ Существует' : '❌ Отсутствует'; ?></li>
        </ul>
    </div>
    
    <div class="status info">
        <h2>Тесты:</h2>
        <button class="test-btn" onclick="testManifest()">Тест manifest.json</button>
        <button class="test-btn" onclick="testFavicon()">Тест favicon.svg</button>
        <button class="test-btn" onclick="testServiceWorker()">Тест Service Worker</button>
        <button class="test-btn" onclick="testNPCGeneration()">Тест генерации NPC</button>
        <div id="testResults"></div>
    </div>
    
    <div class="status warning">
        <h2>Инструкции по устранению ошибок:</h2>
        <ol>
            <li><strong>Ошибки 404 для manifest.json:</strong> Исправлено - добавлен правильный путь ./manifest.json</li>
            <li><strong>Ошибки favicon:</strong> Исправлено - создан SVG favicon и добавлена ссылка в HTML</li>
            <li><strong>Ошибки Service Worker:</strong> Исправлено - добавлена регистрация в template.html</li>
            <li><strong>Проблемы с генерацией NPC:</strong> Исправлено - улучшен расчет характеристик и хитов</li>
        </ol>
    </div>
    
    <div class="status success">
        <h2>Следующие шаги:</h2>
        <ul>
            <li>Очистите кэш браузера (Ctrl+F5)</li>
            <li>Проверьте консоль браузера на наличие ошибок</li>
            <li>Протестируйте генерацию NPC через основной интерфейс</li>
            <li>Проверьте работу на мобильных устройствах</li>
        </ul>
    </div>

    <script>
        function testManifest() {
            fetch('./manifest.json')
                .then(response => {
                    if (response.ok) {
                        showResult('✅ manifest.json доступен', 'success');
                    } else {
                        showResult('❌ manifest.json недоступен: ' + response.status, 'error');
                    }
                })
                .catch(error => {
                    showResult('❌ Ошибка загрузки manifest.json: ' + error.message, 'error');
                });
        }
        
        function testFavicon() {
            fetch('./favicon.svg')
                .then(response => {
                    if (response.ok) {
                        showResult('✅ favicon.svg доступен', 'success');
                    } else {
                        showResult('❌ favicon.svg недоступен: ' + response.status, 'error');
                    }
                })
                .catch(error => {
                    showResult('❌ Ошибка загрузки favicon.svg: ' + error.message, 'error');
                });
        }
        
        function testServiceWorker() {
            if ('serviceWorker' in navigator) {
                showResult('✅ Service Worker поддерживается браузером', 'success');
                navigator.serviceWorker.register('./sw.js')
                    .then(registration => {
                        showResult('✅ Service Worker зарегистрирован успешно', 'success');
                    })
                    .catch(error => {
                        showResult('❌ Ошибка регистрации Service Worker: ' + error.message, 'error');
                    });
            } else {
                showResult('❌ Service Worker не поддерживается браузером', 'warning');
            }
        }
        
        function testNPCGeneration() {
            const formData = new FormData();
            formData.append('race', 'human');
            formData.append('class', 'fighter');
            formData.append('level', '1');
            formData.append('alignment', 'neutral');
            formData.append('background', 'soldier');
            
            fetch('api/generate-npc.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showResult('✅ Генерация NPC работает', 'success');
                } else {
                    showResult('❌ Ошибка генерации NPC: ' + (data.error || 'Неизвестная ошибка'), 'error');
                }
            })
            .catch(error => {
                showResult('❌ Ошибка сети при генерации NPC: ' + error.message, 'error');
            });
        }
        
        function showResult(message, type) {
            const resultsDiv = document.getElementById('testResults');
            const resultDiv = document.createElement('div');
            resultDiv.className = 'status ' + type;
            resultDiv.textContent = message;
            resultsDiv.appendChild(resultDiv);
            
            // Удаляем результат через 5 секунд
            setTimeout(() => {
                if (resultDiv.parentNode) {
                    resultDiv.parentNode.removeChild(resultDiv);
                }
            }, 5000);
        }
    </script>
</body>
</html>
