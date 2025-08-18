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
// --- Фильтрация prompt ---
$maxLen = 500;
$minLen = 5;
$allowedPattern = '/^[a-zA-Zа-яА-ЯёЁ0-9 .,!?;:\-()\[\]{}"\'\\\/@#\n\r]+$/u';
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
    echo json_encode(['error' => 'Запрос содержит недопустимые символы. Разрешены только буквы, цифры, пробелы и базовые знаки препинания.']);
    exit;
}
if (!$prompt) {
    echo json_encode(['error' => 'No prompt']);
    exit;
}
$data = array(
    'model' => 'deepseek-chat',
    'messages' => array(
        array('role' => 'system', 'content' => $system),
        array('role' => 'user', 'content' => $prompt)
    ),
    'max_tokens' => 1000,
    'temperature' => 0.7
);
$options = array(
    'http' => array(
        'header' => "Content-Type: application/json\r\n" .
                    "Authorization: Bearer " . $api_key . "\r\n",
        'method' => 'POST',
        'content' => json_encode($data),
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
    echo json_encode(['error' => "HTTP Error $status: $msg"]);
    exit;
}
$result = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Invalid JSON response: ' . json_last_error_msg()]);
    exit;
}
$aiMessage = $result['choices'][0]['message']['content'] ?? '';
echo json_encode(['result' => $aiMessage], JSON_UNESCAPED_UNICODE);
