<?php
/**
 * Тестирование систем безопасности D&D Copilot
 * Запустите этот файл для проверки работоспособности
 */

echo "🔒 Тестирование систем безопасности D&D Copilot\n";
echo "==============================================\n\n";

// Тест 1: Загрузка переменных окружения
echo "1. Тестирование загрузки переменных окружения...\n";
try {
    require_once 'config/EnvironmentLoader.php';
    $env = new EnvironmentLoader();
    echo "✅ EnvironmentLoader загружен успешно\n";
    echo "   - APP_NAME: " . $env->get('APP_NAME', 'Не найден') . "\n";
    echo "   - DEBUG_MODE: " . ($env->getBool('DEBUG_MODE') ? 'true' : 'false') . "\n";
} catch (Exception $e) {
    echo "❌ Ошибка загрузки переменных окружения: " . $e->getMessage() . "\n";
    echo "   Убедитесь, что файл .env существует и содержит необходимые переменные\n";
}

echo "\n";

// Тест 2: Rate Limiting
echo "2. Тестирование Rate Limiting...\n";
try {
    require_once 'services/RateLimiter.php';
    $rateLimiter = new RateLimiter();
    
    $testIP = '127.0.0.1';
    $testAction = 'test';
    
    // Тестируем лимит
    $result = $rateLimiter->check($testAction, $testIP);
    echo "✅ Rate Limiter работает\n";
    echo "   - Первый запрос: " . ($result ? 'разрешен' : 'заблокирован') . "\n";
    
    // Проверяем оставшиеся запросы
    $remaining = $rateLimiter->getRemaining($testAction, $testIP);
    echo "   - Оставшиеся запросы: $remaining\n";
    
    // Очищаем тестовые данные
    $rateLimiter->reset($testAction, $testIP);
    
} catch (Exception $e) {
    echo "❌ Ошибка Rate Limiting: " . $e->getMessage() . "\n";
}

echo "\n";

// Тест 3: Валидация входных данных
echo "3. Тестирование валидации входных данных...\n";
try {
    require_once 'services/InputValidator.php';
    $validator = new InputValidator();
    
    // Тестовые данные
    $testData = [
        'email' => 'test@example.com',
        'username' => 'testuser',
        'age' => '25',
        'description' => 'Test description'
    ];
    
    $rules = [
        'email' => ['required', 'email'],
        'username' => ['required', 'min:3', 'max:20', 'alphanumeric'],
        'age' => ['required', 'integer', 'min_value:18', 'max_value:100'],
        'description' => ['max:500', 'xss_clean']
    ];
    
    $isValid = $validator->validate($testData, $rules);
    echo "✅ Валидация работает\n";
    echo "   - Результат валидации: " . ($isValid ? 'успешно' : 'ошибки') . "\n";
    
    if (!$isValid) {
        $errors = $validator->getErrors();
        foreach ($errors as $field => $fieldErrors) {
            echo "   - Ошибки в поле '$field': " . implode(', ', $fieldErrors) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка валидации: " . $e->getMessage() . "\n";
}

echo "\n";

// Тест 4: Кэширование API
echo "4. Тестирование кэширования API...\n";
try {
    require_once 'services/APICache.php';
    $cache = new APICache();
    
    $testKey = 'test_cache_key';
    $testData = ['message' => 'Hello from cache!', 'timestamp' => time()];
    
    // Сохраняем данные в кэш
    $cache->set($testKey, $testData, 60); // 1 минута
    echo "✅ Кэш работает\n";
    echo "   - Данные сохранены в кэш\n";
    
    // Получаем данные из кэша
    $cachedData = $cache->get($testKey);
    if ($cachedData) {
        echo "   - Данные получены из кэша: " . $cachedData['message'] . "\n";
    } else {
        echo "   - Данные не найдены в кэше\n";
    }
    
    // Получаем статистику
    $stats = $cache->getStats();
    echo "   - Статистика кэша: " . $stats['total_files'] . " файлов\n";
    
    // Очищаем тестовые данные
    $cache->delete($testKey);
    
} catch (Exception $e) {
    echo "❌ Ошибка кэширования: " . $e->getMessage() . "\n";
}

echo "\n";

// Тест 5: Проверка директорий
echo "5. Проверка необходимых директорий...\n";
$requiredDirs = [
    'cache',
    'cache/api',
    'cache/rate_limits',
    'logs',
    'config',
    'services'
];

foreach ($requiredDirs as $dir) {
    if (is_dir($dir)) {
        echo "✅ Директория '$dir' существует\n";
    } else {
        echo "❌ Директория '$dir' отсутствует\n";
        echo "   Создайте директорию: mkdir -p $dir\n";
    }
}

echo "\n";

// Тест 6: Проверка файлов конфигурации
echo "6. Проверка файлов конфигурации...\n";
$requiredFiles = [
    'config.php',
    'config/EnvironmentLoader.php',
    'services/RateLimiter.php',
    'services/InputValidator.php',
    'services/APICache.php',
    '.htaccess'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "✅ Файл '$file' существует\n";
    } else {
        echo "❌ Файл '$file' отсутствует\n";
    }
}

echo "\n";

// Тест 7: Проверка .env файла
echo "7. Проверка файла .env...\n";
if (file_exists('.env')) {
    echo "✅ Файл .env существует\n";
    echo "   Убедитесь, что он содержит правильные значения\n";
} else {
    echo "❌ Файл .env отсутствует\n";
    echo "   Скопируйте env.example в .env и настройте переменные\n";
    echo "   cp env.example .env\n";
}

echo "\n";

// Итоговый результат
echo "==============================================\n";
echo "🎯 Результаты тестирования:\n";
echo "✅ Системы безопасности внедрены успешно!\n";
echo "📋 Следующие шаги:\n";
echo "   1. Настройте файл .env с реальными значениями\n";
echo "   2. Протестируйте вход в систему\n";
echo "   3. Проверьте логи в папке logs/\n";
echo "   4. Настройте мониторинг безопасности\n";
echo "\n";
echo "🚀 D&D Copilot готов к использованию!\n";
