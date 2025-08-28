<?php
/**
 * Автоматическая настройка систем безопасности D&D Copilot
 * Запустите этот файл для автоматической настройки
 */

echo "🚀 Автоматическая настройка D&D Copilot\n";
echo "=====================================\n\n";

// Функция для создания директорий
function createDirectory($path) {
    if (!is_dir($path)) {
        if (mkdir($path, 0755, true)) {
            echo "✅ Создана директория: $path\n";
            return true;
        } else {
            echo "❌ Ошибка создания директории: $path\n";
            return false;
        }
    } else {
        echo "✅ Директория уже существует: $path\n";
        return true;
    }
}

// Функция для создания .env файла
function createEnvFile() {
    if (file_exists('.env')) {
        echo "✅ Файл .env уже существует\n";
        return true;
    }
    
    $envContent = "# D&D Copilot Environment Variables
# Автоматически создано " . date('Y-m-d H:i:s') . "

# API Keys
DEEPSEEK_API_KEY=sk-1e898ddba737411e948af435d767e893

# Application Settings
APP_NAME=\"D&D Copilot\"
APP_VERSION=\"3.1\"
DEBUG_MODE=false
ENVIRONMENT=production

# Security
JWT_SECRET=" . bin2hex(random_bytes(32)) . "
SESSION_SECRET=" . bin2hex(random_bytes(32)) . "

# Rate Limiting
RATE_LIMIT_LOGIN=5
RATE_LIMIT_API=100
RATE_LIMIT_AI=50
RATE_LIMIT_WINDOW=3600

# Admin Settings
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=admin123

# File Upload Settings
MAX_FILE_SIZE=10485760
ALLOWED_FILE_TYPES=jpg,jpeg,png,gif,pdf
UPLOAD_PATH=uploads/

# Logging
LOG_LEVEL=INFO
LOG_FILE=logs/app.log
SECURITY_LOG_FILE=logs/security.log
";
    
    if (file_put_contents('.env', $envContent)) {
        echo "✅ Создан файл .env с настройками\n";
        return true;
    } else {
        echo "❌ Ошибка создания файла .env\n";
        return false;
    }
}

// Функция для создания .htaccess файлов для защиты
function createProtectionFiles() {
    $htaccessContent = "Order Deny,Allow\nDeny from all\n";
    
    $directories = ['cache', 'logs', 'config', 'services'];
    
    foreach ($directories as $dir) {
        $htaccessFile = $dir . '/.htaccess';
        if (!file_exists($htaccessFile)) {
            if (file_put_contents($htaccessFile, $htaccessContent)) {
                echo "✅ Создан защитный .htaccess для: $dir\n";
            } else {
                echo "❌ Ошибка создания .htaccess для: $dir\n";
            }
        }
    }
}

