<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Простой тест NPC - DnD Copilot</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        select, input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; }
        button { background: #007bff; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #0056b3; }
        .result { margin: 20px 0; padding: 15px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .npc-card { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin: 15px 0; }
        .npc-name { font-size: 1.5em; font-weight: bold; color: #495057; margin-bottom: 10px; }
        .npc-stat { margin: 8px 0; padding: 5px 0; border-bottom: 1px solid #e9ecef; }
        .loading { text-align: center; padding: 20px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎲 Простой тест генерации NPC</h1>
        
        <form id="npcForm">
            <div class="form-group">
                <label for="race">Раса:</label>
                <select id="race" name="race" required>
                    <option value="human">Человек</option>
                    <option value="elf">Эльф</option>
                    <option value="dwarf">Дварф</option>
                    <option value="halfling">Полурослик</option>
                    <option value="orc">Орк</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="class">Класс:</label>
                <select id="class" name="class" required>
                    <option value="fighter">Воин</option>
                    <option value="wizard">Волшебник</option>
                    <option value="rogue">Плут</option>
                    <option value="cleric">Жрец</option>
                    <option value="ranger">Следопыт</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="level">Уровень:</label>
                <input type="number" id="level" name="level" value="1" min="1" max="20" required>
            </div>
            
            <div class="form-group">
                <label for="alignment">Мировоззрение:</label>
                <select id="alignment" name="alignment" required>
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
            
            <button type="submit">🎯 Сгенерировать NPC</button>
        </form>
        
        <div id="result"></div>
    </div>

    <script>
    document.getElementById('npcForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const resultDiv = document.getElementById('result');
        resultDiv.innerHTML = '<div class="loading">🔄 Генерация NPC...</div>';
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('api/generate-npc-test.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success && data.npc) {
                const npc = data.npc;
                resultDiv.innerHTML = `
                    <div class="result success">
                        <h3>✅ NPC успешно сгенерирован!</h3>
                    </div>
                    <div class="npc-card">
                        <div class="npc-name">${npc.name || 'Безымянный NPC'}</div>
                        
                        ${npc.description ? `<div class="npc-stat"><strong>Описание:</strong> ${npc.description}</div>` : ''}
                        ${npc.appearance ? `<div class="npc-stat"><strong>Внешность:</strong> ${npc.appearance}</div>` : ''}
                        ${npc.traits ? `<div class="npc-stat"><strong>Черты характера:</strong> ${npc.traits}</div>` : ''}
                        
                        ${npc.technical_params && npc.technical_params.length > 0 ? `
                            <div class="npc-stat">
                                <strong>Технические параметры:</strong>
                                <ul style="margin: 5px 0; padding-left: 20px;">
                                    ${npc.technical_params.map(param => `<li>${param}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
                        
                        <div class="npc-stat">
                            <strong>Параметры генерации:</strong><br>
                            Раса: ${formData.get('race')} | Класс: ${formData.get('class')} | Уровень: ${formData.get('level')} | Мировоззрение: ${formData.get('alignment')}
                        </div>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div class="result error">
                        <h3>❌ Ошибка генерации NPC</h3>
                        <p>${data.error || 'Неизвестная ошибка'}</p>
                    </div>
                `;
            }
        } catch (error) {
            resultDiv.innerHTML = `
                <div class="result error">
                    <h3>❌ Ошибка сети</h3>
                    <p>${error.message}</p>
                    <p><strong>Попробуйте:</strong></p>
                    <ul>
                        <li>Проверить подключение к интернету</li>
                        <li>Обновить страницу (F5)</li>
                        <li>Очистить кэш браузера (Ctrl+F5)</li>
                    </ul>
                </div>
            `;
        }
    });
    </script>
</body>
</html>
