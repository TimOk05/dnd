<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест доступа к manifest.json</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .result { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <h1>🔍 Тест доступа к manifest.json</h1>
    
    <div class="result info">
        <h2>PHP проверки:</h2>
        <?php
        $manifestPath = __DIR__ . '/manifest.json';
        echo '<p><strong>Путь:</strong> ' . $manifestPath . '</p>';
        echo '<p><strong>file_exists():</strong> ' . (file_exists($manifestPath) ? '✅ Да' : '❌ Нет') . '</p>';
        echo '<p><strong>is_readable():</strong> ' . (is_readable($manifestPath) ? '✅ Да' : '❌ Нет') . '</p>';
        echo '<p><strong>is_file():</strong> ' . (is_file($manifestPath) ? '✅ Да' : '❌ Нет') . '</p>';
        
        if (file_exists($manifestPath)) {
            echo '<p><strong>Размер:</strong> ' . filesize($manifestPath) . ' байт</p>';
            echo '<p><strong>Права:</strong> ' . substr(sprintf('%o', fileperms($manifestPath)), -4) . '</p>';
            
            $content = file_get_contents($manifestPath);
            if ($content !== false) {
                echo '<p><strong>Чтение:</strong> ✅ Успешно</p>';
                echo '<p><strong>JSON валидность:</strong> ' . (json_decode($content) ? '✅ Валиден' : '❌ Невалиден') . '</p>';
                if (json_last_error() !== JSON_ERROR_NONE) {
                    echo '<p><strong>Ошибка JSON:</strong> ' . json_last_error_msg() . '</p>';
                }
            } else {
                echo '<p><strong>Чтение:</strong> ❌ Ошибка</p>';
            }
        }
        ?>
    </div>
    
    <div class="result info">
        <h2>Тест через браузер:</h2>
        <button onclick="testBrowserAccess()">Тест загрузки manifest.json</button>
        <div id="browserResult"></div>
    </div>
    
    <div class="result info">
        <h2>Прямые ссылки:</h2>
        <p><a href="./manifest.json" target="_blank">Открыть manifest.json в новой вкладке</a></p>
        <p><a href="./check-manifest.php" target="_blank">Открыть check-manifest.php</a></p>
        <p><a href="./api-status.php" target="_blank">Открыть api-status.php</a></p>
    </div>
    
    <script>
    function testBrowserAccess() {
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
                    resultDiv.innerHTML = '<div class="result success">✅ manifest.json доступен через браузер</div>';
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
