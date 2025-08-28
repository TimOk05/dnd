<?php
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
        body { font-family: Arial, sans-serif; margin: 20px; background: #f0f0f0; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .stat { background: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #007bff; }
        .success { border-left-color: #28a745; }
        .error { border-left-color: #dc3545; }
        .warning { border-left-color: #ffc107; }
        h1 { color: #333; text-align: center; }
        h3 { color: #007bff; margin-top: 0; }
        .metric { font-size: 18px; font-weight: bold; color: #333; }
        .label { color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class=\"container\">
        <h1>📊 Статистика D&D Copilot</h1>";

// Статистика кэша
try {
    $cache = new APICache();
    $stats = $cache->getStats();
    
    echo "<div class=\"stat success\">
        <h3>💾 Кэш API</h3>
        <div class=\"metric\">" . $stats['total_files'] . "</div>
        <div class=\"label\">Всего файлов</div>
        <div class=\"metric\">" . $stats['valid_files'] . "</div>
        <div class=\"label\">Валидных файлов</div>
        <div class=\"metric\">" . number_format($stats['total_size'] / 1024, 2) . " KB</div>
        <div class=\"label\">Размер кэша</div>
    </div>";
    
} catch (Exception $e) {
    echo "<div class=\"stat error\">
        <h3>❌ Ошибка кэша</h3>
        <p>" . $e->getMessage() . "</p>
    </div>";
}

// Проверка логов
$logFiles = [
    'logs/app.log' => 'Приложение',
    'logs/security.log' => 'Безопасность', 
    'logs/rate_limit.log' => 'Rate Limiting'
];

foreach ($logFiles as $logFile => $label) {
    if (file_exists($logFile)) {
        $size = filesize($logFile);
        $lines = count(file($logFile));
        echo "<div class=\"stat\">
            <h3>📝 Лог: $label</h3>
            <div class=\"metric\">" . number_format($size / 1024, 2) . " KB</div>
            <div class=\"label\">Размер файла</div>
            <div class=\"metric\">$lines</div>
            <div class=\"label\">Количество записей</div>
        </div>";
    } else {
        echo "<div class=\"stat warning\">
            <h3>⚠️ Лог: $label</h3>
            <p>Файл не найден</p>
        </div>";
    }
}

// Проверка систем
echo "<div class=\"stat\">
    <h3>🔧 Системы безопасности</h3>";

$systems = [
    'config/EnvironmentLoader.php' => 'Environment Loader',
    'services/RateLimiter.php' => 'Rate Limiter',
    'services/InputValidator.php' => 'Input Validator',
    'services/APICache.php' => 'API Cache'
];

foreach ($systems as $file => $name) {
    if (file_exists($file)) {
        echo "<div style=\"color: #28a745; margin: 5px 0;\">✅ $name</div>";
    } else {
        echo "<div style=\"color: #dc3545; margin: 5px 0;\">❌ $name</div>";
    }
}

echo "</div>";

// Информация о системе
echo "<div class=\"stat\">
    <h3>ℹ️ Информация о системе</h3>
    <div class=\"metric\">" . phpversion() . "</div>
    <div class=\"label\">Версия PHP</div>
    <div class=\"metric\">" . date('Y-m-d H:i:s') . "</div>
    <div class=\"label\">Текущее время</div>
    <div class=\"metric\">" . memory_get_usage(true) / 1024 / 1024 . " MB</div>
    <div class=\"label\">Использование памяти</div>
</div>";

echo "</div>
</body>
</html>";
?>
