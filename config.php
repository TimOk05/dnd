<?php
// Конфигурация API ключей
// Замените на ваш реальный API ключ DeepSeek
define('DEEPSEEK_API_KEY', 'sk-1e898ddba737411e948af435d767e893');

// Настройки базы данных для системы регистрации
define('DB_HOST', 'localhost');
define('DB_NAME', 'dnd_copilot');
define('DB_USER', 'root');
define('DB_PASS', '');

// Настройки приложения
define('APP_NAME', 'DnD Copilot');
define('APP_VERSION', '1.0.0');
define('DEBUG_MODE', true);

// Настройки безопасности
define('SALT_ROUNDS', 12); // Для password_hash
define('SESSION_TIMEOUT', 3600); // 1 час
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 минут

// Функция для получения API ключа
function getApiKey($service) {
    switch ($service) {
        case 'deepseek':
            return getenv('DEEPSEEK_API_KEY') ?: DEEPSEEK_API_KEY;
        default:
            return null;
    }
}

// Функция для подключения к базе данных
function getDbConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            error_log("Database connection failed: " . $e->getMessage());
        }
        return null;
    }
}

// Функция для инициализации базы данных
function initDatabase() {
    $pdo = getDbConnection();
    if (!$pdo) return false;
    
    try {
        // Создаем таблицу пользователей
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_login TIMESTAMP NULL,
                is_active BOOLEAN DEFAULT TRUE,
                role ENUM('user', 'admin') DEFAULT 'user',
                avatar_url VARCHAR(255) NULL,
                preferences JSON NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Создаем таблицу сессий
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_sessions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                session_token VARCHAR(255) UNIQUE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NOT NULL,
                ip_address VARCHAR(45),
                user_agent TEXT,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Создаем таблицу заметок пользователей
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_notes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                type ENUM('npc', 'initiative', 'dice', 'general') DEFAULT 'general',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                is_favorite BOOLEAN DEFAULT FALSE,
                tags JSON NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Создаем таблицу чата пользователей
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_chat (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                role ENUM('user', 'assistant', 'system') NOT NULL,
                content TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                session_id VARCHAR(100) NOT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Создаем таблицу попыток входа
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS login_attempts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                success BOOLEAN DEFAULT FALSE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        return true;
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            error_log("Database initialization failed: " . $e->getMessage());
        }
        return false;
    }
}

// Функция для логирования
function logMessage($message, $level = 'INFO') {
    if (DEBUG_MODE) {
        error_log("[" . date('Y-m-d H:i:s') . "] [$level] $message");
    }
}

// Функция для генерации безопасного токена
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Функция для проверки сложности пароля
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Пароль должен содержать минимум 8 символов';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Пароль должен содержать хотя бы одну заглавную букву';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Пароль должен содержать хотя бы одну строчную букву';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Пароль должен содержать хотя бы одну цифру';
    }
    
    return $errors;
}

// Функция для валидации email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Функция для валидации имени пользователя
function validateUsername($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}
?>
