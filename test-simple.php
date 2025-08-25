<?php
/**
 * Простой тест генераторов
 */

require_once 'config.php';

echo "<h1>Тест генераторов D&D</h1>\n";

// Тест 1: Проверка конфигурации
echo "<h2>1. Проверка конфигурации</h2>\n";
echo "API ключ DeepSeek: " . (getApiKey('deepseek') ? 'Настроен' : 'Не настроен') . "<br>\n";
echo "Режим отладки: " . (DEBUG_MODE ? 'Включен' : 'Выключен') . "<br>\n";

// Тест 2: Проверка доступности cURL
echo "<h2>2. Проверка cURL</h2>\n";
if (function_exists('curl_init')) {
    echo "✓ cURL доступен<br>\n";
    
    // Тест подключения к D&D API
    $ch = curl_init('https://www.dnd5eapi.co/api/classes');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($result && $httpCode === 200) {
        echo "✓ D&D 5e API доступен<br>\n";
    } else {
        echo "✗ D&D 5e API недоступен (HTTP $httpCode)<br>\n";
    }
} else {
    echo "✗ cURL недоступен<br>\n";
}

// Тест 3: Проверка файлов
echo "<h2>3. Проверка файлов</h2>\n";
$files = [
    'api/dnd-api-working.php',
    'api/generate-npc.php',
    'api/generate-enemies.php',
    'api/generate-characters.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✓ $file существует<br>\n";
    } else {
        echo "✗ $file отсутствует<br>\n";
    }
}

// Тест 4: Проверка синтаксиса
echo "<h2>4. Проверка синтаксиса</h2>\n";
foreach ($files as $file) {
    if (file_exists($file)) {
        $output = shell_exec("php -l $file 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "✓ $file - синтаксис корректен<br>\n";
        } else {
            echo "✗ $file - ошибки синтаксиса<br>\n";
        }
    }
}

echo "<h2>Тест завершен</h2>\n";
?>
