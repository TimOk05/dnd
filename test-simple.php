<?php
session_start();
require_once 'users.php';
require_once 'api/dnd-api.php';

// Проверяем авторизацию
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();

// Тестируем генерацию NPC
$dndApi = new DndApiManager();
$testNpc = $dndApi->generateNPC([
    'race' => 'human',
    'class' => 'fighter',
    'level' => 1,
    'alignment' => 'neutral'
]);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Простой тест NPC - DnD Copilot</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre {
            background: #f8f8f8;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .npc-card {
            background: #e8f4f8;
            border: 2px solid #4a90e2;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
        }
        .npc-name {
            font-size: 1.5em;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .npc-section {
            margin: 8px 0;
        }
        .npc-section strong {
            color: #34495e;
        }
        .back-link {
            display: inline-block;
            background: #4a90e2;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .back-link:hover {
            background: #357abd;
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-link">← Назад к главной</a>
    
    <h1>Простой тест генерации NPC</h1>
    
    <div class="test-section">
        <h2>Тест API доступности</h2>
        <p><a href="api/test-api.php" target="_blank">Открыть полный тест API →</a></p>
    </div>
    
    <div class="test-section">
        <h2>Тест генерации NPC</h2>
        <?php if ($testNpc): ?>
            <p class="success">✅ Генерация NPC работает!</p>
            <div class="npc-card">
                <div class="npc-name"><?php echo htmlspecialchars($testNpc['name']); ?></div>
                
                <div class="npc-section">
                    <strong>Описание:</strong> <?php echo htmlspecialchars($testNpc['description']); ?>
                </div>
                
                <div class="npc-section">
                    <strong>Внешность:</strong> <?php echo htmlspecialchars($testNpc['appearance']); ?>
                </div>
                
                <div class="npc-section">
                    <strong>Черты характера:</strong> <?php echo htmlspecialchars($testNpc['traits']); ?>
                </div>
                
                <div class="npc-section">
                    <strong>Технические параметры:</strong>
                    <ul>
                        <?php foreach ($testNpc['technical_params'] as $param): ?>
                            <li><?php echo htmlspecialchars($param); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <h3>Сырые данные:</h3>
            <pre><?php print_r($testNpc); ?></pre>
        <?php else: ?>
            <p class="error">❌ Ошибка генерации NPC</p>
        <?php endif; ?>
    </div>
    
    <div class="test-section">
        <h2>Быстрые тесты</h2>
        
        <h3>Тест разных рас:</h3>
        <?php
        $races = ['human', 'elf', 'dwarf', 'orc'];
        foreach ($races as $race) {
            $npc = $dndApi->generateNPC(['race' => $race, 'class' => 'fighter', 'level' => 1]);
            if ($npc) {
                echo "<p class='success'>✅ $race: " . htmlspecialchars($npc['name']) . "</p>";
            } else {
                echo "<p class='error'>❌ $race: ошибка</p>";
            }
        }
        ?>
        
        <h3>Тест разных классов:</h3>
        <?php
        $classes = ['fighter', 'wizard', 'rogue', 'barbarian'];
        foreach ($classes as $class) {
            $npc = $dndApi->generateNPC(['race' => 'human', 'class' => $class, 'level' => 1]);
            if ($npc) {
                echo "<p class='success'>✅ $class: " . htmlspecialchars($npc['name']) . "</p>";
            } else {
                echo "<p class='error'>❌ $class: ошибка</p>";
            }
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>Следующие шаги</h2>
        <ul>
            <li>Если тесты проходят успешно, можно переходить к полной интеграции</li>
            <li>Если есть ошибки, нужно проверить логи и исправить проблемы</li>
            <li>Для улучшения качества можно подключить дополнительные API</li>
        </ul>
        
        <p><a href="test-npc-api.php">Перейти к полной тестовой странице →</a></p>
    </div>
</body>
</html>
