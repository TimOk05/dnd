<?php
require_once 'config.php';
require_once 'users.php';

// Только для администратора
if (!isLoggedIn() || !function_exists('hasRole') || !hasRole('admin')) {
    die('Доступ запрещен');
}

echo "<h1>Тест безопасности DnD Copilot</h1>";

// Тест 1: Проверка конфигурации
echo "<h2>1. Проверка конфигурации</h2>";
echo "<ul>";
echo "<li>DEBUG_MODE: " . (DEBUG_MODE ? 'ВКЛ' : 'ВЫКЛ') . "</li>";
echo "<li>ENVIRONMENT: " . ENVIRONMENT . "</li>";
echo "<li>SESSION_LIFETIME: " . SESSION_LIFETIME . " секунд</li>";
echo "<li>MAX_LOGIN_ATTEMPTS: " . MAX_LOGIN_ATTEMPTS . "</li>";
echo "<li>LOCKOUT_TIME: " . LOCKOUT_TIME . " секунд</li>";
echo "<li>PASSWORD_MIN_LENGTH: " . PASSWORD_MIN_LENGTH . "</li>";
echo "</ul>";

// Тест 2: Проверка API ключей
echo "<h2>2. Проверка API ключей</h2>";
$deepseek_key = getApiKey('deepseek');
echo "<ul>";
echo "<li>DeepSeek API Key: " . ($deepseek_key ? 'Настроен' : 'НЕ НАСТРОЕН') . "</li>";
if ($deepseek_key) {
    echo "<li>Длина ключа: " . strlen($deepseek_key) . " символов</li>";
    echo "<li>Начинается с: " . substr($deepseek_key, 0, 10) . "...</li>";
}
echo "</ul>";

// Тест 3: Проверка файлов безопасности
echo "<h2>3. Проверка файлов безопасности</h2>";
$files_to_check = [
    '.env' => 'Файл переменных окружения',
    'users.json' => 'Файл пользователей',
    'login_attempts.json' => 'Файл попыток входа',
    '.htaccess' => 'Файл конфигурации Apache',
    'logs/app.log' => 'Файл логов'
];

echo "<ul>";
foreach ($files_to_check as $file => $description) {
    $exists = file_exists($file);
    $readable = is_readable($file);
    $writable = is_writable($file);
    
    echo "<li><strong>$description ($file):</strong>";
    echo "<ul>";
    echo "<li>Существует: " . ($exists ? 'Да' : 'Нет') . "</li>";
    if ($exists) {
        echo "<li>Читаемый: " . ($readable ? 'Да' : 'Нет') . "</li>";
        echo "<li>Записываемый: " . ($writable ? 'Да' : 'Нет') . "</li>";
        if ($file === 'logs/app.log') {
            $size = filesize($file);
            echo "<li>Размер: " . ($size ? $size . ' байт' : 'Пустой') . "</li>";
        }
    }
    echo "</ul></li>";
}
echo "</ul>";

// Тест 4: Проверка сессии
echo "<h2>4. Проверка сессии</h2>";
echo "<ul>";
echo "<li>ID сессии: " . session_id() . "</li>";
echo "<li>Время создания: " . ($_SESSION['created_at'] ?? 'Не установлено') . "</li>";
echo "<li>IP адрес: " . ($_SESSION['ip'] ?? 'Не установлено') . "</li>";
echo "<li>Текущий IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Неизвестно') . "</li>";
echo "<li>Время жизни: " . (time() - ($_SESSION['created_at'] ?? time())) . " секунд</li>";
echo "</ul>";

// Тест 5: Проверка функций безопасности
echo "<h2>5. Проверка функций безопасности</h2>";
echo "<ul>";
echo "<li>Функция sanitizeInput: " . (function_exists('sanitizeInput') ? 'Доступна' : 'НЕ ДОСТУПНА') . "</li>";
echo "<li>Функция generateCSRFToken: " . (function_exists('generateCSRFToken') ? 'Доступна' : 'НЕ ДОСТУПНА') . "</li>";
echo "<li>Функция verifyCSRFToken: " . (function_exists('verifyCSRFToken') ? 'Доступна' : 'НЕ ДОСТУПНА') . "</li>";
echo "<li>Функция logMessage: " . (function_exists('logMessage') ? 'Доступна' : 'НЕ ДОСТУПНА') . "</li>";
echo "</ul>";

// Тест 6: Проверка блокировки IP
echo "<h2>6. Проверка блокировки IP</h2>";
$current_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$is_blocked = isIPBlocked($current_ip);
echo "<ul>";
echo "<li>Текущий IP: $current_ip</li>";
echo "<li>Заблокирован: " . ($is_blocked ? 'Да' : 'Нет') . "</li>";
echo "</ul>";

// Тест 7: Проверка пользователей
echo "<h2>7. Проверка пользователей</h2>";
$users = loadUsers();
echo "<ul>";
echo "<li>Количество пользователей: " . count($users) . "</li>";
if (!empty($users)) {
    echo "<li>Пользователи:";
    echo "<ul>";
    foreach ($users as $username => $user_data) {
        $has_admin = isset($user_data['roles']) && in_array('admin', $user_data['roles']);
        echo "<li>$username" . ($has_admin ? ' (админ)' : '') . "</li>";
    }
    echo "</ul></li>";
}
echo "</ul>";

// Тест 8: Проверка CSRF токена
echo "<h2>8. Проверка CSRF токена</h2>";
$csrf_token = generateCSRFToken();
echo "<ul>";
echo "<li>CSRF токен: " . substr($csrf_token, 0, 20) . "...</li>";
echo "<li>Проверка токена: " . (verifyCSRFToken($csrf_token) ? 'Прошла' : 'НЕ ПРОШЛА') . "</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>Тест завершен.</strong> Если все проверки прошли успешно, система настроена правильно.</p>";
echo "<p><a href='index.php'>Вернуться на главную</a></p>";
?>
