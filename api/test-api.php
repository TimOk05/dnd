<?php
header('Content-Type: application/json');

// Тест доступности D&D API
function testDndApi() {
    $url = 'https://www.dnd5eapi.co/api/monsters';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'DnD-Copilot/1.0');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => 'CURL Error: ' . $error];
    }
    
    if ($http_code !== 200) {
        return ['success' => false, 'error' => 'HTTP Error: ' . $http_code];
    }
    
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['success' => false, 'error' => 'JSON Error: ' . json_last_error_msg()];
    }
    
    return [
        'success' => true,
        'monsters_count' => $data['count'] ?? 0,
        'sample_monsters' => array_slice($data['results'] ?? [], 0, 5)
    ];
}

// Тест доступности DeepSeek API
function testDeepSeekApi() {
    // Проверяем наличие ключа API
    if (!defined('DEEPSEEK_API_KEY') || empty(DEEPSEEK_API_KEY)) {
        return ['success' => false, 'error' => 'DeepSeek API key not configured'];
    }
    
    return ['success' => true, 'message' => 'DeepSeek API key configured'];
}

// Выполняем тесты
$results = [
    'dnd_api' => testDndApi(),
    'deepseek_api' => testDeepSeekApi(),
    'timestamp' => date('Y-m-d H:i:s'),
    'server_info' => [
        'php_version' => PHP_VERSION,
        'curl_enabled' => function_exists('curl_init'),
        'json_enabled' => function_exists('json_decode')
    ]
];

echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
