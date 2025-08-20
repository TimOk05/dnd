<?php
// Диагностическая страница для выявления проблем
echo "<h1>🔍 Диагностика системы авторизации</h1>";

// Проверяем PHP версию
echo "<h2>1. Версия PHP</h2>";
echo "<p>Версия PHP: " . phpversion() . "</p>";

// Проверяем необходимые функции
echo "<h2>2. Проверка функций</h2>";
$functions = ['password_hash', 'password_verify', 'json_encode', 'json_decode', 'session_start'];
foreach ($functions as $func) {
    if (function_exists($func)) {
        echo "<p>✅ $func() - доступна</p>";
    } else {
        echo "<p>❌ $func() - НЕ доступна</p>";
    }
}

// Проверяем права доступа
echo "<h2>3. Права доступа</h2>";
if (is_writable('.')) {
    echo "<p>✅ Папка доступна для записи</p>";
} else {
    echo "<p>❌ Папка НЕ доступна для записи</p>";
}

// Проверяем файлы
echo "<h2>4. Проверка файлов</h2>";
$files = ['users.php', 'login.php', 'index.php', 'config.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p>✅ $file - существует</p>";
    } else {
        echo "<p>❌ $file - НЕ существует</p>";
    }
}

// Проверяем файл пользователей
echo "<h2>5. Файл пользователей</h2>";
if (file_exists('users.json')) {
    $content = file_get_contents('users.json');
    $users = json_decode($content, true);
    if ($users === null) {
        echo "<p>❌ users.json содержит неверный JSON</p>";
    } else {
        echo "<p>✅ users.json содержит " . count($users) . " пользователей</p>";
        foreach ($users as $user) {
            echo "<p>- " . htmlspecialchars($user['username']) . " (создан: " . $user['created_at'] . ")</p>";
        }
    }
} else {
    echo "<p>ℹ️ users.json не существует (будет создан при первой регистрации)</p>";
}

// Тестируем функции
echo "<h2>6. Тест функций</h2>";
try {
    require_once 'users.php';
    echo "<p>✅ users.php загружен успешно</p>";
    
    // Тестируем загрузку пользователей
    $test_users = loadUsers();
    echo "<p>✅ loadUsers() работает, загружено " . count($test_users) . " пользователей</p>";
    
    // Тестируем хеширование
    $test_hash = password_hash('test123', PASSWORD_DEFAULT);
    if (password_verify('test123', $test_hash)) {
        echo "<p>✅ Хеширование паролей работает</p>";
    } else {
        echo "<p>❌ Проблема с хешированием паролей</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Ошибка при загрузке users.php: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Проверяем сессии
echo "<h2>7. Проверка сессий</h2>";
session_start();
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p>✅ Сессии работают</p>";
    echo "<p>ID сессии: " . session_id() . "</p>";
} else {
    echo "<p>❌ Проблема с сессиями</p>";
}

// Проверяем конфигурацию
echo "<h2>8. Проверка config.php</h2>";
if (file_exists('config.php')) {
    $config_content = file_get_contents('config.php');
    if (strpos($config_content, 'DB_HOST') !== false) {
        echo "<p>⚠️ config.php содержит настройки базы данных (это может вызывать ошибки)</p>";
    } else {
        echo "<p>✅ config.php не содержит настроек БД</p>";
    }
} else {
    echo "<p>ℹ️ config.php не существует</p>";
}

// Тест простой регистрации
echo "<h2>9. Тест регистрации</h2>";
echo "<form method='post' action='users.php'>";
echo "<input type='hidden' name='action' value='register'>";
echo "<p>Имя пользователя: <input type='text' name='username' value='test_user' required></p>";
echo "<p>Пароль: <input type='password' name='password' value='test123' required></p>";
echo "<button type='submit'>Тест регистрации</button>";
echo "</form>";

echo "<h2>10. Ссылки для тестирования</h2>";
echo "<p><a href='login.php'>Страница входа/регистрации</a></p>";
echo "<p><a href='test_auth.php'>Тест авторизации</a></p>";
echo "<p><a href='index.php'>Главная страница</a></p>";

echo "<h2>11. Информация о сервере</h2>";
echo "<p>Сервер: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Неизвестно') . "</p>";
echo "<p>Документ рут: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Неизвестно') . "</p>";
echo "<p>Текущая папка: " . getcwd() . "</p>";
?>
