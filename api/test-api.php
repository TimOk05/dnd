<?php
/**
 * Тестирование доступности D&D API
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Тест доступности D&D API</h1>";

// Тест D&D 5e API
echo "<h2>1. Тест D&D 5e API (dnd5eapi.co)</h2>";

$dnd5e_url = 'https://www.dnd5eapi.co/api/classes';
$ch = curl_init($dnd5e_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "<p style='color: red;'>❌ Ошибка: $error</p>";
} elseif ($httpCode !== 200) {
    echo "<p style='color: red;'>❌ HTTP код: $httpCode</p>";
} else {
    $data = json_decode($response, true);
    if ($data && isset($data['results'])) {
        echo "<p style='color: green;'>✅ API доступен! Найдено классов: " . count($data['results']) . "</p>";
        echo "<ul>";
        foreach (array_slice($data['results'], 0, 5) as $class) {
            echo "<li>" . htmlspecialchars($class['name']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>⚠️ API отвечает, но формат неожиданный</p>";
        echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
    }
}

// Тест Open5e API
echo "<h2>2. Тест Open5e API</h2>";

$open5e_url = 'https://open5e.com/api/classes/';
$ch = curl_init($open5e_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "<p style='color: red;'>❌ Ошибка: $error</p>";
} elseif ($httpCode !== 200) {
    echo "<p style='color: red;'>❌ HTTP код: $httpCode</p>";
} else {
    echo "<p style='color: green;'>✅ Open5e API доступен!</p>";
}

// Тест локальной генерации
echo "<h2>3. Тест локальной генерации NPC</h2>";

require_once 'dnd-api.php';

try {
    $dndApi = new DndApiManager();
    
    // Тест генерации без API
    $npc = $dndApi->generateNPC([
        'race' => 'human',
        'class' => 'fighter',
        'level' => 1,
        'alignment' => 'neutral'
    ]);
    
    if ($npc) {
        echo "<p style='color: green;'>✅ Локальная генерация работает!</p>";
        echo "<pre>" . print_r($npc, true) . "</pre>";
    } else {
        echo "<p style='color: red;'>❌ Локальная генерация не работает</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка: " . $e->getMessage() . "</p>";
}

// Информация о системе
echo "<h2>4. Информация о системе</h2>";
echo "<p><strong>PHP версия:</strong> " . phpversion() . "</p>";
echo "<p><strong>cURL доступен:</strong> " . (function_exists('curl_init') ? 'Да' : 'Нет') . "</p>";
echo "<p><strong>JSON доступен:</strong> " . (function_exists('json_decode') ? 'Да' : 'Нет') . "</p>";
echo "<p><strong>Время выполнения:</strong> " . date('Y-m-d H:i:s') . "</p>";

echo "<h2>5. Рекомендации</h2>";
echo "<ul>";
echo "<li>Если D&D 5e API недоступен, система будет использовать локальную генерацию</li>";
echo "<li>Для улучшения качества можно подключить дополнительные API</li>";
echo "<li>Все API должны поддерживать HTTPS</li>";
echo "</ul>";
?>
