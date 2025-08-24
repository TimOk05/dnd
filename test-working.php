<?php
session_start();
require_once 'users.php';
require_once 'api/dnd-api-working.php';

// Проверяем авторизацию
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();

// Тестируем генерацию NPC
$dndApi = new DndApiWorking();
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
    <title>Рабочая генерация NPC - DnD Copilot</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=UnifrakturCook:wght@700&family=IM+Fell+English+SC&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #f8ecd0;
            --bg-secondary: #fffbe6;
            --bg-tertiary: #f3e1b6;
            --text-primary: #2d1b00;
            --text-secondary: #3d2a0a;
            --text-tertiary: #7c4a02;
            --border-primary: #a67c52;
            --accent-primary: #a67c52;
            --accent-success: #2bb07b;
            --accent-info: #4a90e2;
            --accent-warning: #ffd700;
            --accent-danger: #b71c1c;
            --shadow-primary: #0002;
            --shadow-secondary: #0006;
        }
        
        body {
            background: var(--bg-primary);
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 20px;
            color: var(--text-primary);
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px var(--shadow-secondary);
            border: 2px solid var(--border-primary);
        }
        
        h1 {
            text-align: center;
            font-family: 'UnifrakturCook', cursive;
            font-size: 2.5em;
            color: var(--text-tertiary);
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px var(--shadow-primary);
        }
        
        .back-link {
            display: inline-block;
            background: var(--accent-primary);
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            background: var(--accent-info);
            transform: translateY(-2px);
        }
        
        .test-section {
            background: var(--bg-tertiary);
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border: 1px solid var(--border-primary);
        }
        
        .success { color: var(--accent-success); font-weight: bold; }
        .error { color: var(--accent-danger); font-weight: bold; }
        .warning { color: var(--accent-warning); font-weight: bold; }
        
        .npc-card {
            background: var(--bg-secondary);
            border: 2px solid var(--accent-info);
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
            box-shadow: 0 2px 10px var(--shadow-primary);
        }
        
        .npc-name {
            font-size: 1.8em;
            font-weight: bold;
            color: var(--text-tertiary);
            margin-bottom: 15px;
            text-align: center;
        }
        
        .npc-section {
            margin: 12px 0;
            padding: 8px 0;
            border-bottom: 1px solid var(--border-primary);
        }
        
        .npc-section:last-child {
            border-bottom: none;
        }
        
        .npc-section strong {
            color: var(--text-tertiary);
            display: block;
            margin-bottom: 5px;
        }
        
        .npc-section ul {
            margin: 5px 0;
            padding-left: 20px;
        }
        
        .npc-section li {
            margin: 3px 0;
        }
        
        .api-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .api-status.working {
            background: var(--accent-success);
            color: white;
        }
        
        .api-status.error {
            background: var(--accent-danger);
            color: white;
        }
        
        .generate-form {
            background: var(--bg-tertiary);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--text-secondary);
            font-weight: 600;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid var(--border-primary);
            border-radius: 6px;
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-size: 1em;
        }
        
        .generate-btn {
            background: var(--accent-success);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .generate-btn:hover {
            background: var(--accent-info);
            transform: translateY(-2px);
        }
        
        .generate-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        pre {
            background: var(--bg-primary);
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            font-size: 0.9em;
            border: 1px solid var(--border-primary);
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">← Назад к главной</a>
        
        <h1>Рабочая генерация NPC</h1>
        
        <div class="test-section">
            <h2>Статус API <span class="api-status <?php echo $testNpc ? 'working' : 'error'; ?>">
                <?php echo $testNpc ? 'Работает' : 'Ошибка'; ?>
            </span></h2>
            <p>Система использует реальные D&D 5e API для генерации персонажей. Никакой локальной генерации - только официальные данные.</p>
        </div>
        
        <div class="test-section">
            <h2>Тестовая генерация NPC</h2>
            <?php if ($testNpc): ?>
                <p class="success">✅ Генерация NPC работает с реальными API!</p>
                <div class="npc-card">
                    <div class="npc-name"><?php echo htmlspecialchars($testNpc['name']); ?></div>
                    
                    <div class="npc-section">
                        <strong>Раса и класс:</strong>
                        <?php echo htmlspecialchars($testNpc['race']); ?> - <?php echo htmlspecialchars($testNpc['class']); ?> (уровень <?php echo $testNpc['level']; ?>)
                    </div>
                    
                                         <div class="npc-section">
                         <strong>Описание:</strong>
                         <?php echo htmlspecialchars($testNpc['description']); ?>
                     </div>
                     
                     <div class="npc-section">
                         <strong>Внешность:</strong>
                         <?php echo htmlspecialchars($testNpc['appearance']); ?>
                     </div>
                     
                     <div class="npc-section">
                         <strong>Профессия:</strong>
                         <?php echo htmlspecialchars($testNpc['profession']); ?>
                     </div>
                     
                     <div class="npc-section">
                         <strong>Технические параметры:</strong>
                         <ul>
                             <?php foreach ($testNpc['technical_params'] as $param): ?>
                                 <li><?php echo htmlspecialchars($param); ?></li>
                             <?php endforeach; ?>
                         </ul>
                     </div>
                     
                     <?php if (!empty($testNpc['spells'])): ?>
                     <div class="npc-section">
                         <strong>Заклинания:</strong>
                         <?php foreach ($testNpc['spells'] as $level => $spells): ?>
                             <div style="margin: 8px 0;">
                                 <strong><?php echo $level === 'cantrips' ? 'Заговоры (0 уровень)' : 'Уровень ' . str_replace('level_', '', $level); ?>:</strong>
                                 <ul>
                                     <?php foreach ($spells as $spell): ?>
                                         <li><?php echo htmlspecialchars($spell); ?></li>
                                     <?php endforeach; ?>
                                 </ul>
                             </div>
                         <?php endforeach; ?>
                     </div>
                     <?php endif; ?>
                     
                     <div class="npc-section">
                         <strong>Источник данных:</strong>
                         <?php echo htmlspecialchars($testNpc['api_source']); ?>
                     </div>
                </div>
            <?php else: ?>
                <p class="error">❌ Ошибка генерации NPC - API недоступен</p>
                <p>Проверьте подключение к интернету и доступность D&D 5e API.</p>
            <?php endif; ?>
        </div>
        
        <div class="test-section">
            <h2>Интерактивная генерация</h2>
            <div class="generate-form">
                <form id="npcForm">
                    <div class="form-group">
                        <label for="race">Раса:</label>
                        <select name="race" id="race" required>
                            <option value="human">Человек</option>
                            <option value="elf">Эльф</option>
                            <option value="dwarf">Дварф</option>
                            <option value="halfling">Полурослик</option>
                            <option value="orc">Орк</option>
                            <option value="tiefling">Тифлинг</option>
                            <option value="dragonborn">Драконорожденный</option>
                            <option value="gnome">Гном</option>
                            <option value="half-elf">Полуэльф</option>
                            <option value="half-orc">Полуорк</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="class">Класс:</label>
                        <select name="class" id="class" required>
                            <option value="fighter">Воин</option>
                            <option value="wizard">Волшебник</option>
                            <option value="rogue">Плут</option>
                            <option value="cleric">Жрец</option>
                            <option value="ranger">Следопыт</option>
                            <option value="barbarian">Варвар</option>
                            <option value="bard">Бард</option>
                            <option value="druid">Друид</option>
                            <option value="monk">Монах</option>
                            <option value="paladin">Паладин</option>
                            <option value="sorcerer">Сорсерер</option>
                            <option value="warlock">Колдун</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="level">Уровень:</label>
                        <input type="number" name="level" id="level" min="1" max="20" value="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="alignment">Мировоззрение:</label>
                        <select name="alignment" id="alignment" required>
                            <option value="lawful good">Законно-добрый</option>
                            <option value="neutral good">Нейтрально-добрый</option>
                            <option value="chaotic good">Хаотично-добрый</option>
                            <option value="lawful neutral">Законно-нейтральный</option>
                            <option value="neutral">Нейтральный</option>
                            <option value="chaotic neutral">Хаотично-нейтральный</option>
                            <option value="lawful evil">Законно-злой</option>
                            <option value="neutral evil">Нейтрально-злой</option>
                            <option value="chaotic evil">Хаотично-злой</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="generate-btn">Сгенерировать NPC</button>
                </form>
                
                <div id="npcResult"></div>
            </div>
        </div>
        
        <?php if ($testNpc): ?>
        <div class="test-section">
            <h2>Сырые данные API</h2>
            <pre><?php print_r($testNpc); ?></pre>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        document.getElementById('npcForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('.generate-btn');
            const resultDiv = document.getElementById('npcResult');
            
            submitBtn.textContent = 'Генерация...';
            submitBtn.disabled = true;
            resultDiv.innerHTML = '<p>Генерация NPC...</p>';
            
            fetch('api/generate-npc-working.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.npc) {
                    const npc = data.npc;
                    resultDiv.innerHTML = `
                        <div class="npc-card">
                            <div class="npc-name">${npc.name}</div>
                            
                            <div class="npc-section">
                                <strong>Раса и класс:</strong>
                                ${npc.race} - ${npc.class} (уровень ${npc.level})
                            </div>
                            
                                                         <div class="npc-section">
                                 <strong>Описание:</strong>
                                 ${npc.description}
                             </div>
                             
                             <div class="npc-section">
                                 <strong>Внешность:</strong>
                                 ${npc.appearance}
                             </div>
                             
                             <div class="npc-section">
                                 <strong>Профессия:</strong>
                                 ${npc.profession}
                             </div>
                             
                             <div class="npc-section">
                                 <strong>Технические параметры:</strong>
                                 <ul>
                                     ${npc.technical_params.map(param => `<li>${param}</li>`).join('')}
                                 </ul>
                             </div>
                             
                             ${npc.spells && Object.keys(npc.spells).length > 0 ? `
                             <div class="npc-section">
                                 <strong>Заклинания:</strong>
                                 ${Object.entries(npc.spells).map(([level, spells]) => `
                                     <div style="margin: 8px 0;">
                                         <strong>${level === 'cantrips' ? 'Заговоры (0 уровень)' : 'Уровень ' + level.replace('level_', '')}:</strong>
                                         <ul>
                                             ${spells.map(spell => `<li>${spell}</li>`).join('')}
                                         </ul>
                                     </div>
                                 `).join('')}
                             </div>
                             ` : ''}
                             
                             <div class="npc-section">
                                 <strong>Источник данных:</strong>
                                 ${npc.api_source}
                             </div>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `<p class="error">Ошибка: ${data.error || 'Неизвестная ошибка'}</p>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                resultDiv.innerHTML = '<p class="error">Ошибка сети. Попробуйте ещё раз.</p>';
            })
            .finally(() => {
                submitBtn.textContent = 'Сгенерировать NPC';
                submitBtn.disabled = false;
            });
        });
    </script>
</body>
</html>
