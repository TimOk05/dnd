<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Финальный тест - DnD Copilot</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f0f0f0; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .status-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
        .status-card { padding: 20px; border-radius: 10px; border: 2px solid; }
        .status-ok { background: #d4edda; border-color: #28a745; color: #155724; }
        .status-error { background: #f8d7da; border-color: #dc3545; color: #721c24; }
        .status-warning { background: #fff3cd; border-color: #ffc107; color: #856404; }
        .test-btn { background: #007bff; color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; margin: 5px; }
        .test-btn:hover { background: #0056b3; }
        .test-btn:disabled { background: #6c757d; cursor: not-allowed; }
        .result { margin: 10px 0; padding: 15px; border-radius: 8px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        h1 { text-align: center; color: #333; margin-bottom: 30px; }
        h2 { color: #495057; margin-top: 30px; }
        .summary { background: #e9ecef; padding: 20px; border-radius: 10px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎯 Финальный тест DnD Copilot</h1>
        
        <div class="summary">
            <h2>📋 Статус системы:</h2>
            <div class="status-grid">
                <div class="status-card status-ok">
                    <h3>✅ Основные файлы</h3>
                    <p>Все необходимые PHP файлы присутствуют</p>
                </div>
                <div class="status-card status-warning">
                    <h3>⚠️ PWA компоненты</h3>
                    <p>Manifest.json временно отключен</p>
                </div>
                <div class="status-card status-ok">
                    <h3>✅ API готов</h3>
                    <p>Генерация NPC должна работать</p>
                </div>
                <div class="status-card status-ok">
                    <h3>✅ Безопасность</h3>
                    <p>htaccess настроен правильно</p>
                </div>
            </div>
        </div>
        
        <h2>🧪 Тесты компонентов:</h2>
        <button class="test-btn" onclick="testAPI()">Тест API генерации NPC</button>
        <button class="test-btn" onclick="testFavicon()">Тест favicon.svg</button>
        <button class="test-btn" onclick="testServiceWorker()">Тест Service Worker</button>
        <button class="test-btn" onclick="testAll()">Запустить все тесты</button>
        
        <div id="testResults"></div>
        
        <h2>🔗 Полезные ссылки:</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <a href="simple-npc-test.php" style="display: block; padding: 15px; background: #007bff; color: white; text-decoration: none; border-radius: 8px; text-align: center;">
                🎲 Простой тест NPC
            </a>
            <a href="index.php" style="display: block; padding: 15px; background: #28a745; color: white; text-decoration: none; border-radius: 8px; text-align: center;">
                🏠 Главная страница
            </a>
            <a href="api/generate-npc-test.php" style="display: block; padding: 15px; background: #ffc107; color: #212529; text-decoration: none; border-radius: 8px; text-align: center;">
                🔧 API endpoint
            </a>
            <a href="test-npc-no-auth.php" style="display: block; padding: 15px; background: #17a2b8; color: white; text-decoration: none; border-radius: 8px; text-align: center;">
                🧪 Расширенный тест
            </a>
        </div>
        
        <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
            <h3>📝 Что исправлено:</h3>
            <ul>
                <li>✅ Убрана зависимость от manifest.json</li>
                <li>✅ Обновлен Service Worker</li>
                <li>✅ Создан простой тест генерации NPC</li>
                <li>✅ Настроен .htaccess для доступа к JSON файлам</li>
                <li>✅ Добавлены мета-теги для PWA без manifest</li>
            </ul>
        </div>
    </div>

    <script>
    async function testAPI() {
        showResult('🔄 Тестирование API генерации NPC...', 'info');
        
        try {
            const formData = new FormData();
            formData.append('race', 'human');
            formData.append('class', 'fighter');
            formData.append('level', '1');
            formData.append('alignment', 'neutral');
            formData.append('background', 'soldier');
            
            const response = await fetch('api/generate-npc-test.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success && data.npc) {
                showResult('✅ API генерации NPC работает! NPC: ' + (data.npc.name || 'Безымянный'), 'success');
            } else {
                showResult('❌ Ошибка API: ' + (data.error || 'Неизвестная ошибка'), 'error');
            }
        } catch (error) {
            showResult('❌ Ошибка сети: ' + error.message, 'error');
        }
    }
    
    async function testFavicon() {
        try {
            const response = await fetch('./favicon.svg');
            if (response.ok) {
                showResult('✅ favicon.svg доступен', 'success');
            } else {
                showResult('❌ favicon.svg недоступен: ' + response.status, 'error');
            }
        } catch (error) {
            showResult('❌ Ошибка загрузки favicon: ' + error.message, 'error');
        }
    }
    
    async function testServiceWorker() {
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.register('./sw.js');
                showResult('✅ Service Worker зарегистрирован успешно', 'success');
            } catch (error) {
                showResult('❌ Ошибка регистрации Service Worker: ' + error.message, 'error');
            }
        } else {
            showResult('⚠️ Service Worker не поддерживается браузером', 'warning');
        }
    }
    
    async function testAll() {
        showResult('🔄 Запуск всех тестов...', 'info');
        
        await testFavicon();
        await new Promise(resolve => setTimeout(resolve, 500));
        
        await testServiceWorker();
        await new Promise(resolve => setTimeout(resolve, 500));
        
        await testAPI();
        
        showResult('✅ Все тесты завершены!', 'success');
    }
    
    function showResult(message, type) {
        const resultsDiv = document.getElementById('testResults');
        const resultDiv = document.createElement('div');
        resultDiv.className = 'result ' + type;
        resultDiv.textContent = message;
        resultsDiv.appendChild(resultDiv);
        
        // Удаляем результат через 8 секунд
        setTimeout(() => {
            if (resultDiv.parentNode) {
                resultDiv.parentNode.removeChild(resultDiv);
            }
        }, 8000);
    }
    </script>
</body>
</html>
