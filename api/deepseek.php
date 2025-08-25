<?php
// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Подключаем конфигурацию
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Обработка preflight запросов
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    // Получаем данные запроса
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Invalid JSON');
    }

    $message = $input['message'] ?? '';
    $isChat = $input['isChat'] ?? false;

    if (empty($message)) {
        throw new Exception('Message is required');
    }

    // Получаем API ключ - замените на ваш реальный ключ
    $apiKey = getApiKey('deepseek'); // Используем функцию из config.php

    if (empty($apiKey) || $apiKey === 'sk-your-deepseek-api-key-here') {
        throw new Exception('API key not configured - please update the API key in config.php');
    }

    // Загружаем данные из PDF файлов и их описаний
    $pdfContext = '';
    $pdfDir = '../pdf/';
    $pdfDatabaseFile = 'pdf-database.json';
    
    if (is_dir($pdfDir)) {
        $pdfFiles = glob($pdfDir . '*.pdf');
        if (!empty($pdfFiles)) {
            $pdfContext = "\n\nДоступные PDF файлы с описаниями:\n";
            
            // Загружаем базу данных описаний
            $pdfDatabase = [];
            if (file_exists($pdfDatabaseFile)) {
                $pdfDatabase = json_decode(file_get_contents($pdfDatabaseFile), true);
            }
            
            foreach ($pdfFiles as $pdfFile) {
                $filename = basename($pdfFile);
                $fileInfo = null;
                
                // Ищем описание в базе данных
                if (isset($pdfDatabase['files'])) {
                    foreach ($pdfDatabase['files'] as $file) {
                        if ($file['filename'] === $filename) {
                            $fileInfo = $file;
                            break;
                        }
                    }
                }
                
                if ($fileInfo) {
                    $pdfContext .= "- Файл: {$fileInfo['title']} ({$filename})\n";
                    $pdfContext .= "  Категория: {$fileInfo['category']}, Уровень: {$fileInfo['level']}\n";
                    $pdfContext .= "  Описание: {$fileInfo['summary']}\n";
                    $pdfContext .= "  Теги: " . implode(', ', $fileInfo['tags']) . "\n\n";
                } else {
                    $pdfContext .= "- Файл: $filename (без описания)\n";
                }
            }
        }
    }
    
    // Системный промпт для D&D помощника с контекстом приложения
    $systemPrompt = $isChat
        ? "Ты опытный мастер D&D и AI-помощник в приложении DM Copilot. Ты знаешь все функции приложения: таблицы данных (NPC, зелья, напитки, события, таверны), ведение сессий, генерация подсказок, анализ сессий. У тебя есть доступ к PDF файлам с дополнительной информацией о мире и правилах. Отвечай на русском языке, давай практичные советы по ведению игры, созданию сюжетов, балансировке встреч и правилам D&D. Будь дружелюбным и полезным. Можешь ссылаться на функции приложения и предлагать их использовать. Всегда давай конкретные, применимые советы." . $pdfContext
        : "Ты опытный мастер D&D и AI-помощник в приложении DM Copilot. Ты знаешь все функции приложения: таблицы данных (NPC, зелья, напитки, события, таверны), ведение сессий, генерация подсказок, анализ сессий. У тебя есть доступ к PDF файлам с дополнительной информацией о мире и правилах. Отвечай на русском языке, создавай креативные и сбалансированные элементы для D&D сессий. Всегда структурируй ответы четко, используй маркированные списки для лучшей читаемости. Можешь ссылаться на функции приложения и предлагать их использовать. Создавай контент, который можно сразу использовать в игре." . $pdfContext;

    // Подготавливаем данные для DeepSeek API
    $data = [
        'model' => 'deepseek-chat',
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $message]
        ],
        'max_tokens' => 1000,
        'temperature' => 0.7
    ];

    // Проверяем, доступен ли cURL
    if (!function_exists('curl_init')) {
        throw new Exception('cURL extension not available');
    }

    // Вызываем DeepSeek API
    $ch = curl_init('https://api.deepseek.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        throw new Exception('cURL error: ' . $curlError);
    }

    if ($httpCode !== 200) {
        throw new Exception('DeepSeek API error: HTTP ' . $httpCode . ' - ' . $response);
    }

    $result = json_decode($response, true);

    if (!$result || !isset($result['choices'][0]['message']['content'])) {
        throw new Exception('Invalid response from DeepSeek: ' . $response);
    }

    // Возвращаем результат
    echo json_encode([
        'suggestion' => $result['choices'][0]['message']['content'],
        'tokens' => $result['usage']['total_tokens'] ?? 0
    ]);

} catch (Exception $e) {
    // Логируем ошибку
    error_log('DeepSeek API Error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'details' => $e->getMessage()
    ]);
}
?>
