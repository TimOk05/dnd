<?php
// Конфигурация API ключей
// Используйте переменные окружения для безопасности
define('DEEPSEEK_API_KEY', getenv('DEEPSEEK_API_KEY') ?: 'sk-1e898ddba737411e948af435d767e893');

// Настройки приложения
define('APP_NAME', 'DnD Copilot');
define('APP_VERSION', '3.1.0');
define('DEBUG_MODE', getenv('DEBUG_MODE') === 'true');
define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'production');

// Настройки безопасности
define('SESSION_LIFETIME', 28800); // 8 часов
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 минут
define('PASSWORD_MIN_LENGTH', 8);

// Настройки API
define('DND_API_URL', 'https://www.dnd5eapi.co/api');
define('DEEPSEEK_API_URL', 'https://api.deepseek.com/v1/chat/completions');
define('API_TIMEOUT', 30);

// Настройки файлов
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', ['application/pdf']);

// Функция для получения API ключа
function getApiKey($service) {
    $keys = [
        'deepseek' => DEEPSEEK_API_KEY,
        'dnd_api' => null // D&D API не требует ключа
    ];
    return $keys[$service] ?? null;
}

// Функция для логирования
function logMessage($message, $level = 'INFO', $context = []) {
    if (DEBUG_MODE || $level === 'ERROR') {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        $logEntry = "[$timestamp] [$level] $message$contextStr\n";
        
        $logFile = __DIR__ . '/logs/app.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

// Функция для проверки подключения к базе данных
function checkDatabaseConnection() {
    // В будущем добавить проверку подключения к БД
    return true;
}

// Функция для валидации входных данных
function sanitizeInput($input, $type = 'string') {
    switch ($type) {
        case 'string':
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        case 'int':
            return (int) $input;
        case 'email':
            return filter_var($input, FILTER_SANITIZE_EMAIL);
        case 'url':
            return filter_var($input, FILTER_SANITIZE_URL);
        default:
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

// Функция для генерации CSRF токена
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Функция для проверки CSRF токена
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Настройка сессии
function configureSession() {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', ENVIRONMENT === 'production');
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    ini_set('session.cookie_lifetime', SESSION_LIFETIME);
    
    session_start();
    
    // Регенерация ID сессии для безопасности
    if (!isset($_SESSION['initialized'])) {
        session_regenerate_id(true);
        $_SESSION['initialized'] = true;
        $_SESSION['created_at'] = time();
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    // Проверка времени жизни сессии
    if (isset($_SESSION['created_at']) && (time() - $_SESSION['created_at']) > SESSION_LIFETIME) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
    
    // Проверка IP адреса
    if (isset($_SESSION['ip']) && $_SESSION['ip'] !== ($_SERVER['REMOTE_ADDR'] ?? 'unknown')) {
        session_destroy();
        header('Location: login.php?error=session_hijack');
        exit;
    }
}

// Инициализация конфигурации
configureSession();
?>
