<?php
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'POST only']);
    exit;
}

$api_key = 'sk-1e898ddba737411e948af435d767e893';
$api_url = 'https://api.deepseek.com/v1/chat/completions';
$prompt = isset($_POST['prompt']) ? trim($_POST['prompt']) : '';
$system = isset($_POST['system']) ? trim($_POST['system']) : '';
$type = isset($_POST['type']) ? $_POST['type'] : 'chat';

// Увеличиваем timeout для генерации NPC
$timeout = ($type === 'npc') ? 60 : 30;

// --- Фильтрация только для пользовательских сообщений (чата) ---
if ($type === 'chat') {
    $maxLen = 500;
    $minLen = 5;
    $allowedPattern = '/^[\p{L}\p{N}\s.,!?;:\-()\[\]{}"\'\\\/@#\n\r]+$/u';
    $prompt = preg_replace('/\s+/u', ' ', $prompt); // убрать лишние пробелы
    if (mb_strlen($prompt, 'UTF-8') > $maxLen) {
        echo json_encode(['error' => 'Запрос слишком длинный (максимум 500 символов).']);
        exit;
    }
    if (mb_strlen($prompt, 'UTF-8') < $minLen) {
        echo json_encode(['error' => 'Запрос слишком короткий.']);
        exit;
    }
    if (!preg_match($allowedPattern, $prompt)) {
        echo json_encode(['error' => 'Запрос содержит недопустимые символы. Разрешены только буквы (включая кириллицу), цифры, пробелы и базовые знаки препинания.']);
        exit;
    }
}

if (!$prompt) {
    echo json_encode(['error' => 'No prompt']);
    exit;
}

// Оптимизируем промпт для NPC
if ($type === 'npc') {
    // Упрощаем системный промпт для более быстрой генерации
    $system = "Создай NPC для D&D. Формат:\n\nИмя и Профессия\n[имя и профессия]\n\nОписание\n[3-4 предложения о персонаже]\n\nВнешность\n[2-3 предложения о внешности]\n\nЧерты характера\n[1-2 предложения о личности]\n\nТехнические параметры\nОружие: [оружие]\nУрон: [урон]\nХиты: [хиты]";
    
    // Уменьшаем max_tokens для более быстрого ответа
    $max_tokens = 800;
    $temperature = 0.8;
} else {
    $max_tokens = 1000;
    $temperature = 0.7;
}

$data = array(
    'model' => 'deepseek-chat',
    'messages' => array(
        array('role' => 'system', 'content' => $system),
        array('role' => 'user', 'content' => $prompt)
    ),
    'max_tokens' => $max_tokens,
    'temperature' => $temperature
);

// Используем cURL для лучшего контроля timeout
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key
));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(['error' => 'Connection error: ' . $curlError]);
    exit;
}

if ($httpCode !== 200) {
    echo json_encode(['error' => "HTTP Error $httpCode: " . $response]);
    exit;
}

$result = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Invalid JSON response: ' . json_last_error_msg()]);
    exit;
}

$aiMessage = $result['choices'][0]['message']['content'] ?? '';
echo json_encode(['result' => $aiMessage], JSON_UNESCAPED_UNICODE);
