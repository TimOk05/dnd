<?php
session_start();
require_once 'users.php';

// Проверяем авторизацию пользователя
if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized - please login']);
    exit;
}

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

// Используем file_get_contents для совместимости
$options = array(
    'http' => array(
        'header' => "Content-Type: application/json\r\n" .
                    "Authorization: Bearer " . $api_key . "\r\n",
        'method' => 'POST',
        'content' => json_encode($data),
        'timeout' => $timeout,
        'ignore_errors' => true
    )
);

$context = stream_context_create($options);
$response = file_get_contents($api_url, false, $context);

if ($response === false) {
    $error = error_get_last();
    echo json_encode(['error' => 'Request failed: ' . ($error['message'] ?? 'unknown')]);
    exit;
}

if (!isset($http_response_header) || empty($http_response_header)) {
    echo json_encode(['error' => 'No HTTP response headers received']);
    exit;
}

list($version, $status, $msg) = explode(' ', $http_response_header[0], 3);
if ($status != 200) {
    echo json_encode(['error' => "HTTP Error $status: $msg - $response"]);
    exit;
}

$result = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Invalid JSON response: ' . json_last_error_msg() . ' - Raw response: ' . substr($response, 0, 200)]);
    exit;
}

$aiMessage = $result['choices'][0]['message']['content'] ?? '';
if (empty($aiMessage)) {
    echo json_encode(['error' => 'Empty AI response - Full response: ' . json_encode($result)]);
    exit;
}

echo json_encode(['result' => $aiMessage], JSON_UNESCAPED_UNICODE);
