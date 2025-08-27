<?php
require_once 'config.php';
require_once 'users.php';

// Запускаем сессию
configureSession();

echo "<h1>Тест отладки</h1>";

echo "<h2>Проверка сессии:</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Session data: <pre>" . print_r($_SESSION, true) . "</pre>";

echo "<h2>Проверка авторизации:</h2>";
echo "isLoggedIn(): " . (isLoggedIn() ? 'true' : 'false') . "<br>";
echo "getCurrentUser(): " . (getCurrentUser() ?: 'null') . "<br>";
echo "getCurrentUserData(): <pre>" . print_r(getCurrentUserData(), true) . "</pre>";

echo "<h2>Проверка пользователей:</h2>";
$users = loadUsers();
echo "Количество пользователей: " . count($users) . "<br>";
echo "Пользователи: <pre>" . print_r($users, true) . "</pre>";

echo "<h2>Проверка файлов:</h2>";
echo "users.json существует: " . (file_exists('users.json') ? 'да' : 'нет') . "<br>";
echo "login_attempts.json существует: " . (file_exists('login_attempts.json') ? 'да' : 'нет') . "<br>";

echo "<h2>Проверка констант:</h2>";
echo "MAX_LOGIN_ATTEMPTS: " . MAX_LOGIN_ATTEMPTS . "<br>";
echo "LOCKOUT_TIME: " . LOCKOUT_TIME . "<br>";
echo "PASSWORD_MIN_LENGTH: " . PASSWORD_MIN_LENGTH . "<br>";
?>
