<?php
require_once 'config.php';

// Включаем отображение ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Статус системы - DnD Copilot</title>
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
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .info { color: blue; }
        .section {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .section h3 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        pre {
            background: #f8f8f8;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 12px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007cba;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
        .btn:hover {
            background: #005a87;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Статус системы DnD Copilot</h1>
        
        <!-- Проверка PHP -->
        <div class="section">
            <h3>Проверка PHP</h3>
            <p><span class="success">✓</span> Версия PHP: <?php echo PHP_VERSION; ?></p>
            <p><span class="success">✓</span> PDO: <?php echo extension_loaded('pdo') ? 'Доступно' : 'Недоступно'; ?></p>
            <p><span class="success">✓</span> PDO MySQL: <?php echo extension_loaded('pdo_mysql') ? 'Доступно' : 'Недоступно'; ?></p>
            <p><span class="success">✓</span> JSON: <?php echo extension_loaded('json') ? 'Доступно' : 'Недоступно'; ?></p>
            <p><span class="success">✓</span> cURL: <?php echo extension_loaded('curl') ? 'Доступно' : 'Недоступно'; ?></p>
        </div>
        
        <!-- Проверка конфигурации -->
        <div class="section">
            <h3>Конфигурация</h3>
            <p><span class="info">DB_HOST:</span> <?php echo DB_HOST; ?></p>
            <p><span class="info">DB_NAME:</span> <?php echo DB_NAME; ?></p>
            <p><span class="info">DB_USER:</span> <?php echo DB_USER; ?></p>
            <p><span class="info">DB_PASS:</span> <?php echo DB_PASS ? '***' : '(пустой)'; ?></p>
            <p><span class="info">DEBUG_MODE:</span> <?php echo DEBUG_MODE ? 'Включен' : 'Выключен'; ?></p>
        </div>
        
        <!-- Проверка подключения к базе данных -->
        <div class="section">
            <h3>Подключение к базе данных</h3>
            <?php
            try {
                $pdo = new PDO(
                    "mysql:host=" . DB_HOST . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
                echo '<p><span class="success">✓</span> Подключение к MySQL успешно</p>';
                
                // Проверяем существование базы данных
                $stmt = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
                if ($stmt->rowCount() > 0) {
                    echo '<p><span class="success">✓</span> База данных "' . DB_NAME . '" существует</p>';
                    
                    // Подключаемся к базе данных
                    $pdo = new PDO(
                        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                        DB_USER,
                        DB_PASS,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                        ]
                    );
                    
                    // Проверяем таблицы
                    $tables = ['users', 'user_sessions', 'user_notes', 'user_chat', 'login_attempts'];
                    foreach ($tables as $table) {
                        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                        if ($stmt->rowCount() > 0) {
                            echo '<p><span class="success">✓</span> Таблица "' . $table . '" существует</p>';
                        } else {
                            echo '<p><span class="error">✗</span> Таблица "' . $table . '" отсутствует</p>';
                        }
                    }
                    
                    // Проверяем наличие администратора
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
                    $result = $stmt->fetch();
                    if ($result['count'] > 0) {
                        echo '<p><span class="success">✓</span> Администратор найден</p>';
                    } else {
                        echo '<p><span class="warning">⚠</span> Администратор не найден</p>';
                    }
                    
                } else {
                    echo '<p><span class="error">✗</span> База данных "' . DB_NAME . '" не существует</p>';
                }
                
            } catch (PDOException $e) {
                echo '<p><span class="error">✗</span> Ошибка подключения: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            ?>
        </div>
        
        <!-- Проверка файлов -->
        <div class="section">
            <h3>Проверка файлов</h3>
            <?php
            $requiredFiles = [
                'config.php',
                'auth.php',
                'login.php',
                'profile.php',
                'setup.php',
                'template.html',
                'ai.php'
            ];
            
            foreach ($requiredFiles as $file) {
                if (file_exists($file)) {
                    echo '<p><span class="success">✓</span> ' . $file . '</p>';
                } else {
                    echo '<p><span class="error">✗</span> ' . $file . ' - отсутствует</p>';
                }
            }
            ?>
        </div>
        
        <!-- Проверка API -->
        <div class="section">
            <h3>Проверка API ключа</h3>
            <?php
            $apiKey = getApiKey('deepseek');
            if ($apiKey && $apiKey !== 'your-deepseek-api-key-here') {
                echo '<p><span class="success">✓</span> API ключ DeepSeek настроен</p>';
            } else {
                echo '<p><span class="warning">⚠</span> API ключ DeepSeek не настроен</p>';
            }
            ?>
        </div>
        
        <!-- Действия -->
        <div class="section">
            <h3>Действия</h3>
            <a href="setup.php" class="btn">Настроить базу данных</a>
            <a href="login.php" class="btn">Страница входа</a>
            <a href="index.php" class="btn">Главная страница</a>
        </div>
        
        <!-- Рекомендации -->
        <div class="section">
            <h3>Рекомендации</h3>
            <?php
            if (!extension_loaded('pdo_mysql')) {
                echo '<p><span class="error">✗</span> Установите расширение PDO MySQL</p>';
            }
            
            if (!extension_loaded('curl')) {
                echo '<p><span class="error">✗</span> Установите расширение cURL для работы с API</p>';
            }
            
            if (DEBUG_MODE) {
                echo '<p><span class="warning">⚠</span> Режим отладки включен. Отключите его в продакшене.</p>';
            }
            ?>
        </div>
    </div>
</body>
</html>
