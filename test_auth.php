<?php
// Тестовый скрипт для проверки системы авторизации
require_once 'users.php';

echo "<h1>Тест системы авторизации</h1>";

// Проверяем, авторизован ли пользователь
if (isLoggedIn()) {
    echo "<p>✅ Пользователь авторизован: " . getCurrentUser() . "</p>";
    echo "<p><a href='index.php'>Перейти к приложению</a></p>";
    echo "<p><a href='users.php?action=logout'>Выйти</a></p>";
} else {
    echo "<p>❌ Пользователь не авторизован</p>";
    echo "<p><a href='login.php'>Перейти к странице входа</a></p>";
}

// Проверяем файл пользователей
$users_file = 'users.json';
if (file_exists($users_file)) {
    $users = loadUsers();
    echo "<h2>Зарегистрированные пользователи:</h2>";
    if (empty($users)) {
        echo "<p>Нет зарегистрированных пользователей</p>";
    } else {
        echo "<ul>";
        foreach ($users as $user) {
            echo "<li>" . htmlspecialchars($user['username']) . " (создан: " . $user['created_at'] . ")</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p>Файл пользователей не существует (будет создан при первой регистрации)</p>";
}

// Проверяем права доступа
echo "<h2>Проверка прав доступа:</h2>";
if (is_writable('.')) {
    echo "<p>✅ Папка доступна для записи</p>";
} else {
    echo "<p>❌ Папка недоступна для записи</p>";
}

if (function_exists('password_hash')) {
    echo "<p>✅ Функция password_hash() доступна</p>";
} else {
    echo "<p>❌ Функция password_hash() недоступна</p>";
}

if (function_exists('json_encode')) {
    echo "<p>✅ Функция json_encode() доступна</p>";
} else {
    echo "<p>❌ Функция json_encode() недоступна</p>";
}

echo "<h2>Ссылки:</h2>";
echo "<p><a href='login.php'>Страница входа/регистрации</a></p>";
echo "<p><a href='index.php'>Главная страница</a></p>";
?>
