<?php
// Конфигурация API ключей
// Замените на ваш реальный API ключ DeepSeek
define('DEEPSEEK_API_KEY', 'your-deepseek-api-key-here');

// Настройки базы данных (если понадобится)
define('DB_HOST', 'localhost');
define('DB_NAME', 'dm_copilot');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');

// Настройки приложения
define('APP_NAME', 'DM Copilot');
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
?>
