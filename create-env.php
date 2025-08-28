<?php
$envContent = "# API ключи
DEEPSEEK_API_KEY=sk-1e898ddba737411e948af435d767e893

# Настройки приложения
DEBUG_MODE=false
ENVIRONMENT=production

# Настройки безопасности
SESSION_LIFETIME=28800
MAX_LOGIN_ATTEMPTS=5
LOCKOUT_TIME=900
PASSWORD_MIN_LENGTH=8";

$result = file_put_contents('.env', $envContent);

if ($result !== false) {
    echo "Файл .env создан успешно!<br>";
    echo "Размер файла: " . $result . " байт<br>";
    echo "<br>Содержимое файла:<br>";
    echo "<pre>" . htmlspecialchars($envContent) . "</pre>";
} else {
    echo "Ошибка при создании файла .env<br>";
    echo "Проверьте права доступа к директории";
}
?>
