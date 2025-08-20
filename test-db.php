<?php
// Включаем отображение ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Тест подключения к базе данных</h1>";

// Пробуем разные варианты подключения
$hosts = ['localhost', '127.0.0.1'];
$users = ['root'];
$passwords = ['', 'root', 'password', 'admin'];

foreach ($hosts as $host) {
    foreach ($users as $user) {
        foreach ($passwords as $password) {
            echo "<h3>Тестируем: $host, $user, пароль: " . ($password ?: 'пустой') . "</h3>";
            
            try {
                $pdo = new PDO(
                    "mysql:host=$host;charset=utf8mb4",
                    $user,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
                
                echo "<p style='color: green;'>✓ Успешное подключение!</p>";
                
                // Пробуем создать базу данных
                try {
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS dnd_copilot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    echo "<p style='color: green;'>✓ База данных создана!</p>";
                    
                    // Подключаемся к созданной базе данных
                    $pdo = new PDO(
                        "mysql:host=$host;dbname=dnd_copilot;charset=utf8mb4",
                        $user,
                        $password,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                        ]
                    );
                    
                    echo "<p style='color: green;'>✓ Подключение к базе данных успешно!</p>";
                    echo "<p><strong>Рабочие настройки:</strong></p>";
                    echo "<pre>";
                    echo "define('DB_HOST', '$host');\n";
                    echo "define('DB_NAME', 'dnd_copilot');\n";
                    echo "define('DB_USER', '$user');\n";
                    echo "define('DB_PASS', " . ($password ? "'$password'" : "''") . ");\n";
                    echo "</pre>";
                    
                    // Создаем тестовую таблицу
                    $pdo->exec("
                        CREATE TABLE IF NOT EXISTS test_table (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            name VARCHAR(50) NOT NULL
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                    ");
                    echo "<p style='color: green;'>✓ Таблица создана!</p>";
                    
                    // Удаляем тестовую таблицу
                    $pdo->exec("DROP TABLE test_table");
                    echo "<p style='color: green;'>✓ Тестовая таблица удалена!</p>";
                    
                    echo "<p style='color: green; font-weight: bold;'>🎉 ВСЕ РАБОТАЕТ! Используйте эти настройки в config.php</p>";
                    break 3; // Выходим из всех циклов
                    
                } catch (PDOException $e) {
                    echo "<p style='color: orange;'>⚠ Ошибка создания базы данных: " . $e->getMessage() . "</p>";
                }
                
            } catch (PDOException $e) {
                echo "<p style='color: red;'>✗ Ошибка подключения: " . $e->getMessage() . "</p>";
            }
        }
    }
}

echo "<h2>Если ничего не работает:</h2>";
echo "<ul>";
echo "<li>Проверьте, что MySQL сервер запущен</li>";
echo "<li>Проверьте права доступа пользователя root</li>";
echo "<li>Попробуйте создать пользователя MySQL вручную</li>";
echo "</ul>";
?>
