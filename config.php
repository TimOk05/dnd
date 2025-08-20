<?php
// Конфигурация API ключей
// Замените на ваш реальный API ключ DeepSeek
define('DEEPSEEK_API_KEY', 'sk-1e898ddba737411e948af435d767e893');

// Настройки приложения
define('APP_NAME', 'DnD Copilot');
define('APP_VERSION', '1.0.0');
define('DEBUG_MODE', false);

// Функция для получения API ключа
function getApiKey($service) {
    switch ($service) {
        case 'deepseek':
            return getenv('DEEPSEEK_API_KEY') ?: DEEPSEEK_API_KEY;
        default:
            return null;
    }
}

// Функция для логирования
function logMessage($message, $level = 'INFO') {
    if (DEBUG_MODE) {
        error_log("[" . date('Y-m-d H:i:s') . "] [$level] $message");
    }
}

// Функция для проверки подключения к базе данных (заглушка)
function checkDatabaseConnection() {
    return true; // Наша система не использует базу данных
}
?>