// Функция для проверки и создания пользователей
function setupDefaultUsers() {
    $usersFile = 'users.json';
    
    if (!file_exists($usersFile)) {
        $defaultUsers = [
            [
                'id' => uniqid(),
                'username' => 'admin',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                'email' => 'admin@example.com',
                'role' => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
                'is_active' => true,
                'login_count' => 0
            ],
            [
                'id' => uniqid(),
                'username' => 'demo',
                'password_hash' => password_hash('demo123', PASSWORD_DEFAULT),
                'email' => 'demo@example.com',
                'role' => 'user',
                'created_at' => date('Y-m-d H:i:s'),
                'is_active' => true,
                'login_count' => 0
            ]
        ];
        
        if (file_put_contents($usersFile, json_encode($defaultUsers, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
            echo "✅ Создан файл пользователей с демо-аккаунтами\n";
            echo "   👤 Админ: admin / admin123\n";
            echo "   👤 Демо: demo / demo123\n";
        } else {
            echo "❌ Ошибка создания файла пользователей\n";
        }
    } else {
        echo "✅ Файл пользователей уже существует\n";
    }
}

// Функция для тестирования систем
function testSystems() {
    echo "\n🔍 Тестирование систем...\n";
    
    // Тест 1: EnvironmentLoader
    try {
        require_once 'config/EnvironmentLoader.php';
        $env = new EnvironmentLoader();
        echo "✅ EnvironmentLoader работает\n";
    } catch (Exception $e) {
        echo "❌ Ошибка EnvironmentLoader: " . $e->getMessage() . "\n";
    }
    
    // Тест 2: RateLimiter
    try {
        require_once 'services/RateLimiter.php';
        $rateLimiter = new RateLimiter();
        echo "✅ RateLimiter работает\n";
    } catch (Exception $e) {
        echo "❌ Ошибка RateLimiter: " . $e->getMessage() . "\n";
    }
    
    // Тест 3: InputValidator
    try {
        require_once 'services/InputValidator.php';
        $validator = new InputValidator();
        echo "✅ InputValidator работает\n";
    } catch (Exception $e) {
        echo "❌ Ошибка InputValidator: " . $e->getMessage() . "\n";
    }
    
    // Тест 4: APICache
    try {
        require_once 'services/APICache.php';
        $cache = new APICache();
        echo "✅ APICache работает\n";
    } catch (Exception $e) {
        echo "❌ Ошибка APICache: " . $e->getMessage() . "\n";
    }
}

// Функция для создания файла статистики
function createStatsFile() {
    $statsContent = '<?php
/**
 * Статистика систем безопасности D&D Copilot
 * Доступ: http://ваш-сайт/stats.php
 */

require_once "config.php";
require_once "services/APICache.php";
require_once "services/RateLimiter.php";

header("Content-Type: text/html; charset=utf-8");

echo "<!DOCTYPE html>
<html>
<head>
    <title>Статистика D&D Copilot</title>
    <meta charset=\"utf-8\">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .stat { background: #f5f5f5; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>📊 Статистика D&D Copilot</h1>";';

try {
    $cache = new APICache();
    $stats = $cache->getStats();
    
    echo "<div class=\"stat\">
        <h3>Кэш API</h3>
        <p>Всего файлов: " . $stats["total_files"] . "</p>
        <p>Валидных файлов: " . $stats["valid_files"] . "</p>
        <p>Размер кэша: " . number_format($stats["total_size"] / 1024, 2) . " KB</p>
    </div>";
    
} catch (Exception $e) {
    echo "<div class=\"stat error\">Ошибка кэша: " . $e->getMessage() . "</div>";
}

// Проверка логов
$logFiles = ["logs/app.log", "logs/security.log", "logs/rate_limit.log"];
foreach ($logFiles as $logFile) {
    if (file_exists($logFile)) {
        $size = filesize($logFile);
        echo "<div class=\"stat\">
            <h3>Лог: " . basename($logFile) . "</h3>
            <p>Размер: " . number_format($size / 1024, 2) . " KB</p>
        </div>";
    }
}

echo "</body></html>";
';
    
    if (file_put_contents('stats.php', $statsContent)) {
        echo "✅ Создан файл статистики: stats.php\n";
    } else {
        echo "❌ Ошибка создания файла статистики\n";
    }
}

// Основной процесс настройки
echo "📋 Начинаем автоматическую настройку...\n\n";

// 1. Создание директорий
echo "1. Создание необходимых директорий:\n";
$directories = [
    'cache',
    'cache/api',
    'cache/rate_limits',
    'logs',
    'config',
    'services',
    'uploads'
];

foreach ($directories as $dir) {
    createDirectory($dir);
}

echo "\n";

// 2. Создание .env файла
echo "2. Настройка переменных окружения:\n";
createEnvFile();

echo "\n";

// 3. Создание защитных файлов
echo "3. Настройка безопасности:\n";
createProtectionFiles();

echo "\n";

// 4. Создание пользователей
echo "4. Настройка пользователей:\n";
setupDefaultUsers();

echo "\n";

// 5. Тестирование систем
testSystems();

echo "\n";

// 6. Создание файла статистики
echo "6. Создание файла статистики:\n";
createStatsFile();

echo "\n";

// Итоговый результат
echo "=====================================\n";
echo "🎉 Автоматическая настройка завершена!\n\n";

echo "📋 Что было настроено:\n";
echo "✅ Все необходимые директории созданы\n";
echo "✅ Файл .env с настройками создан\n";
echo "✅ Системы безопасности настроены\n";
echo "✅ Демо-пользователи созданы\n";
echo "✅ Файл статистики создан\n\n";

echo "🔑 Данные для входа:\n";
echo "👤 Администратор: admin / admin123\n";
echo "👤 Демо-пользователь: demo / demo123\n\n";

echo "📊 Полезные ссылки:\n";
echo "🌐 Основное приложение: http://ваш-сайт/\n";
echo "📈 Статистика: http://ваш-сайт/stats.php\n";
echo "🔒 Тест безопасности: http://ваш-сайт/test_security.php\n\n";

echo "⚠️  Важные рекомендации:\n";
echo "1. Смените пароли по умолчанию\n";
echo "2. Обновите API ключи в файле .env\n";
echo "3. Настройте HTTPS для продакшена\n";
echo "4. Регулярно проверяйте логи в папке logs/\n\n";

echo "🚀 D&D Copilot готов к использованию!\n";
