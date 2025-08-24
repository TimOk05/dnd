<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Проверка manifest.json</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .result { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <h1>🔍 Проверка manifest.json</h1>
    
    <div class="result info">
        <h2>Информация о файле:</h2>
        <p><strong>Текущая директория:</strong> <?php echo __DIR__; ?></p>
        <p><strong>Полный путь к manifest.json:</strong> <?php echo __DIR__ . '/manifest.json'; ?></p>
    </div>
    
    <div class="result <?php echo file_exists(__DIR__ . '/manifest.json') ? 'success' : 'error'; ?>">
        <h2>Проверка существования файла:</h2>
        <p><strong>file_exists():</strong> <?php echo file_exists(__DIR__ . '/manifest.json') ? '✅ Файл существует' : '❌ Файл не найден'; ?></p>
        <p><strong>is_readable():</strong> <?php echo is_readable(__DIR__ . '/manifest.json') ? '✅ Файл читается' : '❌ Файл не читается'; ?></p>
        <p><strong>filesize():</strong> <?php echo file_exists(__DIR__ . '/manifest.json') ? filesize(__DIR__ . '/manifest.json') . ' байт' : 'N/A'; ?></p>
    </div>
    
    <?php if (file_exists(__DIR__ . '/manifest.json')): ?>
    <div class="result info">
        <h2>Содержимое файла:</h2>
        <pre><?php echo htmlspecialchars(file_get_contents(__DIR__ . '/manifest.json')); ?></pre>
    </div>
    
    <div class="result <?php echo json_decode(file_get_contents(__DIR__ . '/manifest.json')) ? 'success' : 'error'; ?>">
        <h2>Проверка JSON:</h2>
        <p><strong>JSON валидность:</strong> <?php echo json_decode(file_get_contents(__DIR__ . '/manifest.json')) ? '✅ Валидный JSON' : '❌ Невалидный JSON'; ?></p>
        <?php if (json_last_error() !== JSON_ERROR_NONE): ?>
        <p><strong>Ошибка JSON:</strong> <?php echo json_last_error_msg(); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="result info">
        <h2>Тест через браузер:</h2>
        <button onclick="testManifest()">Тест загрузки manifest.json</button>
        <div id="browserResult"></div>
    </div>
    
    <script>
    function testManifest() {
        const resultDiv = document.getElementById('browserResult');
        resultDiv.innerHTML = 'Тестирование...';
        
        fetch('./manifest.json')
            .then(response => {
                if (response.ok) {
                    return response.text();
                } else {
                    throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                }
            })
            .then(text => {
                try {
                    const json = JSON.parse(text);
                    resultDiv.innerHTML = '<div class="result success">✅ manifest.json загружен и валиден</div>';
                    resultDiv.innerHTML += '<pre>' + JSON.stringify(json, null, 2) + '</pre>';
                } catch (e) {
                    resultDiv.innerHTML = '<div class="result error">❌ Ошибка парсинга JSON: ' + e.message + '</div>';
                    resultDiv.innerHTML += '<pre>' + text + '</pre>';
                }
            })
            .catch(error => {
                resultDiv.innerHTML = '<div class="result error">❌ Ошибка загрузки: ' + error.message + '</div>';
            });
    }
    </script>
</body>
</html>
