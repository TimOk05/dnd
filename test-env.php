<?php
echo "<h1>Тест загрузки .env файла</h1>";

// Проверяем существование файла
echo "<h2>1. Проверка файла .env</h2>";
$envFile = __DIR__ . '/.env';
echo "Путь к файлу: $envFile<br>";
echo "Файл существует: " . (file_exists($envFile) ? 'Да' : 'Нет') . "<br>";
echo "Файл читаемый: " . (is_readable($envFile) ? 'Да' : 'Нет') . "<br>";

if (file_exists($envFile)) {
    echo "<h3>Содержимое файла:</h3>";
    echo "<pre>" . htmlspecialchars(file_get_contents($envFile)) . "</pre>";
}

// Тестируем функцию loadEnv
echo "<h2>2. Тест функции loadEnv</h2>";
function loadEnv($file) {
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) continue; // Пропускаем комментарии
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                if (!getenv($key)) {
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                    echo "Установлена переменная: $key = $value<br>";
                }
            }
        }
    }
}

loadEnv($envFile);

// Проверяем переменные
echo "<h2>3. Проверка переменных</h2>";
echo "DEEPSEEK_API_KEY: " . (getenv('DEEPSEEK_API_KEY') ?: 'НЕ УСТАНОВЛЕНА') . "<br>";
echo "DEBUG_MODE: " . (getenv('DEBUG_MODE') ?: 'НЕ УСТАНОВЛЕНА') . "<br>";
echo "ENVIRONMENT: " . (getenv('ENVIRONMENT') ?: 'НЕ УСТАНОВЛЕНА') . "<br>";

// Проверяем $_ENV
echo "<h2>4. Проверка \$_ENV</h2>";
echo "<pre>" . print_r($_ENV, true) . "</pre>";
?>
