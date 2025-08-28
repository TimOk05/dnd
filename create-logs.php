<?php
// Создаем директорию logs если её нет
$logsDir = 'logs';
if (!is_dir($logsDir)) {
    if (mkdir($logsDir, 0755, true)) {
        echo "Директория logs создана успешно<br>";
    } else {
        echo "Ошибка при создании директории logs<br>";
    }
} else {
    echo "Директория logs уже существует<br>";
}

// Создаем файл app.log
$logFile = $logsDir . '/app.log';
$logContent = "# Логи приложения DnD Copilot\n# Создан: " . date('Y-m-d H:i:s') . "\n\n";

$result = file_put_contents($logFile, $logContent);

if ($result !== false) {
    echo "Файл app.log создан успешно!<br>";
    echo "Путь: $logFile<br>";
    echo "Размер: " . $result . " байт<br>";
} else {
    echo "Ошибка при создании файла app.log<br>";
}

// Проверяем права доступа
echo "<br>Проверка прав доступа:<br>";
echo "Директория logs читаемая: " . (is_readable($logsDir) ? 'Да' : 'Нет') . "<br>";
echo "Директория logs записываемая: " . (is_writable($logsDir) ? 'Да' : 'Нет') . "<br>";
echo "Файл app.log читаемый: " . (is_readable($logFile) ? 'Да' : 'Нет') . "<br>";
echo "Файл app.log записываемый: " . (is_writable($logFile) ? 'Да' : 'Нет') . "<br>";
?>
