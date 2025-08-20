<?php
require_once 'config.php';

// Проверяем, что файл запущен напрямую
if (basename($_SERVER['SCRIPT_NAME']) !== 'setup.php') {
    die('Доступ запрещен');
}

echo "<!DOCTYPE html>
<html lang='ru'>
<head>
    <meta charset='UTF-8'>
    <title>Настройка DnD Copilot</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { color: blue; }
        pre {
            background: #f8f8f8;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Настройка DnD Copilot</h1>";

try {
    echo "<h2>Проверка подключения к базе данных...</h2>";
    
    // Проверяем подключение к MySQL
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
    
    // Создаем базу данных, если она не существует
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p class='success'>✓ База данных '" . DB_NAME . "' создана или уже существует</p>";
    
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
        'users' => "
            CREATE TABLE IF NOT EXISTS users (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'user_sessions' => "
            CREATE TABLE IF NOT EXISTS user_sessions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                session_token VARCHAR(255) UNIQUE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NOT NULL,
                ip_address VARCHAR(45),
                user_agent TEXT,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'user_notes' => "
            CREATE TABLE IF NOT EXISTS user_notes (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'user_chat' => "
            CREATE TABLE IF NOT EXISTS user_chat (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                role ENUM('user', 'assistant', 'system') NOT NULL,
                content TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                session_id VARCHAR(100) NOT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'login_attempts' => "
            CREATE TABLE IF NOT EXISTS login_attempts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                success BOOLEAN DEFAULT FALSE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        "
    ];
    
    foreach ($tables as $tableName => $sql) {
        $pdo->exec($sql);
        echo "<p class='success'>✓ Таблица '$tableName' создана</p>";
    }
    
    // Создаем администратора по умолчанию
    echo "<h2>Создание администратора по умолчанию...</h2>";
    
    $adminUsername = 'admin';
    $adminEmail = 'admin@dndcopilot.local';
    $adminPassword = 'Admin123!';
    
    // Проверяем, существует ли уже администратор
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$adminUsername, $adminEmail]);
    
    if (!$stmt->fetch()) {
        $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT, ['cost' => SALT_ROUNDS]);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, role) 
            VALUES (?, ?, ?, 'admin')
        ");
        $stmt->execute([$adminUsername, $adminEmail, $passwordHash]);
        
        echo "<p class='success'>✓ Администратор создан</p>";
        echo "<div class='info'>
            <strong>Данные для входа:</strong><br>
            Имя пользователя: <strong>$adminUsername</strong><br>
            Email: <strong>$adminEmail</strong><br>
            Пароль: <strong>$adminPassword</strong><br>
            <em>Рекомендуется сменить пароль после первого входа!</em>
        </div>";
    } else {
        echo "<p class='warning'>⚠ Администратор уже существует</p>";
    }
    
    echo "<h2>Настройка завершена!</h2>";
    echo "<p class='success'>✓ База данных успешно настроена</p>";
    echo "<p><a href='login.php'>Перейти к странице входа</a></p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>✗ Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h3>Проверьте настройки в config.php:</h3>";
    echo "<pre>
DB_HOST = " . DB_HOST . "
DB_NAME = " . DB_NAME . "
DB_USER = " . DB_USER . "
DB_PASS = " . (DB_PASS ? '***' : '(пустой)') . "
    </pre>";
    echo "<p class='warning'>Убедитесь, что:</p>";
    echo "<ul>";
    echo "<li>MySQL сервер запущен</li>";
    echo "<li>Пользователь базы данных существует и имеет права на создание баз данных</li>";
    echo "<li>Настройки подключения в config.php корректны</li>";
    echo "</ul>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Неожиданная ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div></body></html>";
?>
