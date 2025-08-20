<?php
require_once 'config.php';

// Включаем отображение ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='ru'>
<head>
    <meta charset='UTF-8'>
    <title>Быстрая настройка DnD Copilot</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .btn { display: inline-block; padding: 10px 20px; background: #007cba; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
    </style>
</head>
<body>
    <h1>Быстрая настройка DnD Copilot</h1>";

try {
    echo "<h2>Проверка подключения к MySQL...</h2>";
    
    // Пробуем подключиться к MySQL
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "<p class='success'>✓ Подключение к MySQL успешно</p>";
    
    // Создаем базу данных
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p class='success'>✓ База данных '" . DB_NAME . "' создана</p>";
    
    // Подключаемся к созданной базе данных
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "<h2>Создание таблиц...</h2>";
    
    // Создаем таблицы
    $tables = [
        'users' => "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE,
            role ENUM('user', 'admin') DEFAULT 'user',
            avatar_url VARCHAR(255) NULL,
            preferences JSON NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'user_sessions' => "CREATE TABLE IF NOT EXISTS user_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            session_token VARCHAR(255) UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NOT NULL,
            ip_address VARCHAR(45),
            user_agent TEXT,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'user_notes' => "CREATE TABLE IF NOT EXISTS user_notes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            type ENUM('npc', 'initiative', 'dice', 'general') DEFAULT 'general',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_favorite BOOLEAN DEFAULT FALSE,
            tags JSON NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'user_chat' => "CREATE TABLE IF NOT EXISTS user_chat (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            role ENUM('user', 'assistant', 'system') NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            session_id VARCHAR(100) NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'login_attempts' => "CREATE TABLE IF NOT EXISTS login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            success BOOLEAN DEFAULT FALSE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    
    foreach ($tables as $tableName => $sql) {
        $pdo->exec($sql);
        echo "<p class='success'>✓ Таблица '$tableName' создана</p>";
    }
    
    // Создаем администратора
    echo "<h2>Создание администратора...</h2>";
    
    $adminUsername = 'admin';
    $adminEmail = 'admin@dndcopilot.local';
    $adminPassword = 'Admin123!';
    
    // Проверяем, существует ли уже администратор
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$adminUsername, $adminEmail]);
    
    if (!$stmt->fetch()) {
        $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT, ['cost' => SALT_ROUNDS]);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'admin')");
        $stmt->execute([$adminUsername, $adminEmail, $passwordHash]);
        
        echo "<p class='success'>✓ Администратор создан</p>";
        echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 15px 0;'>
            <strong>Данные для входа:</strong><br>
            Имя пользователя: <strong>$adminUsername</strong><br>
            Email: <strong>$adminEmail</strong><br>
            Пароль: <strong>$adminPassword</strong>
        </div>";
    } else {
        echo "<p class='success'>✓ Администратор уже существует</p>";
    }
    
    echo "<h2>Настройка завершена!</h2>";
    echo "<p class='success'>✓ База данных успешно настроена</p>";
    echo "<p><a href='login.php' class='btn'>Перейти к входу</a></p>";
    echo "<p><a href='index.php' class='btn'>Перейти к приложению</a></p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>✗ Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h3>Возможные решения:</h3>";
    echo "<ul>";
    echo "<li>Проверьте, что MySQL сервер запущен</li>";
    echo "<li>Попробуйте другой пароль в config.php</li>";
    echo "<li>Убедитесь, что пользователь 'root' имеет права на создание баз данных</li>";
    echo "</ul>";
    echo "<h3>Попробуйте эти пароли в config.php:</h3>";
    echo "<ul>";
    echo "<li>define('DB_PASS', ''); // Пустой пароль</li>";
    echo "<li>define('DB_PASS', 'root'); // Пароль 'root'</li>";
    echo "<li>define('DB_PASS', 'password'); // Пароль 'password'</li>";
    echo "<li>define('DB_PASS', 'admin'); // Пароль 'admin'</li>";
    echo "</ul>";
}

echo "</body></html>";
?>
