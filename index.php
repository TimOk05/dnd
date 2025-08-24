<?php
session_start();
require_once 'users.php';

// Проверяем авторизацию
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Получаем имя текущего пользователя
$currentUser = getCurrentUser();

if (isset($_GET['curltest'])) {
    $ch = curl_init('https://api.deepseek.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    echo "CURL RESULT: " . htmlspecialchars($result) . "<br>ERROR: " . htmlspecialchars($err);
    exit;
}
if (isset($_GET['curltest2'])) {
    $apiKey = 'sk-1e898ddba737411e948af435d767e893';
    $data = [
        'model' => 'deepseek-chat',
        'messages' => [
            ['role' => 'system', 'content' => 'Проверь соединение.'],
            ['role' => 'user', 'content' => 'Скажи: соединение работает.']
        ]
    ];
    $ch = curl_init('https://api.deepseek.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
        'User-Agent: DnD-PHP-Test'
    ]);
    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    echo "CURL RESULT: " . htmlspecialchars($result) . "<br>ERROR: " . htmlspecialchars($err);
    exit;
}

// --- Заметки ---
if (!isset($_SESSION['notes'])) {
    $_SESSION['notes'] = [];
}
if (isset($_POST['add_note']) && isset($_POST['note_content'])) {
    $_SESSION['notes'][] = trim($_POST['note_content']);
    exit('OK');
}
if (isset($_POST['remove_note'])) {
    $idx = (int)$_POST['remove_note'];
    if (isset($_SESSION['notes'][$idx])) {
        array_splice($_SESSION['notes'], $idx, 1);
    }
    exit('OK');
}

// --- Быстрые генерации через AJAX ---
if (isset($_POST['fast_action'])) {
    $action = $_POST['fast_action'];
    // --- Кости ---
    if ($action === 'dice_result') {
        $dice = $_POST['dice'] ?? '1d20';
        $label = $_POST['label'] ?? '';
        if (preg_match('/^(\d{1,2})d(\d{1,3})$/', $dice, $m)) {
            $count = (int)$m[1]; $sides = (int)$m[2];
            $results = [];
            for ($i = 0; $i < $count; $i++) $results[] = rand(1, $sides);
            $sum = array_sum($results);
            $out = "Бросок: $dice\nРезультаты: " . implode(', ', $results) . "\nСумма: $sum";
            if ($label) $out .= "\nКомментарий: $label";
            echo nl2br(htmlspecialchars($out));
            exit;
        } else {
            echo 'Неверный формат кубов!';
            exit;
        }
    }
    // --- Сохранение заметки инициативы ---
    if ($action === 'save_note') {
        $content = $_POST['content'] ?? '';
        if ($content) {
            $_SESSION['notes'][] = $content;
            echo 'Заметка сохранена';
        } else {
            echo 'Ошибка: пустое содержимое';
        }
        exit;
    }
    echo 'Неизвестное действие';
    exit;
}

// --- Чат ---
if (!isset($_SESSION['chat'])) {
    $_SESSION['chat'] = [];
}
if (isset($_GET['reset'])) {
    $_SESSION['chat'] = [];
    header("Location: index.php");
    exit;
}

// --- Новый systemInstruction с усиленными требованиями ---
$systemInstruction = "Ты — помощник мастера DnD. Твоя задача — сгенерировать NPC для быстрого и удобного вывода в игровом приложении. Каждый блок будет отображаться отдельно, поэтому не добавляй пояснений, не используй лишние слова, не пиши ничего кроме блоков.\nСтрого по шаблону, каждый блок с новой строки:\nИмя: ...\nКраткое описание: ...\nЧерта характера: ...\nСлабость: ...\nКороткая характеристика: Оружие: ... Урон: ... Хиты: ... Способность: ...\n\nВАЖНО: НЕ используй слово 'Описание' в начале блоков. Начинай блоки сразу с содержимого. НЕ дублируй информацию между блоками. Каждый блок должен содержать только релевантную информацию.

ВАЖНО: Способность — это конкретный навык персонажа в D&D, например: 'Двойная атака', 'Исцеление ран', 'Скрытность', 'Божественная кара', 'Ярость', 'Вдохновение', 'Магическая защита', 'Элементальная магия', 'Боевой стиль', 'Связь с природой', 'Боевые искусства', 'Скрытные способности', 'Магическое исследование', 'Общение с животными', 'Магическая обработка', 'Магическое красноречие'. НЕ пиши описания, только название способности. ОБЯЗАТЕЛЬНО указывай способность для каждого класса кроме 'Без класса'.\nТехнические параметры (Оружие, Урон, Хиты, Способность) обязательны и всегда идут первым блоком. Если не можешь заполнить какой-то параметр — напиши ‘-'. Не добавляй ничего лишнего.";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && !isset($_POST['add_note']) && !isset($_POST['remove_note'])) {
    $userMessage = trim($_POST['message']);
    if ($userMessage !== '') {
        if (empty($_SESSION['chat']) || $_SESSION['chat'][0]['role'] !== 'system') {
            array_unshift($_SESSION['chat'], ['role' => 'system', 'content' => $systemInstruction]);
        }
        $_SESSION['chat'][] = ['role' => 'user', 'content' => $userMessage];
        $apiKey = 'sk-1e898ddba737411e948af435d767e893';
        $apiUrl = 'https://api.deepseek.com/v1/chat/completions';
        $messages = array_map(function($msg) {
            return ['role' => $msg['role'], 'content' => $msg['content']];
        }, $_SESSION['chat']);
        $data = [
            'model' => 'deepseek-chat',
            'messages' => $messages
        ];
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);
        $aiMessage = $result['choices'][0]['message']['content'] ?? '[Ошибка AI]';
        $aiMessage = preg_replace('/[*_`>#\-]+/', '', $aiMessage);
        $aiMessage = str_replace(['"', "'", '“', '”', '«', '»'], '', $aiMessage);
        $aiMessage = preg_replace('/\n{2,}/', "\n", $aiMessage);
        $aiMessage = preg_replace('/\s{3,}/', "\n", $aiMessage);
        $lines = explode("\n", $aiMessage);
        $formatted = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (mb_strlen($line) > 90) {
                $formatted = array_merge($formatted, str_split($line, 80));
            } else {
                $formatted[] = $line;
            }
        }
        $aiMessage = implode("\n", $formatted);
        $_SESSION['chat'][] = ['role' => 'assistant', 'content' => $aiMessage];
    }
}

// --- Генерация быстрых кнопок ---
$fastBtns = '';
$fastBtns .= '<button class="fast-btn" onclick="openDiceStep1()">🎲 Бросок костей</button>';
$fastBtns .= '<button class="fast-btn" onclick="openCharacterModal()">⚔️ Персонаж</button>';
$fastBtns .= '<button class="fast-btn" onclick="openEnemyModal()">👹 Противники</button>';
$fastBtns .= '<button class="fast-btn" onclick="openInitiativeModal()">⚡ Инициатива</button>';
$fastBtns .= '<a href="generators.php" class="fast-btn" style="text-decoration: none; display: inline-block;">🎲 Генераторы</a>';

// --- Генерация сообщений чата (пропускаем system) ---
$chatMsgs = '';
foreach ($_SESSION['chat'] as $msg) {
    if ($msg['role'] === 'system') continue;
    $who = $msg['role'] === 'user' ? 'Вы' : 'AI';
    $class = $msg['role'];
    $chatMsgs .= '<div class="msg ' . $class . '"><b>' . $who . ':</b> ' . nl2br(htmlspecialchars($msg['content'])) . '</div>';
}

// --- Генерация блока заметок ---
$notesBlock = '';
foreach ($_SESSION['notes'] as $i => $note) {
    // Ищем имя NPC в заметке
    $plain = strip_tags(str_replace(['<br>', "\n"], "\n", $note));
    $lines = array_filter(array_map('trim', explode("\n", $plain)));
    $nameLine = '';
    
    // Сначала ищем в специальном заголовке NPC
    if (preg_match('/<div class="npc-name-header">([^<]+)<\/div>/iu', $note, $matches)) {
        $nameLine = trim($matches[1]);
    } else {
        // Ищем имя в заголовке NPC
        if (preg_match('/<div class="npc-modern-header">([^<]+)<\/div>/iu', $note, $matches)) {
            $nameLine = trim($matches[1]);
        } else {
            // Ищем строку с именем по разным вариантам
            foreach ($lines as $line) {
                if (preg_match('/^(Имя|Name|Имя NPC|Имя персонажа)\s*:/iu', $line)) {
                    $nameLine = $line;
                    break;
                }
            }
        }
    }
    
    // Если нашли имя, извлекаем только имя без префикса
    if ($nameLine) {
        if (preg_match('/^(Имя|Name|Имя NPC|Имя персонажа)\s*:\s*(.+)$/iu', $nameLine, $matches)) {
            $nameLine = trim($matches[2]);
        }
        // Убираем лишние слова из имени
        $nameLine = preg_replace('/^описание\s+/i', '', $nameLine);
        $nameLine = preg_replace('/^\s*—\s*/', '', $nameLine);
        $nameLine = preg_replace('/^npc\s+/i', '', $nameLine);
    }
    
    // Если это не NPC заметка, ищем первое значимое слово
    if (!$nameLine) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line && !preg_match('/^(описание|внешность|черты|способность|оружие|урон|хиты|класс|раса|уровень|профессия)/iu', $line)) {
                $nameLine = $line;
                break;
            }
        }
    }
    
    // Очищаем имя - берем только первое слово с большой буквы
    if ($nameLine) {
        $words = preg_split('/\s+/', $nameLine);
        if (count($words) > 1) {
            // Берем только первое слово как имя
            $nameLine = $words[0];
        }
        // Убираем лишние символы, оставляем только буквы
        $nameLine = preg_replace('/[^\wа-яё]/ui', '', $nameLine);
        $nameLine = trim($nameLine);
    }
    
    $previewSrc = $nameLine ?: (count($lines) ? $lines[0] : '(нет данных)');
    // Убираем лишние слова из превью
    $previewSrc = preg_replace('/^описание\s+/i', '', $previewSrc);
    $previewSrc = preg_replace('/^\s*—\s*/', '', $previewSrc);
    $previewSrc = preg_replace('/^npc\s+/i', '', $previewSrc);
    
    // Очищаем превью - берем только первое слово если это имя
    if ($nameLine) {
        $words = preg_split('/\s+/', $previewSrc);
        if (count($words) > 1) {
            $preview = $words[0];
        } else {
            $preview = $previewSrc;
        }
    } else {
        // Обрезаем превью до 30 символов или 3 слов
        $words = preg_split('/\s+/', $previewSrc);
        if (count($words) > 3) {
            $preview = implode(' ', array_slice($words, 0, 3)) . '…';
        } else if (mb_strlen($previewSrc) > 30) {
            $preview = mb_substr($previewSrc, 0, 30) . '…';
        } else {
            $preview = $previewSrc;
        }
    }
    $notesBlock .= '<div class="note-item" onclick="expandNote(' . $i . ')">' . htmlspecialchars($preview, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '<button class="note-remove" onclick="event.stopPropagation();removeNote(' . $i . ')">×</button></div>';
}

// --- Загрузка шаблона и подстановка контента ---
$template = file_get_contents(__DIR__ . '/template.html');
$template = str_replace('{{fast_buttons}}', $fastBtns, $template);
$template = str_replace('{{chat_messages}}', $chatMsgs, $template);
$template = str_replace('{{notes_block}}', $notesBlock, $template);
echo $template;
?>
<script>
// --- Dice Modal Steps ---
function openDiceStep1() {
    showModal('<b class="mini-menu-title">Выберите тип кости:</b><div class="mini-menu-btns">' +
        ['d3','d4','d6','d8','d10','d12','d20','d100'].map(d => `<button onclick=\'openDiceStep2("${d}")\' class=\'fast-btn\'>${d}</button>`).join(' ') + '</div>'
    );
    document.getElementById('modal-save').style.display = 'none';
}
function openDiceStep2(dice) {
    showModal(`<b class="mini-menu-title">Сколько бросков ${dice}?</b><div class="npc-level-wrap"><input type=number id=dice-count value=1 min=1 max=20 style=\'width:60px\'></div><div class="npc-level-wrap"><input type=text id=dice-label placeholder=\'Комментарий (необязательно)\' style=\'margin-top:8px;width:90%\'></div><button class=\'fast-btn\' onclick=\'getDiceResult("${dice}")\'>Бросить</button>`);
    document.getElementById('modal-save').style.display = 'none';
    // Автофокус на поле количества
    setTimeout(() => document.getElementById('dice-count').focus(), 100);
}
function getDiceResult(dice) {
    let count = document.getElementById('dice-count').value;
    let label = document.getElementById('dice-label').value;
    let diceStr = count + dice;
    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'fast_action=dice_result&dice=' + encodeURIComponent(diceStr) + '&label=' + encodeURIComponent(label)
    })
    .then(r => r.text())
    .then(txt => {
        document.getElementById('modal-content').innerHTML = formatResultSegments(txt, false);
        document.getElementById('modal-save').style.display = '';
        document.getElementById('modal-save').onclick = function() { saveNote(txt); closeModal(); };
    });
}
// --- Генерация персонажей и противников ---
const characterRaces = ['Человек','Эльф','Гном','Полуорк','Полурослик','Тифлинг','Драконорожденный','Полуэльф','Дварф','Гоблин','Орк','Кобольд','Ящеролюд','Хоббит'];
const characterClasses = ['Воин','Паладин','Колдун','Волшебник','Плут','Следопыт','Жрец','Бард','Варвар','Монах','Сорсерер','Друид'];

// --- Функция открытия генерации персонажей ---
function openCharacterModal() {
    showModal(`
        <div class="character-generator">
            <div class="generator-header">
                <h2>⚔️ Генератор персонажей</h2>
                <p class="generator-subtitle">Создайте полноценного персонажа с использованием D&D API и AI</p>
            </div>
            
            <form id="characterForm" class="character-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="character-race">Раса персонажа</label>
                        <select id="character-race" name="race" required>
                            <option value="">Выберите расу</option>
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
                        <label for="character-class">Класс персонажа</label>
                        <select id="character-class" name="class" required>
                            <option value="">Выберите класс</option>
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
                        <label for="character-level">Уровень персонажа</label>
                        <input type="number" id="character-level" name="level" min="1" max="20" value="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="character-alignment">Мировоззрение</label>
                        <select id="character-alignment" name="alignment" required>
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
                </div>
                

                
                <button type="submit" class="generate-btn">
                    <span class="btn-icon">⚔️</span>
                    <span class="btn-text">Создать персонажа</span>
                </button>
            </form>
            
            <div id="characterResult" class="result-container"></div>
        </div>
    `);
    
    document.getElementById('modal-save').style.display = 'none';
    
    // Добавляем обработчик формы
    document.getElementById('characterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('use_ai', 'on'); // AI всегда включен
        const submitBtn = this.querySelector('button[type="submit"]');
        const resultDiv = document.getElementById('characterResult');
        
        submitBtn.innerHTML = '<span class="btn-icon">⏳</span><span class="btn-text">Создание...</span>';
        submitBtn.disabled = true;
        resultDiv.innerHTML = '<div class="loading">Создание персонажа с AI-улучшением...</div>';
        
        fetch('api/generate-characters.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.npc) {
                resultDiv.innerHTML = formatCharacterFromApi(data.npc);
            } else {
                resultDiv.innerHTML = '<div class="error">Ошибка: ' + (data.error || 'Неизвестная ошибка') + '</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultDiv.innerHTML = '<div class="error">Ошибка сети. Попробуйте ещё раз.</div>';
        })
        .finally(() => {
            submitBtn.innerHTML = '<span class="btn-icon">⚔️</span><span class="btn-text">Создать персонажа</span>';
            submitBtn.disabled = false;
        });
    });
}

// --- Функция открытия генерации противников ---
function openEnemyModal() {
    showModal(`
        <div class="enemy-generator">
            <div class="generator-header">
                <h2>👹 Генератор противников</h2>
                <p class="generator-subtitle">Создайте подходящих противников для вашей группы</p>
            </div>
            
            <form id="enemyForm" class="enemy-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="enemy-threat">Уровень угрозы</label>
                        <select id="enemy-threat" name="threat_level" required>
                            <option value="">Выберите уровень угрозы</option>
                            <option value="easy">Легкий (1-4 уровень)</option>
                            <option value="medium">Средний (5-10 уровень)</option>
                            <option value="hard">Сложный (11-16 уровень)</option>
                            <option value="deadly">Смертельный (17-20 уровень)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="enemy-count">Количество противников</label>
                        <input type="number" id="enemy-count" name="count" min="1" max="10" value="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="enemy-type">Тип противников</label>
                        <select id="enemy-type" name="enemy_type">
                            <option value="">Любой тип</option>
                            <option value="humanoid">Гуманоиды</option>
                            <option value="beast">Звери</option>
                            <option value="undead">Нежить</option>
                            <option value="giant">Великаны</option>
                            <option value="dragon">Драконы</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="enemy-environment">Среда обитания</label>
                        <select id="enemy-environment" name="environment">
                            <option value="">Любая среда</option>
                            <option value="arctic">Арктика</option>
                            <option value="coastal">Побережье</option>
                            <option value="desert">Пустыня</option>
                            <option value="forest">Лес</option>
                            <option value="grassland">Равнины</option>
                            <option value="hill">Холмы</option>
                            <option value="mountain">Горы</option>
                            <option value="swamp">Болота</option>
                            <option value="underdark">Подземелье</option>
                            <option value="urban">Город</option>
                        </select>
                    </div>
                </div>
                

                
                <button type="submit" class="generate-btn">
                    <span class="btn-icon">👹</span>
                    <span class="btn-text">Создать противников</span>
                </button>
            </form>
            
            <div id="enemyResult" class="result-container"></div>
        </div>
    `);
    
    document.getElementById('modal-save').style.display = 'none';
    
    // Добавляем обработчик формы
    document.getElementById('enemyForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('use_ai', 'on'); // AI всегда включен
        const submitBtn = this.querySelector('button[type="submit"]');
        const resultDiv = document.getElementById('enemyResult');
        
        submitBtn.innerHTML = '<span class="btn-icon">⏳</span><span class="btn-text">Создание...</span>';
        submitBtn.disabled = true;
        resultDiv.innerHTML = '<div class="loading">Создание противников...</div>';
        
        fetch('api/generate-enemies.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.enemies) {
                resultDiv.innerHTML = formatEnemiesFromApi(data.enemies);
            } else {
                resultDiv.innerHTML = '<div class="error">Ошибка: ' + (data.error || 'Неизвестная ошибка') + '</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultDiv.innerHTML = '<div class="error">Ошибка сети. Попробуйте ещё раз.</div>';
        })
        .finally(() => {
            submitBtn.innerHTML = '<span class="btn-icon">👹</span><span class="btn-text">Создать противников</span>';
            submitBtn.disabled = false;
        });
    });
}
function openNpcStepLevel(cls) {
    npcClass = cls;
    showModal(`
        <b class="mini-menu-title">Укажите уровень NPC (1-20):</b>
        <div class="npc-level-wrap">
            <input type=number id=npc-level value=1 min=1 max=20 style='width:60px'>
        </div>
        <div class="npc-advanced-settings" style="margin-top: 15px;">
            <button class='fast-btn' onclick='toggleAdvancedSettings()' style='background: var(--accent-info);'>⚙️ Расширенные настройки</button>
        </div>
        <div id="advanced-settings-panel" style="display: none; margin-top: 15px; padding: 15px; background: var(--bg-tertiary); border-radius: 8px; border: 1px solid var(--border-tertiary);">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; color: var(--text-tertiary); font-weight: bold;">Пол:</label>
                <div class="advanced-options">
                    <label style="margin-right: 15px;"><input type="radio" name="gender" value="мужской" checked> Мужской</label>
                    <label style="margin-right: 15px;"><input type="radio" name="gender" value="женский"> Женский</label>
                    <label><input type="radio" name="gender" value="рандом"> Рандом</label>
                </div>
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-tertiary); font-weight: bold;">Мировоззрение:</label>
                <div class="advanced-options">
                    <label style="margin-right: 15px;"><input type="radio" name="alignment" value="добрый" checked> Добрый</label>
                    <label style="margin-right: 15px;"><input type="radio" name="alignment" value="нейтральный"> Нейтральный</label>
                    <label style="margin-right: 15px;"><input type="radio" name="alignment" value="злой"> Злой</label>
                    <label><input type="radio" name="alignment" value="рандом"> Рандом</label>
                </div>
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; color: var(--text-tertiary); font-weight: bold;">Профессия:</label>
                <select id="npc-background" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid var(--border-tertiary); background: var(--bg-primary); color: var(--text-primary);">
                    <option value="soldier">Солдат</option>
                    <option value="criminal">Преступник</option>
                    <option value="sage">Мудрец</option>
                    <option value="noble">Благородный</option>
                    <option value="merchant">Торговец</option>
                    <option value="artisan">Ремесленник</option>
                    <option value="farmer">Фермер</option>
                    <option value="hermit">Отшельник</option>
                    <option value="entertainer">Артист</option>
                    <option value="acolyte">Послушник</option>
                    <option value="outlander">Чужеземец</option>
                    <option value="urchin">Бродяга</option>
                </select>
            </div>
        </div>
        <div style="margin-top: 15px;">
            <button class='fast-btn' onclick='generateNpcWithLevel()'>Создать NPC</button>
        </div>
    `);
    document.getElementById('modal-save').style.display = 'none';
    // Автофокус на поле уровня
    setTimeout(() => document.getElementById('npc-level').focus(), 100);
}

// --- Функция переключения расширенных настроек ---
function toggleAdvancedSettings() {
    const panel = document.getElementById('advanced-settings-panel');
    const button = document.querySelector('.npc-advanced-settings .fast-btn');
    
    if (panel.style.display === 'none') {
        panel.style.display = 'block';
        button.innerHTML = '⚙️ Скрыть расширенные настройки';
        button.style.background = 'var(--accent-warning)';
    } else {
        panel.style.display = 'none';
        button.innerHTML = '⚙️ Расширенные настройки';
        button.style.background = 'var(--accent-info)';
    }
}
// --- Загрузка базы уникальных торговцев ---
window.uniqueTraders = {
  data: {
    traits: [
      'Любознательный и наблюдательный',
      'Осторожный и расчетливый',
      'Дружелюбный и общительный',
      'Гордый и независимый',
      'Мудрый и терпеливый'
    ],
    motivation: [
      'Поиск знаний и мудрости',
      'Защита близких и слабых',
      'Достижение власти и влияния',
      'Исследование мира и приключения',
      'Служение высшей цели'
    ],
    occupations: [
      { name_ru: 'Торговец' },
      { name_ru: 'Ремесленник' },
      { name_ru: 'Стражник' },
      { name_ru: 'Ученый' },
      { name_ru: 'Авантюрист' }
    ]
  }
};

// --- Загрузка механических параметров D&D ---
window.dndMechanics = {
  classes: {
    fighter: { casting_category: 'none', saving_throws: ['str', 'con'] },
    wizard: { casting_category: 'full', spellcasting_ability: 'int', saving_throws: ['int', 'wis'] },
    cleric: { casting_category: 'full', spellcasting_ability: 'wis', saving_throws: ['wis', 'cha'] },
    rogue: { casting_category: 'none', saving_throws: ['dex', 'int'] },
    barbarian: { casting_category: 'none', saving_throws: ['str', 'con'] },
    paladin: { casting_category: 'half', spellcasting_ability: 'cha', saving_throws: ['wis', 'cha'] },
    ranger: { casting_category: 'half', spellcasting_ability: 'wis', saving_throws: ['str', 'dex'] },
    bard: { casting_category: 'full', spellcasting_ability: 'cha', saving_throws: ['dex', 'cha'] },
    druid: { casting_category: 'full', spellcasting_ability: 'wis', saving_throws: ['int', 'wis'] },
    monk: { casting_category: 'none', saving_throws: ['str', 'dex'] },
    warlock: { casting_category: 'pact', spellcasting_ability: 'cha', saving_throws: ['wis', 'cha'] },
    sorcerer: { casting_category: 'full', spellcasting_ability: 'cha', saving_throws: ['con', 'cha'] },
    artificer: { casting_category: 'half', spellcasting_ability: 'int', saving_throws: ['con', 'int'] }
  },
  races: {
    human: { speed: { walk: 30 } },
    elf: { speed: { walk: 30 } },
    dwarf: { speed: { walk: 25 } },
    halfling: { speed: { walk: 25 } },
    gnome: { speed: { walk: 25 } },
    half_elf: { speed: { walk: 30 } },
    half_orc: { speed: { walk: 30 } },
    tiefling: { speed: { walk: 30 } },
    dragonborn: { speed: { walk: 30 } },
    goblin: { speed: { walk: 30 } },
    orc: { speed: { walk: 30 } },
    kobold: { speed: { walk: 30 } },
    lizardfolk: { speed: { walk: 30 } },
    hobbit: { speed: { walk: 25 } }
  },
  enums: {
    saving_throws: ['str', 'dex', 'con', 'int', 'wis', 'cha']
  },
  rules: {
    slot_tables: {
      full: {
        1: { 1: 2 },
        2: { 1: 3, 2: 2 },
        3: { 1: 4, 2: 2 },
        4: { 1: 4, 2: 3 },
        5: { 1: 4, 2: 3, 3: 2 }
      },
      half: {
        1: { 1: 0 },
        2: { 1: 2 },
        3: { 1: 3 },
        4: { 1: 3, 2: 1 },
        5: { 1: 4, 2: 2 }
      },
      pact: {
        1: { 1: 1 },
        2: { 1: 2 },
        3: { 1: 2, 2: 1 },
        4: { 1: 2, 2: 1 },
        5: { 1: 2, 2: 1, 3: 1 }
      }
    }
  }
};
console.log('D&D Mechanics loaded successfully');

// --- Функция генерации технических параметров NPC ---
function generateTechnicalParams(race, npcClass, level) {
    console.log('generateTechnicalParams called with:', { race, npcClass, level });
    console.log('window.dndMechanics:', window.dndMechanics);
    
    if (!window.dndMechanics) {
        console.error('D&D Mechanics not loaded');
        return "Технические параметры: Базовые (данные не загружены)";
    }
    
    const mechanics = window.dndMechanics;
    
    // Преобразуем русские названия классов в английские ключи
    const classMapping = {
        'воин': 'fighter',
        'маг': 'wizard',
        'жрец': 'cleric',
        'плут': 'rogue',
        'варвар': 'barbarian',
        'паладин': 'paladin',
        'следопыт': 'ranger',
        'бард': 'bard',
        'друид': 'druid',
        'монах': 'monk',
        'колдун': 'warlock',
        'чародей': 'sorcerer',
        'изобретатель': 'artificer'
    };
    
    const classKey = classMapping[npcClass.toLowerCase()] || npcClass.toLowerCase();
    
    // Преобразуем русские названия рас в английские ключи
    const raceMapping = {
        'человек': 'human',
        'эльф': 'elf',
        'гном': 'gnome',
        'дворф': 'dwarf',
        'полурослик': 'halfling',
        'полуэльф': 'half_elf',
        'полуорк': 'half_orc',
        'тифлинг': 'tiefling',
        'драконорожденный': 'dragonborn',
        'гоблин': 'goblin',
        'орк': 'orc',
        'кобольд': 'kobold',
        'ящеролюд': 'lizardfolk',
        'хоббит': 'hobbit'
    };
    
    const raceKey = raceMapping[race.toLowerCase()] || race.toLowerCase();
    
    console.log('Processing with keys:', { classKey, raceKey });
    console.log('Available classes:', mechanics.classes ? Object.keys(mechanics.classes) : 'undefined');
    console.log('Available races:', mechanics.races ? Object.keys(mechanics.races) : 'undefined');
    
    // Генерируем случайные характеристики (10-18)
    const abilities = {
        str: Math.floor(Math.random() * 9) + 10,
        dex: Math.floor(Math.random() * 9) + 10,
        con: Math.floor(Math.random() * 9) + 10,
        int: Math.floor(Math.random() * 9) + 10,
        wis: Math.floor(Math.random() * 9) + 10,
        cha: Math.floor(Math.random() * 9) + 10
    };
    
    // Определяем бонус мастерства по уровню
    let proficiencyBonus = 2;
    if (level >= 5) proficiencyBonus = 3;
    if (level >= 9) proficiencyBonus = 4;
    if (level >= 13) proficiencyBonus = 5;
    if (level >= 17) proficiencyBonus = 6;
    
    // Получаем данные класса
    const classData = mechanics.classes[classKey] || mechanics.classes.fighter || {};
    const castingCategory = classData.casting_category || 'none';
    const spellcastingAbility = classData.spellcasting_ability || null;
    
    // Рассчитываем модификаторы
    const mods = {};
    for (let ability in abilities) {
        mods[ability] = Math.floor((abilities[ability] - 10) / 2);
    }
    
    // Рассчитываем КД
    let ac = 10 + mods.dex; // Базовая формула
    if (castingCategory !== 'none') {
        ac = 13 + mods.dex; // Mage Armor для заклинателей
    }
    
    // Рассчитываем инициативу
    const initiativeMod = mods.dex;
    
    // Рассчитываем скорость
    const raceData = mechanics.races[raceKey] || mechanics.races.human || { speed: { walk: 30 } };
    const speed = raceData.speed ? raceData.speed.walk : 30;
    
    // Рассчитываем спасброски
    const savingThrows = classData.saving_throws || ['str', 'con'];
    const savingThrowMods = {};
    
    for (let ability of mechanics.enums.saving_throws) {
        const isProficient = savingThrows.includes(ability);
        savingThrowMods[ability] = mods[ability] + (isProficient ? proficiencyBonus : 0);
    }
    
    // Рассчитываем параметры заклинаний
    let spellAttackBonus = 0;
    let spellSaveDC = 0;
    let spellSlots = {};
    
    if (castingCategory !== 'none' && spellcastingAbility) {
        spellAttackBonus = proficiencyBonus + mods[spellcastingAbility];
        spellSaveDC = 8 + proficiencyBonus + mods[spellcastingAbility];
        
        // Получаем слоты заклинаний
        if (mechanics.rules && mechanics.rules.slot_tables) {
            const slotTable = mechanics.rules.slot_tables[castingCategory];
            if (slotTable && slotTable[level]) {
                spellSlots = slotTable[level];
            }
        }
    }
    
    // Рассчитываем боевые параметры
    let extraAttacks = 0;
    if (classData.martial && classData.martial.extra_attacks) {
        extraAttacks = classData.martial.extra_attacks[level] || 0;
    }
    
    // Формируем результат
    let result = `\n\nТехнические параметры:\n`;
    result += `Класс доспеха: ${ac}\n`;
    result += `Инициатива: ${initiativeMod >= 0 ? '+' : ''}${initiativeMod}\n`;
    result += `Скорость: ${speed} футов\n`;
    result += `Уровень: ${level}\n`;
    result += `Бонус мастерства: +${proficiencyBonus}\n`;
    
    // Характеристики
    result += `\nХарактеристики:\n`;
    result += `СИЛ ${abilities.str} (${mods.str >= 0 ? '+' : ''}${mods.str})\n`;
    result += `ЛОВ ${abilities.dex} (${mods.dex >= 0 ? '+' : ''}${mods.dex})\n`;
    result += `ТЕЛ ${abilities.con} (${mods.con >= 0 ? '+' : ''}${mods.con})\n`;
    result += `ИНТ ${abilities.int} (${mods.int >= 0 ? '+' : ''}${mods.int})\n`;
    result += `МДР ${abilities.wis} (${mods.wis >= 0 ? '+' : ''}${mods.wis})\n`;
    result += `ХАР ${abilities.cha} (${mods.cha >= 0 ? '+' : ''}${mods.cha})\n`;
    
    // Спасброски
    result += `\nСпасброски:\n`;
    for (let ability of mechanics.enums.saving_throws) {
        const mod = savingThrowMods[ability];
        const proficient = savingThrows.includes(ability) ? ' (мастерство)' : '';
        result += `${ability.toUpperCase()} ${mod >= 0 ? '+' : ''}${mod}${proficient}\n`;
    }
    
    // Заклинания
    if (castingCategory !== 'none') {
        result += `\nЗаклинания:\n`;
        result += `Бонус атаки заклинаниями: +${spellAttackBonus}\n`;
        result += `Сложность спасбросков: ${spellSaveDC}\n`;
        if (Object.keys(spellSlots).length > 0) {
            result += `Слоты заклинаний: `;
            const slotList = [];
            for (let spellLevel in spellSlots) {
                slotList.push(`${spellLevel} уровень: ${spellSlots[spellLevel]}`);
            }
            result += slotList.join(', ') + '\n';
        }
    }
    
    // Боевые параметры
    if (extraAttacks > 0) {
        result += `\nБоевые параметры:\n`;
        result += `Дополнительные атаки: ${extraAttacks}\n`;
    }
    
    // Особые способности класса
    if (classData.martial && classData.martial.special_features) {
        result += `\nОсобые способности:\n`;
        for (let feature of classData.martial.special_features) {
            result += `- ${feature}\n`;
        }
    }
    
    console.log('Technical params result:', result);
    return result;
}

function fetchNpcFromAI(race, npcClass, prof, level, advancedSettings = {}) {
    showModal('🎲 Генерация NPC...<br><small>Это может занять до 30 секунд</small>');
    
    // Добавляем индикатор прогресса
    let progressDots = 0;
    const progressInterval = setInterval(() => {
        progressDots = (progressDots + 1) % 4;
        const dots = '.'.repeat(progressDots);
        document.getElementById('modal-content').innerHTML = `🎲 Генерация NPC${dots}<br><small>Это может занять до 30 секунд</small>`;
    }, 500);
    
        // Используем встроенные данные вместо загрузки JSON
    const json = window.uniqueTraders;
    console.log('Using embedded traders data:', json);
    console.log('JSON loaded successfully:', json);
          // 1. Имя по расе или случайное
        let name = '';
        // Используем предустановленные имена для каждой расы
        const raceNames = {
            'человек': ['Александр', 'Елена', 'Михаил', 'Анна', 'Дмитрий', 'Мария', 'Сергей', 'Ольга', 'Андрей', 'Татьяна'],
            'эльф': ['Лиран', 'Аэлиус', 'Талас', 'Сильвана', 'Элронд', 'Галадриэль', 'Леголас', 'Арвен', 'Трандуил', 'Келебриан'],
            'гном': ['Торин', 'Гимли', 'Балин', 'Дорин', 'Нори', 'Бифур', 'Бофур', 'Бомбур', 'Двалин', 'Оин'],
            'полуорк': ['Гром', 'Ургаш', 'Краг', 'Шака', 'Мог', 'Гар', 'Торг', 'Зуг', 'Руг', 'Буг'],
            'полурослик': ['Бильбо', 'Фродо', 'Сэм', 'Пиппин', 'Мерри', 'Том', 'Дик', 'Гарри', 'Рори', 'Нори'],
            'тифлинг': ['Зара', 'Малик', 'Аш', 'Люцифер', 'Бел', 'Кейн', 'Азазель', 'Маммон', 'Левиафан', 'Асмодей'],
            'драконорожденный': ['Дракс', 'Рекс', 'Торн', 'Скай', 'Блейз', 'Фрост', 'Эмбер', 'Сторм', 'Фанг', 'Клод'],
            'полуэльф': ['Элрон', 'Арагорн', 'Арвен', 'Элронд', 'Келебриан', 'Элронд', 'Галадриэль', 'Леголас', 'Трандуил', 'Сильвана'],
            'дворф': ['Торин', 'Гимли', 'Балин', 'Дорин', 'Нори', 'Бифур', 'Бофур', 'Бомбур', 'Двалин', 'Оин'],
            'гоблин': ['Сник', 'Гоб', 'Ниб', 'Зог', 'Рат', 'Скрит', 'Грим', 'Твич', 'Скваб', 'Гриз'],
            'орк': ['Гром', 'Ургаш', 'Краг', 'Шака', 'Мог', 'Гар', 'Торг', 'Зуг', 'Руг', 'Буг'],
            'кобольд': ['Сник', 'Гоб', 'Ниб', 'Зог', 'Рат', 'Скрит', 'Грим', 'Твич', 'Скваб', 'Гриз'],
            'ящеролюд': ['Зар', 'Кеш', 'Тал', 'Рекс', 'Скай', 'Торн', 'Фанг', 'Клод', 'Блейз', 'Фрост'],
            'хоббит': ['Бильбо', 'Фродо', 'Сэм', 'Пиппин', 'Мерри', 'Том', 'Дик', 'Гарри', 'Рори', 'Нори']
        };
        
        // Выбираем имя по расе или случайное
        let raceKey = race ? race.toLowerCase() : 'человек';
        console.log('NPC Generation Debug:', { race, raceKey, availableRaces: Object.keys(raceNames) });
        let namePool = raceNames[raceKey] || raceNames['человек'];
        name = namePool[Math.floor(Math.random() * namePool.length)];
        console.log('Selected name:', name, 'from pool:', namePool);
        // 2. Черты, мотивация, профессия
        let trait = '';
        if (json.data && json.data.traits && Array.isArray(json.data.traits) && json.data.traits.length > 0) {
          trait = json.data.traits[Math.floor(Math.random() * json.data.traits.length)];
        }
        let motivation = '';
        if (json.data && json.data.motivation && Array.isArray(json.data.motivation) && json.data.motivation.length > 0) {
          motivation = json.data.motivation[Math.floor(Math.random() * json.data.motivation.length)];
        }
        let occ = '';
        if (json.data && json.data.occupations && Array.isArray(json.data.occupations) && json.data.occupations.length > 0) {
          occ = json.data.occupations[Math.floor(Math.random() * json.data.occupations.length)].name_ru;
        }
        // 3. Формируем контекст
        let contextBlock = '';
        if (name) contextBlock += `\nИмя: ${name} (используй это имя для NPC)`;
        if (trait) contextBlock += `\nЧерта: ${trait}`;
        if (motivation) contextBlock += `\nМотивация: ${motivation}`;
        if (occ) contextBlock += `\nПрофессия: ${occ}`;
        contextBlock += '\nИспользуй эти данные для вдохновения, но придумай цельного NPC.';
        
        // Генерируем технические параметры
        console.log('About to generate technical params for:', { race, npcClass, level });
        const technicalParams = generateTechnicalParams(race, npcClass, level);
        console.log('Technical params generated:', technicalParams);
        
        const systemInstruction = 'Создай уникального NPC для D&D. СТРОГО следуй этому формату:\n\nИмя и Профессия\n[только имя и профессия, например: "Торин Каменщик"]\n\nОписание\n[3-4 предложения о прошлом, мотивации, целях персонажа БЕЗ упоминания имени]\n\nВнешность\n[2-3 предложения о внешнем виде, одежде, особенностях]\n\nЧерты характера\n[1-2 предложения о личности, поведении, привычках]\n\nТехнические параметры\n[ИСПОЛЬЗУЙ ТОЛЬКО ПРЕДОСТАВЛЕННЫЕ ТЕХНИЧЕСКИЕ ПАРАМЕТРЫ, НЕ ИЗМЕНЯЙ ИХ]\n\nВАЖНО: Имя указывай ТОЛЬКО в блоке "Имя и Профессия". НЕ используй имя в других блоках. ОБЯЗАТЕЛЬНО учитывай указанный пол и мировоззрение в описании и чертах характера.';
        
        // Добавляем расширенные настройки в промпт
        let advancedPrompt = `Создай NPC для DnD. Раса: ${race}. Класс: ${npcClass}. Уровень: ${level}. Придумай подходящую профессию для этого персонажа.`;
        
        if (advancedSettings.gender) {
            advancedPrompt += ` Пол: ${advancedSettings.gender}.`;
        }
        if (advancedSettings.alignment) {
            advancedPrompt += ` Мировоззрение: ${advancedSettings.alignment}.`;
        }
        
        console.log('Advanced settings:', advancedSettings);
        console.log('Advanced prompt:', advancedPrompt);
        console.log('Context block:', contextBlock);
        console.log('Technical params length:', technicalParams.length);
        
        // Используем новый API для генерации NPC
        const formData = new FormData();
        formData.append('race', race);
        formData.append('class', npcClass);
        formData.append('level', level);
        formData.append('alignment', advancedSettings.alignment || 'neutral');
        formData.append('background', prof || 'soldier');
        
        fetch('api/generate-npc.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            clearInterval(progressInterval); // Останавливаем индикатор прогресса
            
            console.log('API Response:', data); // Отладочная информация
            
            if (data && data.success && data.npc) {
                const npc = data.npc;
                let html = `
                    <div class="npc-header">
                        <h3>${npc.name}</h3>
                        <div class="npc-subtitle">${npc.race} - ${npc.class} (уровень ${npc.level})</div>
                    </div>
                    
                    <div class="npc-section">
                        <h4>Основная информация</h4>
                        <div class="info-grid">
                            <div class="info-item">
                                <strong>Мировоззрение:</strong> ${npc.alignment}
                            </div>
                            <div class="info-item">
                                <strong>Профессия:</strong> ${npc.background}
                            </div>
                        </div>
                    </div>
                    
                    ${npc.description ? `
                        <div class="npc-section">
                            <h4>Описание</h4>
                            <p>${npc.description}</p>
                        </div>
                    ` : ''}
                    
                    ${npc.technical_params && npc.technical_params.length > 0 ? `
                        <div class="npc-section">
                            <h4>Технические параметры</h4>
                            <div class="technical-params">
                                ${npc.technical_params.map(param => `<div class="param-item">${param}</div>`).join('')}
                            </div>
                        </div>
                    ` : ''}
                `;
                
                document.getElementById('modal-content').innerHTML = html;
                document.getElementById('modal-save').style.display = '';
                document.getElementById('modal-save').onclick = function() { saveNote(document.getElementById('modal-content').innerHTML); closeModal(); };
                
                // Удаляем старую кнопку повторной генерации, если она есть
                let oldRegenerateBtn = document.querySelector('.modal-regenerate');
                if (oldRegenerateBtn) {
                    oldRegenerateBtn.remove();
                }
                
                // Добавляем кнопку повторной генерации
                let regenerateBtn = document.createElement('button');
                regenerateBtn.className = 'modal-regenerate';
                regenerateBtn.textContent = '🔄 Повторить генерацию';
                regenerateBtn.onclick = regenerateNpc;
                document.getElementById('modal').appendChild(regenerateBtn);
            } else {
                let errorMsg = 'Неизвестная ошибка';
                if (data && data.error) {
                    errorMsg = data.error;
                }
                document.getElementById('modal-content').innerHTML = '<div class="result-segment error">❌ Ошибка генерации: ' + errorMsg + '<br><small>Попробуйте ещё раз или проверьте соединение</small></div>';
                document.getElementById('modal-save').style.display = 'none';
            }
        })
        .catch((e) => {
            clearInterval(progressInterval); // Останавливаем индикатор прогресса
            console.error('AI Response Error:', e);
            document.getElementById('modal-content').innerHTML = '<div class="result-segment error">❌ Ошибка AI<br><small>Ошибка: ' + e.message + '</small><br><small>Попробуйте ещё раз</small></div>';
            document.getElementById('modal-save').style.display = 'none';
        });
}



function generateNpcWithLevel() {
    npcLevel = document.getElementById('npc-level').value;
    
    console.log('generateNpcWithLevel called with level:', npcLevel);
    console.log('Current npcRace:', npcRace);
    console.log('Current npcClass:', npcClass);
    
    // Собираем расширенные настройки
    let advancedSettings = {};
    
    // Получаем выбранный пол
    const genderRadio = document.querySelector('input[name="gender"]:checked');
    console.log('Gender radio found:', genderRadio);
    if (genderRadio) {
        console.log('Gender radio value:', genderRadio.value);
        if (genderRadio.value !== 'рандом') {
            advancedSettings.gender = genderRadio.value;
        }
    }
    
    // Получаем выбранное мировоззрение
    const alignmentRadio = document.querySelector('input[name="alignment"]:checked');
    console.log('Alignment radio found:', alignmentRadio);
    if (alignmentRadio) {
        console.log('Alignment radio value:', alignmentRadio.value);
        if (alignmentRadio.value !== 'рандом') {
            advancedSettings.alignment = alignmentRadio.value;
        }
    }
    
    // Получаем выбранную профессию
    const backgroundSelect = document.getElementById('npc-background');
    console.log('Background select found:', backgroundSelect);
    let background = 'soldier'; // значение по умолчанию
    if (backgroundSelect) {
        background = backgroundSelect.value;
        console.log('Background value:', background);
    }
    
    console.log('Collected advanced settings:', advancedSettings);
    
    // Сохраняем параметры для повторной генерации
    lastGeneratedParams = {
        race: npcRace,
        class: npcClass,
        level: npcLevel,
        background: background,
        advancedSettings: advancedSettings
    };
    
    // Передаем выбранную профессию
    fetchNpcFromAI(npcRace, npcClass, background, npcLevel, advancedSettings);
}

function regenerateNpc() {
    if (lastGeneratedParams.race && lastGeneratedParams.class && lastGeneratedParams.level) {
        const advancedSettings = lastGeneratedParams.advancedSettings || {};
        const background = lastGeneratedParams.background || 'soldier';
        fetchNpcFromAI(lastGeneratedParams.race, lastGeneratedParams.class, background, lastGeneratedParams.level, advancedSettings);
    } else {
        alert('Нет сохраненных параметров для повторной генерации');
    }
}

// --- Инициатива ---
let initiativeList = [];
let currentInitiativeIndex = 0;
let currentRound = 1;

function openInitiativeModal() {
    if (document.body.classList.contains('mobile-device')) {
        openSimpleInitiativeModal();
    } else {
        showModal('<div class="initiative-container">' +
            '<div class="initiative-header">' +
                '<h3>⚡ Инициатива</h3>' +
                '<div class="initiative-stats">' +
                    '<span class="stat-item">Участников: <strong id="initiative-count">0</strong></span>' +
                    '<span class="stat-item">Раунд: <strong id="initiative-round">1</strong></span>' +
                '</div>' +
            '</div>' +
            '<div class="initiative-current-turn" id="initiative-current-turn"></div>' +
            '<div class="initiative-list" id="initiative-list"></div>' +
            '<div class="initiative-controls">' +
                '<div class="control-group">' +
                    '<button class="initiative-btn player-btn" onclick="addInitiativeEntry(\'player\')">👤 Игрок</button>' +
                    '<button class="initiative-btn enemy-btn" onclick="addInitiativeEntry(\'enemy\')">👹 Противник</button>' +
                    '<button class="initiative-btn other-btn" onclick="addInitiativeEntry(\'other\')">⚡ Ещё</button>' +
                '</div>' +
                '<div class="control-group">' +
                    '<button class="initiative-btn round-btn" onclick="nextRound()">🔄 Новый раунд</button>' +
                    '<button class="initiative-btn clear-btn" onclick="clearInitiative()">🗑️ Очистить</button>' +
                '</div>' +
            '</div>' +
        '</div>');
        document.getElementById('modal-save').style.display = '';
        document.getElementById('modal-save').onclick = function() { saveInitiativeNote(); closeModal(); };
        updateInitiativeDisplay();
    }
}

function addInitiativeEntry(type) {
    let title = type === 'player' ? 'Добавить игрока' : 
                type === 'enemy' ? 'Добавить противника' : 'Добавить участника';
    let diceButton = type === 'enemy' || type === 'other' ? 
        '<button class="dice-btn" onclick="rollInitiativeDice()">🎲 d20</button>' : '';
    
    showModal('<div class="initiative-entry">' +
        '<div class="entry-title">' + title + '</div>' +
        '<input type="text" id="initiative-name" placeholder="Название (до 30 символов)" maxlength="30" class="initiative-input">' +
        '<input type="number" id="initiative-value" placeholder="Значение инициативы" class="initiative-input">' +
        diceButton +
        '<div class="entry-buttons">' +
            '<button class="save-btn" onclick="saveInitiativeEntry(\'' + type + '\')">Сохранить</button>' +
            '<button class="cancel-btn" onclick="openInitiativeModal()">Отмена</button>' +
        '</div>' +
    '</div>');
    document.getElementById('modal-save').style.display = 'none';
    // Автофокус на поле имени
    setTimeout(() => document.getElementById('initiative-name').focus(), 100);
}

function rollInitiativeDice() {
    let result = Math.floor(Math.random() * 20) + 1;
    document.getElementById('initiative-value').value = result;
}

function saveInitiativeEntry(type) {
    let name = document.getElementById('initiative-name').value.trim();
    let value = parseInt(document.getElementById('initiative-value').value);
    
    if (!name || isNaN(value)) {
        alert('Заполните все поля!');
        return;
    }
    
    // Проверяем ограничения на название
    if (!/^[а-яё0-9\s]+$/i.test(name)) {
        alert('Используйте только кириллицу, цифры и пробелы!');
        return;
    }
    
    let entry = {
        id: Date.now(),
        name: name,
        value: value,
        type: type
    };
    
    initiativeList.push(entry);
    sortInitiativeList();
    openInitiativeModal();
}

function sortInitiativeList() {
    initiativeList.sort((a, b) => {
        if (b.value !== a.value) {
            return b.value - a.value; // По убыванию
        }
        return a.id - b.id; // При равных значениях - по времени добавления
    });
}

function updateInitiativeDisplay() {
    // Обновляем счетчик участников и раунд
    document.getElementById('initiative-count').textContent = initiativeList.length;
    document.getElementById('initiative-round').textContent = currentRound;
    
    // Показываем текущего участника
    if (initiativeList.length > 0) {
        let current = initiativeList[currentInitiativeIndex];
        let typeIcon = current.type === 'player' ? '👤' : 
                      current.type === 'enemy' ? '👹' : '⚡';
        
        document.getElementById('initiative-current-turn').innerHTML = 
            '<div class="current-turn-display">' +
                '<div class="current-turn-icon">' + typeIcon + '</div>' +
                '<div class="current-turn-info">' +
                    '<div class="current-turn-name">' + current.name + '</div>' +
                    '<div class="current-turn-value">Инициатива: ' + current.value + '</div>' +
                '</div>' +
                '<div class="current-turn-actions">' +
                    '<button class="turn-btn prev-btn" onclick="prevInitiative()">◀</button>' +
                    '<button class="turn-btn next-btn" onclick="nextInitiative()">▶</button>' +
                '</div>' +
            '</div>';
    } else {
        document.getElementById('initiative-current-turn').innerHTML = 
            '<div class="no-initiative">Добавьте участников для начала боя</div>';
    }
    
    // Обновляем список участников
    let listHtml = '';
    initiativeList.forEach((entry, index) => {
        let isActive = index === currentInitiativeIndex;
        let typeClass = entry.type === 'player' ? 'player-entry' : 
                       entry.type === 'enemy' ? 'enemy-entry' : 'other-entry';
        let activeClass = isActive ? ' active' : '';
        let typeIcon = entry.type === 'player' ? '👤' : 
                      entry.type === 'enemy' ? '👹' : '⚡';
        
        listHtml += '<div class="initiative-item ' + typeClass + activeClass + '" onclick="setActiveInitiative(' + index + ')">' +
            '<div class="initiative-item-content">' +
                '<div class="initiative-icon">' + typeIcon + '</div>' +
                '<div class="initiative-info">' +
                    '<div class="initiative-name">' + entry.name + '</div>' +
                    '<div class="initiative-value">' + entry.value + '</div>' +
                '</div>' +
            '</div>' +
            '<div class="initiative-actions">' +
                '<button class="edit-btn" onclick="event.stopPropagation(); editInitiativeEntry(' + entry.id + ')">✏️</button>' +
                '<button class="delete-btn" onclick="event.stopPropagation(); deleteInitiativeEntry(' + entry.id + ')">🗑️</button>' +
            '</div>' +
        '</div>';
    });
    
    document.getElementById('initiative-list').innerHTML = listHtml;
}

function setActiveInitiative(index) {
    currentInitiativeIndex = index;
    updateInitiativeDisplay();
}

function prevInitiative() {
    if (initiativeList.length > 0) {
        currentInitiativeIndex = (currentInitiativeIndex - 1 + initiativeList.length) % initiativeList.length;
        updateInitiativeDisplay();
    }
}



function clearInitiative() {
    if (confirm('Очистить всех участников инициативы?')) {
        initiativeList = [];
        currentInitiativeIndex = 0;
        currentRound = 1;
        updateInitiativeDisplay();
    }
}

function nextRound() {
    currentRound++;
    currentInitiativeIndex = 0;
    updateInitiativeDisplay();
}

function nextInitiative() {
    if (initiativeList.length > 0) {
        currentInitiativeIndex = (currentInitiativeIndex + 1) % initiativeList.length;
        // Если прошли полный круг, увеличиваем раунд
        if (currentInitiativeIndex === 0) {
            currentRound++;
        }
        updateInitiativeDisplay();
    }
}

function prevInitiative() {
    if (initiativeList.length > 0) {
        currentInitiativeIndex = (currentInitiativeIndex - 1 + initiativeList.length) % initiativeList.length;
        // Если пошли назад и достигли конца, уменьшаем раунд
        if (currentInitiativeIndex === initiativeList.length - 1 && currentRound > 1) {
            currentRound--;
        }
        updateInitiativeDisplay();
    }
}

function editInitiativeEntry(id) {
    let entry = initiativeList.find(e => e.id === id);
    if (!entry) return;
    
    let title = entry.type === 'player' ? 'Редактировать игрока' : 
                entry.type === 'enemy' ? 'Редактировать противника' : 'Редактировать участника';
    
    showModal('<div class="initiative-entry">' +
        '<div class="entry-title">' + title + '</div>' +
        '<input type="text" id="initiative-name" value="' + entry.name + '" maxlength="30" class="initiative-input">' +
        '<input type="number" id="initiative-value" value="' + entry.value + '" class="initiative-input">' +
        '<div class="entry-buttons">' +
            '<button class="save-btn" onclick="updateInitiativeEntry(' + entry.id + ')">Сохранить</button>' +
            '<button class="cancel-btn" onclick="openInitiativeModal()">Отмена</button>' +
        '</div>' +
    '</div>');
    document.getElementById('modal-save').style.display = 'none';
    // Автофокус на поле имени
    setTimeout(() => document.getElementById('initiative-name').focus(), 100);
}

function updateInitiativeEntry(id) {
    let name = document.getElementById('initiative-name').value.trim();
    let value = parseInt(document.getElementById('initiative-value').value);
    
    if (!name || isNaN(value)) {
        alert('Заполните все поля!');
        return;
    }
    
    if (!/^[а-яё0-9\s]+$/i.test(name)) {
        alert('Используйте только кириллицу, цифры и пробелы!');
        return;
    }
    
    let entry = initiativeList.find(e => e.id === id);
    if (entry) {
        entry.name = name;
        entry.value = value;
        sortInitiativeList();
        openInitiativeModal();
    }
}

function deleteInitiativeEntry(id) {
    if (confirm('Удалить участника?')) {
        initiativeList = initiativeList.filter(e => e.id !== id);
        if (currentInitiativeIndex >= initiativeList.length) {
            currentInitiativeIndex = Math.max(0, initiativeList.length - 1);
        }
        updateInitiativeDisplay();
    }
}

function saveInitiativeNote() {
    if (initiativeList.length === 0) {
        alert('Нет участников для сохранения!');
        return;
    }
    
    let noteContent = '<div class="initiative-note">' +
        '<div class="initiative-note-title">Инициатива</div>';
    
    initiativeList.forEach((entry, index) => {
        let typeClass = entry.type === 'player' ? 'player-entry' : 
                       entry.type === 'enemy' ? 'enemy-entry' : 'other-entry';
        let isActive = index === currentInitiativeIndex ? ' active' : '';
        
        noteContent += '<div class="initiative-item ' + typeClass + isActive + '">' +
            '<div class="initiative-name">' + entry.name + '</div>' +
            '<div class="initiative-value">' + entry.value + '</div>' +
        '</div>';
    });
    
    noteContent += '</div>';
    
    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'fast_action=save_note&content=' + encodeURIComponent(noteContent)
    })
    .then(r => r.text())
    .then(() => {
        alert('Инициатива сохранена в заметки!');
        closeModal();
    });
}
// --- Форматирование результата NPC по смысловым блокам ---
function formatNpcBlocks(txt, forcedName = '') {
    // Проверяем, является ли это данными от рабочей API системы
    if (typeof txt === 'object' && txt.name && txt.race && txt.class) {
        return formatNpcFromWorkingApi(txt);
    }
    
    // Оригинальная логика для AI-генерации
    // Очищаем текст от лишних символов
    txt = txt.replace(/[\#\*`>\[\]]+/g, '');
    
    // Ищем блоки по заголовкам
    const blockTitles = [
        'Имя и Профессия', 'Описание', 'Внешность', 'Черты характера', 'Технические параметры'
    ];
    
    let blocks = [];
    let regex = /(Имя и Профессия|Описание|Внешность|Черты характера|Технические параметры)\s*[:\- ]/gi;
    let matches = [...txt.matchAll(regex)];
    
    if (matches.length > 0) {
        for (let i = 0; i < matches.length; i++) {
            let start = matches[i].index + matches[i][0].length;
            let end = (i + 1 < matches.length) ? matches[i + 1].index : txt.length;
            let title = matches[i][1];
            let content = txt.slice(start, end).replace(/^\s+|\s+$/g, '');
            if (content && content.length > 5) {
                blocks.push({ title, content });
            }
        }
    }
    let name = '', desc = '', appear = '', trait = '', techBlock = '';
    
    // Извлекаем данные из блоков
    for (let block of blocks) {
        if (block.title === 'Имя и Профессия') name = block.content;
        if (block.title === 'Описание') desc = block.content;
        if (block.title === 'Внешность') appear = block.content;
        if (block.title === 'Черты характера') trait = block.content;
        if (block.title === 'Технические параметры') techBlock = block.content;
    }
    
    // Если блоки не найдены, пытаемся извлечь данные из всего текста
    if (!name || !desc || !appear || !trait) {
        let lines = txt.split(/\n/).map(s => s.trim()).filter(Boolean);
        
            // Ищем имя в первой строке
    if (!name && lines.length > 0) {
        let firstLine = lines[0];
        if (firstLine.length < 50 && !firstLine.includes(':')) {
            name = firstLine;
        }
    }
    
    // Если имя не найдено, ищем его в описании (часто AI помещает имя туда)
    if (!name && desc) {
        let nameMatch = desc.match(/^([А-ЯЁ][а-яё]+(?:\s+[А-ЯЁ][а-яё]+)*)(?:\s*[,\-]\s*[а-яё\s]+)?/);
        if (nameMatch && nameMatch[1]) {
            name = nameMatch[1];
            // Убираем имя из описания
            desc = desc.replace(nameMatch[0], '').trim();
            desc = desc.replace(/^[,\s]+/, '').replace(/[,\s]+$/, '');
        }
    }
        
        // Ищем описание (обычно после имени)
        if (!desc && lines.length > 1) {
            for (let i = 1; i < Math.min(5, lines.length); i++) {
                let line = lines[i];
                if (line.length > 20 && line.length < 200 && 
                    !line.includes('Оружие:') && !line.includes('Урон:') && !line.includes('Хиты:')) {
                    desc = line;
                    break;
                }
            }
        }
        
        // Ищем внешность (описания внешнего вида)
        if (!appear) {
            for (let line of lines) {
                if (line.length > 15 && line.length < 150 &&
                    /высокий|низкий|стройный|полный|волосы|глаза|лицо|одежда|длинные|короткие|светлые|темные|крепкий|мужчина|плечи|руки|шрамы|фартук|хвост|серебристые|заплетённые|косы|ярко-голубые|проницательные|внешность|стройная|женщина|собранными|тёмными|пучок|форменном|платье|формария|глаза|следят|движения|точны|экономны|мускулистым|телосложением|покрытым|старыми|шрамами|доспехов|брони|зелёные|морской|волны|холодными|острыми|чертами|унаследованными|эльфийской|крови|внутренней|силой/i.test(line.toLowerCase()) &&
                    !line.includes('Оружие:') && !line.includes('Урон:') && !line.includes('Хиты:')) {
                    appear = line;
                    break;
                }
            }
        }
        
        // Ищем черты характера
        if (!trait) {
            for (let line of lines) {
                if (line.length > 10 && line.length < 100 &&
                    /харизматичный|проницательный|ответственный|надменный|артистичный|дипломатичный|преданный|терпеливый|внимательный|мечтательный|общительный|находчивый|рассеянный|хитрый|наблюдательный|амбициозный|осторожный|циничный|любознательный|обаятельный|нетерпеливый|наивный|агрессивный|мстительный|спокойный|вспыльчивый|добрый|злой|нейтральный/i.test(line.toLowerCase()) &&
                    !line.includes('Оружие:') && !line.includes('Урон:') && !line.includes('Хиты:')) {
                    trait = line;
                    break;
                }
            }
        }
    }
    
    // Если блоки не найдены, используем принудительное имя
    if (!name && forcedName) name = forcedName;
    
    // Очищаем блоки от лишних символов и форматирования
    if (name) name = name.replace(/[\[\]()]/g, '').trim();
    if (desc) desc = desc.replace(/[\[\]()]/g, '').trim();
    if (appear) appear = appear.replace(/[\[\]()]/g, '').trim();
    if (trait) trait = trait.replace(/[\[\]()]/g, '').trim();
    if (techBlock) techBlock = techBlock.replace(/[\[\]()]/g, '').trim();
    
    // Убираем имя из других блоков
    if (name) {
        let cleanName = name.split(/\s+/)[0].replace(/[^\wа-яё]/gi, '').trim();
        const nameRegex = new RegExp(cleanName + '\\s*', 'gi');
        
        if (trait && trait.includes(cleanName)) {
            trait = trait.replace(nameRegex, '').trim().replace(/^[,\s]+/, '').replace(/[,\s]+$/, '');
        }
        if (desc && desc.includes(cleanName)) {
            desc = desc.replace(nameRegex, '').trim().replace(/^[,\s]+/, '').replace(/[,\s]+$/, '');
        }
        if (appear && appear.includes(cleanName)) {
            appear = appear.replace(nameRegex, '').trim().replace(/^[,\s]+/, '').replace(/[,\s]+$/, '');
        }
    }
    
    // Убираем формальные ссылки на имя
    if (trait && trait.includes('Имя:')) {
        trait = trait.replace(/.*?Имя:\s*[^.]*\.?/i, '').trim();
    }
    if (desc && desc.includes('Имя:')) {
        desc = desc.replace(/.*?Имя:\s*[^.]*\.?/i, '').trim();
    }
    if (appear && appear.includes('Имя:')) {
        appear = appear.replace(/.*?Имя:\s*[^.]*\.?/i, '').trim();
    }
    
    // Если в описании черты характера - переносим
    if (desc && /черты характера|прямолинейный|наблюдательный|грубоватым|юмор|харизматичный|проницательный|ответственный|надменный|артистичный|дипломатичный|преданный|терпеливый|внимательный|мечтательный|общительный|находчивый|рассеянный|дикая|необузданная|натура|брала верх|духовное|воспитание|наставники|покинула|храм|найти путь|сочетая|ярость|варвара|глубокую связь|природой|дикая энергия|направлена|защиту|священных|поддержание|баланса|племенем|лесом/i.test(desc.toLowerCase())) {
        if (!trait || trait === '-') {
            trait = desc;
            desc = '';
        } else {
            // Если уже есть черты характера, объединяем
            trait = trait + '. ' + desc;
            desc = '';
        }
    }
    if (!name && forcedName) name = forcedName;
    // Извлечение технических параметров
    let summaryLines = [];
    let techParams = { weapon: '', damage: '', hp: '' };
    
    // Ищем технические параметры в блоке
    if (techBlock) {
        let lines = techBlock.split(/\n|\r/).map(s => s.trim()).filter(Boolean);
        for (let line of lines) {
            if (/оружие\s*:/i.test(line)) techParams.weapon = line;
            if (/урон\s*:/i.test(line)) techParams.damage = line;
            if (/хиты\s*:/i.test(line)) techParams.hp = line;
        }
        
        // Если технические параметры найдены, используем их полностью
        if (techBlock.length > 50) {
            techParams.fullBlock = techBlock;
        }
        
        // Проверяем, есть ли новые технические параметры (КД, характеристики и т.д.)
        if (techBlock.includes('Класс доспеха:') || techBlock.includes('Характеристики:') || techBlock.includes('Спасброски:')) {
            techParams.fullBlock = techBlock;
        }
    }
    // Проверяем наличие необходимых блоков
    if (!name) {
        return `<div class='npc-block-modern'><div class='npc-modern-header'>Ошибка генерации</div><div class='npc-modern-block'>AI не вернул имя персонажа. Попробуйте сгенерировать NPC ещё раз.</div></div>`;
    }
    
    // Если нет технических параметров, создаем подходящие для класса
    if (!techBlock || techBlock.length < 10) {
        let weapon, damage, hp;
        
        // Подбираем оружие и параметры в зависимости от класса
        switch(npcClass.toLowerCase()) {
            case 'воин':
            case 'варвар':
            case 'паладин':
                weapon = 'Меч';
                damage = '1d8 рубящий';
                hp = '15';
                break;
            case 'маг':
            case 'волшебник':
                weapon = 'Посох';
                damage = '1d6 дробящий';
                hp = '8';
                break;
            case 'лучник':
            case 'следопыт':
                weapon = 'Лук';
                damage = '1d8 колющий';
                hp = '12';
                break;
            case 'жрец':
            case 'друид':
                weapon = 'Булава';
                damage = '1d6 дробящий';
                hp = '10';
                break;
            case 'плут':
            case 'бард':
                weapon = 'Кинжал';
                damage = '1d4 колющий';
                hp = '8';
                break;
            default:
                weapon = 'Кулаки';
                damage = '1d4 дробящий';
                hp = '10';
        }
        
        techBlock = `Оружие: ${weapon}\nУрон: ${damage}\nХиты: ${hp}`;
    }
    
    // Формируем технические параметры
    if (techParams.weapon) summaryLines.push(techParams.weapon);
    if (techParams.damage) summaryLines.push(techParams.damage);
    if (techParams.hp) summaryLines.push(techParams.hp);
    
    // Проверяем наличие технических параметров
    const foundParams = [techParams.weapon, techParams.damage, techParams.hp].filter(p => p).length;
    if (foundParams < 2) {
        // Если параметров недостаточно, используем базовые
        if (!techParams.weapon) techParams.weapon = 'Оружие: Кулаки';
        if (!techParams.damage) techParams.damage = 'Урон: 1d4 дробящий';
        if (!techParams.hp) techParams.hp = 'Хиты: 10';
        summaryLines = [techParams.weapon, techParams.damage, techParams.hp];
    }
    
    function firstSentence(str) {
        if (!str || str === '-') return '';
        let m = str.match(/^[^.?!]+[.?!]?/);
        return m ? m[0].trim() : str.trim();
    }
    
    let out = '';
    out += `<div class='npc-block-modern'>`;
    
    // Очищаем имя и извлекаем только имя (без профессии)
    let cleanName = name;
    if (name.includes(',')) {
        cleanName = name.split(',')[0].trim();
    } else if (name.includes('-')) {
        cleanName = name.split('-')[0].trim();
    }
    cleanName = cleanName.split(/\s+/)[0].replace(/[^\wа-яё]/gi, '').trim();
    out += `<div class='npc-modern-header'>${cleanName || 'NPC'}</div>`;
    
    // Технические параметры (сворачиваемые)
    if (techParams.fullBlock) {
        // Используем полный блок технических параметров
        let techContent = techParams.fullBlock.replace(/\n/g, '<br>');
        out += `<div class='npc-col-block'>
            <div class='npc-collapsible-header collapsed' onclick='toggleTechnicalParams(this)'>
                <div><span style='font-size:1.2em;'>⚔️</span> <b>Технические параметры</b></div>
                <span class='toggle-icon'>▼</span>
            </div>
            <div class='npc-collapsible-content collapsed'>
                <div class='npc-content' style='font-family: monospace; font-size: 0.9em; white-space: pre-line; margin-top: 8px;'>${techContent}</div>
            </div>
        </div>`;
    } else if (summaryLines.length) {
        let listHtml = '<ul class="npc-modern-list">' + summaryLines.map(s => `<li>${s}</li>`).join('') + '</ul>';
        out += `<div class='npc-col-block'>
            <div class='npc-collapsible-header collapsed' onclick='toggleTechnicalParams(this)'>
                <div><span style='font-size:1.2em;'>⚔️</span> <b>Технические параметры</b></div>
                <span class='toggle-icon'>▼</span>
            </div>
            <div class='npc-collapsible-content collapsed'>
                <div style='margin-top: 8px;'>${listHtml}</div>
            </div>
        </div>`;
    }
    
    // Генерируем случайные fallback значения
    const fallbackDescriptions = [
        'Бывалый авантюрист с богатым опытом путешествий и приключений.',
        'Местный житель, знающий все тайны и слухи этого региона.',
        'Загадочный незнакомец, чье прошлое окутано тайной.',
        'Опытный мастер своего дела, пользующийся уважением среди местных.',
        'Молодой искатель приключений, жаждущий славы и богатства.'
    ];
    
    const fallbackTraits = [
        'Любознательный и наблюдательный, всегда интересуется новостями.',
        'Осторожный и расчетливый, не доверяет незнакомцам.',
        'Дружелюбный и общительный, легко находит общий язык с людьми.',
        'Гордый и независимый, ценит свою свободу превыше всего.',
        'Мудрый и терпеливый, предпочитает действовать обдуманно.'
    ];
    
    const fallbackAppearances = [
        'Среднего роста с крепким телосложением и уверенной походкой.',
        'Высокий и стройный, с острыми чертами лица и внимательным взглядом.',
        'Коренастый и сильный, с широкими плечами и грубыми руками.',
        'Элегантный и ухоженный, с аккуратной одеждой и хорошими манерами.',
        'Простой и неприметный, легко растворяется в толпе.'
    ];
    
    // Описание
    if (desc && desc.length > 10) {
        out += `<div class='npc-col-block'><span style='font-size:1.2em;'>📜</span> <b>Описание</b><div class='npc-content'>${firstSentence(desc)}</div></div>`;
    } else if (!desc || desc.length <= 10) {
        let randomDesc = fallbackDescriptions[Math.floor(Math.random() * fallbackDescriptions.length)];
        out += `<div class='npc-col-block'><span style='font-size:1.2em;'>📜</span> <b>Описание</b><div class='npc-content'>${randomDesc}</div></div>`;
    }
    
    // Черты характера
    if (trait && trait.length > 5) {
        out += `<div class='npc-col-block'><span style='font-size:1.2em;'>🧠</span> <b>Черты характера</b><div class='npc-content'>${firstSentence(trait)}</div></div>`;
    } else if (!trait || trait.length <= 5) {
        let randomTrait = fallbackTraits[Math.floor(Math.random() * fallbackTraits.length)];
        out += `<div class='npc-col-block'><span style='font-size:1.2em;'>🧠</span> <b>Черты характера</b><div class='npc-content'>${randomTrait}</div></div>`;
    }
    
    // Внешность
    if (appear && appear.length > 10) {
        out += `<div class='npc-col-block'><span style='font-size:1.2em;'>👤</span> <b>Внешность</b><div class='npc-content'>${firstSentence(appear)}</div></div>`;
    } else if (!appear || appear.length <= 10) {
        let randomAppear = fallbackAppearances[Math.floor(Math.random() * fallbackAppearances.length)];
        out += `<div class='npc-col-block'><span style='font-size:1.2em;'>👤</span> <b>Внешность</b><div class='npc-content'>${randomAppear}</div></div>`;
    }
    out += `</div>`;
    setTimeout(() => {
      document.querySelectorAll('.npc-desc-toggle-btn').forEach(btn => {
        btn.onclick = function() {
          let block = this.nextElementSibling;
          block.style.display = block.style.display === 'block' ? 'none' : 'block';
        };
      });
    }, 100);
    return out;
}

// --- Форматирование персонажей от API системы ---
function formatCharacterFromApi(character) {
    let out = '<div class="character-block">';
    
    // Заголовок с именем
    out += '<div class="character-header">';
    out += '<h3>' + character.name + '</h3>';
    out += '<div class="character-subtitle">' + character.race + ' - ' + character.class + ' (уровень ' + character.level + ')</div>';
    out += '</div>';
    
    // Основная информация
    out += '<div class="character-section">';
    out += '<div class="section-title">🏷️ Основная информация</div>';
    out += '<div class="section-content">';
    out += '<div class="info-grid">';
    out += '<div class="info-item"><strong>Мировоззрение:</strong> ' + character.alignment + '</div>';
    out += '<div class="info-item"><strong>Профессия:</strong> ' + character.profession + '</div>';
    out += '</div>';
    out += '</div></div>';
    
    // Описание
    if (character.description) {
        out += '<div class="character-section">';
        out += '<div class="section-title">📜 Описание</div>';
        out += '<div class="section-content">' + character.description + '</div>';
        out += '</div>';
    }
    
    // Внешность
    if (character.appearance) {
        out += '<div class="character-section">';
        out += '<div class="section-title">👤 Внешность</div>';
        out += '<div class="section-content">' + character.appearance + '</div>';
        out += '</div>';
    }
    
    // Технические параметры
    if (character.technical_params && character.technical_params.length > 0) {
        out += '<div class="character-section collapsible">';
        out += '<div class="section-title collapsible-header" onclick="toggleSection(this)">';
        out += '<span>⚔️ Технические параметры</span>';
        out += '<span class="toggle-icon">▼</span>';
        out += '</div>';
        out += '<div class="section-content collapsible-content">';
        out += '<ul class="param-list">';
        character.technical_params.forEach(param => {
            out += '<li>' + param + '</li>';
        });
        out += '</ul>';
        out += '</div></div>';
    }
    
    // Заклинания
    if (character.spells && Object.keys(character.spells).length > 0) {
        out += '<div class="character-section collapsible">';
        out += '<div class="section-title collapsible-header" onclick="toggleSection(this)">';
        out += '<span>🔮 Заклинания</span>';
        out += '<span class="toggle-icon">▼</span>';
        out += '</div>';
        out += '<div class="section-content collapsible-content">';
        
        Object.entries(character.spells).forEach(([level, spells]) => {
            const levelName = level === 'cantrips' ? 'Заговоры (0 уровень)' : 'Уровень ' + level.replace('level_', '');
            out += '<div class="spell-level">';
            out += '<h4>' + levelName + '</h4>';
            out += '<ul class="spell-list">';
            spells.forEach(spell => {
                out += '<li>' + spell + '</li>';
            });
            out += '</ul>';
            out += '</div>';
        });
        
        out += '</div></div>';
    }
    
    out += '</div>';
    return out;
}

// --- Форматирование противников от API системы ---
function formatEnemiesFromApi(enemies) {
    let out = '<div class="enemies-container">';
    
    enemies.forEach((enemy, index) => {
        out += '<div class="enemy-block">';
        
        // Заголовок с именем и CR
        out += '<div class="enemy-header">';
        out += '<h3>' + enemy.name + '</h3>';
        out += '<div class="enemy-cr">CR ' + enemy.challenge_rating + '</div>';
        out += '</div>';
        
        // Основная информация
        out += '<div class="enemy-section">';
        out += '<div class="section-title">🏷️ Основная информация</div>';
        out += '<div class="section-content">';
        out += '<div class="info-grid">';
        out += '<div class="info-item"><strong>Тип:</strong> ' + enemy.type + '</div>';
        out += '<div class="info-item"><strong>Размер:</strong> ' + enemy.size + '</div>';
        out += '<div class="info-item"><strong>Мировоззрение:</strong> ' + enemy.alignment + '</div>';
        out += '<div class="info-item"><strong>Среда:</strong> ' + enemy.environment + '</div>';
        out += '</div>';
        out += '</div></div>';
        
        // Описание
        if (enemy.description) {
            out += '<div class="enemy-section">';
            out += '<div class="section-title">📜 Описание</div>';
            out += '<div class="section-content">' + enemy.description + '</div>';
            out += '</div>';
        }
        
        // Характеристики
        if (enemy.abilities) {
            out += '<div class="enemy-section">';
            out += '<div class="section-title">⚔️ Характеристики</div>';
            out += '<div class="section-content">';
            out += '<div class="abilities-grid">';
            out += '<div class="ability-item"><strong>СИЛ:</strong> ' + enemy.abilities.str + '</div>';
            out += '<div class="ability-item"><strong>ЛОВ:</strong> ' + enemy.abilities.dex + '</div>';
            out += '<div class="ability-item"><strong>ТЕЛ:</strong> ' + enemy.abilities.con + '</div>';
            out += '<div class="ability-item"><strong>ИНТ:</strong> ' + enemy.abilities.int + '</div>';
            out += '<div class="ability-item"><strong>МДР:</strong> ' + enemy.abilities.wis + '</div>';
            out += '<div class="ability-item"><strong>ХАР:</strong> ' + enemy.abilities.cha + '</div>';
            out += '</div>';
            out += '</div></div>';
        }
        
        // Боевые параметры
        if (enemy.combat_stats) {
            out += '<div class="enemy-section collapsible">';
            out += '<div class="section-title collapsible-header" onclick="toggleSection(this)">';
            out += '<span>⚔️ Боевые параметры</span>';
            out += '<span class="toggle-icon">▼</span>';
            out += '</div>';
            out += '<div class="section-content collapsible-content">';
            out += '<ul class="param-list">';
            Object.entries(enemy.combat_stats).forEach(([key, value]) => {
                out += '<li><strong>' + key + ':</strong> ' + value + '</li>';
            });
            out += '</ul>';
            out += '</div></div>';
        }
        
        // Действия
        if (enemy.actions && enemy.actions.length > 0) {
            out += '<div class="enemy-section collapsible">';
            out += '<div class="section-title collapsible-header" onclick="toggleSection(this)">';
            out += '<span>⚡ Действия</span>';
            out += '<span class="toggle-icon">▼</span>';
            out += '</div>';
            out += '<div class="section-content collapsible-content">';
            out += '<ul class="action-list">';
            enemy.actions.forEach(action => {
                out += '<li><strong>' + action.name + ':</strong> ' + action.description + '</li>';
            });
            out += '</ul>';
            out += '</div></div>';
        }
        
        // Тактика (если есть AI-описание)
        if (enemy.tactics) {
            out += '<div class="enemy-section">';
            out += '<div class="section-title">🧠 Тактика</div>';
            out += '<div class="section-content">' + enemy.tactics + '</div>';
            out += '</div>';
        }
        
        out += '</div>';
        
        // Разделитель между противниками
        if (index < enemies.length - 1) {
            out += '<div class="enemy-separator"></div>';
        }
    });
    
    out += '</div>';
    return out;
}

// --- Форматирование результата бросков ---
function formatResultSegments(txt, isNpc) {
    if (isNpc) {
        return formatNpcBlocks(txt);
    } else {
        // Для бросков: бросок+результаты, сумма, комментарий (если есть)
        const lines = txt.split(/<br>|\n/).map(l => l.trim()).filter(Boolean);
        let out = '';
        if (lines.length) {
            out += `<div class="result-segment">${lines[0]}</div>`;
        }
        if (lines.length > 1) {
            out += `<div class="result-segment-alt">${lines[1]}</div>`;
        }
        if (lines.length > 2) {
            out += `<div class="result-segment">${lines.slice(2).join('<br>')}</div>`;
        }
        return out;
    }
}
// --- Modal & Notes ---
function showModal(content) {
    document.getElementById('modal-content').innerHTML = content;
    document.getElementById('modal-bg').classList.add('active');
}
function closeModal() {
    document.getElementById('modal-bg').classList.remove('active');
    // Удаляем кнопку повторной генерации при закрытии
    let regenerateBtn = document.querySelector('.modal-regenerate');
    if (regenerateBtn) {
        regenerateBtn.remove();
    }
}
document.getElementById('modal-close').onclick = closeModal;
document.getElementById('modal-bg').onclick = function(e) { if (e.target === this) closeModal(); };
function saveNote(content) {
    // Сохраняем HTML содержимого модального окна
    var content = document.getElementById('modal-content').innerHTML;
    
    // Извлекаем имя NPC из заголовка
    var headerElement = document.querySelector('.npc-modern-header');
    var npcName = headerElement ? headerElement.textContent.trim() : 'NPC';
    
    // Если имя пустое или "NPC", пытаемся найти имя в содержимом
    if (!npcName || npcName === 'NPC') {
        // Ищем имя в тексте содержимого
        var plainText = content.replace(/<[^>]+>/g, '\n');
        var lines = plainText.split(/\n/).map(l => l.trim()).filter(Boolean);
        
        for (var i = 0; i < lines.length; i++) {
            var line = lines[i];
            if (line && line.length > 2 && line.length < 30 && 
                !/^(описание|внешность|черты|способность|оружие|урон|хиты|класс|раса|уровень|профессия|технические)/i.test(line) &&
                !line.includes(':') && !line.includes('—')) {
                npcName = line;
                break;
            }
        }
    }
    
    // Очищаем имя от лишних слов (только первое слово с большой буквы)
    if (npcName && npcName !== 'NPC') {
        var words = npcName.split(/\s+/);
        if (words.length > 1) {
            // Берем только первое слово как имя
            npcName = words[0];
        }
        // Убираем лишние символы
        npcName = npcName.replace(/[^\wа-яё]/gi, '').trim();
    }
    
    // Добавляем имя в начало заметки для лучшей идентификации
    var noteWithName = '<div class="npc-name-header">' + npcName + '</div>' + content;
    
    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'add_note=1&note_content=' + encodeURIComponent(noteWithName)
    }).then(() => location.reload());
}
function removeNote(idx) {
    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'remove_note=' + encodeURIComponent(idx)
    }).then(() => location.reload());
}
function expandNote(idx) {
    if (window.allNotes && window.allNotes[idx]) {
        var content = window.allNotes[idx];
        if (content && content.trim()) {
            // Убираем дублирующий заголовок имени из начала заметки
            var cleanContent = content;
            var nameHeaderMatch = content.match(/<div class="npc-name-header">([^<]+)<\/div>/i);
            if (nameHeaderMatch) {
                // Убираем заголовок имени из начала
                cleanContent = content.replace(/<div class="npc-name-header">[^<]+<\/div>/i, '');
                // Убираем лишние пробелы в начале
                cleanContent = cleanContent.replace(/^\s+/, '');
            }
            
            document.getElementById('modal-content').innerHTML = cleanContent;
            document.getElementById('modal-bg').classList.add('active');
            document.getElementById('modal-save').style.display = 'none';
        }
    }
}
// Передаём все заметки в JS
window.allNotes = <?php echo json_encode($_SESSION['notes'], JSON_UNESCAPED_UNICODE); ?>;
// Debug: выводим первую строку каждой заметки в консоль
if (window.allNotes) {
    window.allNotes.forEach((n, i) => {
        let plain = n.replace(/<[^>]+>/g, '\n');
        let lines = plain.split(/\n/).map(l => l.trim()).filter(Boolean);
        
        // Ищем имя NPC в специальном заголовке
        let nameMatch = n.match(/<div class="npc-name-header">([^<]+)<\/div>/i);
        let headerMatch = n.match(/<div class="npc-modern-header">([^<]+)<\/div>/i);
        let nameLine = lines.find(l => /^(Имя|Name|Имя NPC|Имя персонажа)\s*:/i.test(l));
        
        let preview = '';
        if (nameMatch) {
            preview = nameMatch[1].trim();
        } else if (headerMatch) {
            preview = headerMatch[1].trim();
        } else if (nameLine) {
            let match = nameLine.match(/^(Имя|Name|Имя NPC|Имя персонажа)\s*:\s*(.+)$/i);
            preview = match ? match[2].trim() : nameLine;
        } else {
            // Ищем первое значимое слово
            for (let line of lines) {
                if (line && !/^(описание|внешность|черты|способность|оружие|урон|хиты|класс|раса|уровень|профессия)/i.test(line)) {
                    preview = line;
                    break;
                }
            }
            if (!preview && lines.length) {
                preview = lines[0];
            }
        }
        
        // Очищаем превью от лишних слов
        preview = preview.replace(/^описание\s+/i, '').replace(/^\s*—\s*/, '').replace(/^npc\s+/i, '');
        
        // Очищаем превью - берем только первое слово если это имя
        if (nameMatch || headerMatch) {
            let words = preview.split(/\s+/);
            if (words.length > 1) {
                preview = words[0];
            }
        }
        
        console.log('Заметка', i, 'превью:', preview || '(нет данных)');
    });
}
// --- Чат: отправка сообщения ---
document.querySelector('form').onsubmit = function(e) {
    e.preventDefault();
    var msg = this.message.value.trim();
    if (!msg) return false;
    fetch('ai.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'prompt=' + encodeURIComponent(msg) + '&type=chat'
    })
    .then(r => r.json())
    .then(data => {
        if (data && data.result) {
            // Добавить сообщение в чат (можно обновить страницу или динамически)
            location.reload();
        } else {
            alert(data.error || 'Ошибка AI');
        }
    });
    return false;
};

        // Показываем приветственное сообщение для новых пользователей
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('welcome') === '1') {
            showWelcomeMessage();
        }

        // Горячие клавиши для быстрого доступа
        document.addEventListener('keydown', function(e) {
            // Ctrl+Enter для отправки сообщения
            if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('chatForm').submit();
            }
            
            // F1 для броска костей
            if (e.key === 'F1') {
                e.preventDefault();
                openDiceStep1();
            }
            
            // F2 для генерации персонажей
            if (e.key === 'F2') {
                e.preventDefault();
                openCharacterModal();
            }
            
            // F4 для генерации противников
            if (e.key === 'F4') {
                e.preventDefault();
                openEnemyModal();
            }
            
            // F3 для инициативы
            if (e.key === 'F3') {
                e.preventDefault();
                openInitiativeModal();
            }
            
            // Escape для закрытия модального окна
            if (e.key === 'Escape') {
                const modal = document.getElementById('modal-bg');
                if (modal && modal.classList.contains('active')) {
                    closeModal();
                }
            }
        });
        
        // --- Функция для переключения сворачиваемых секций ---
        function toggleSection(headerElement) {
            const contentElement = headerElement.nextElementSibling;
            const isCollapsed = headerElement.classList.contains('collapsed');
            
            if (isCollapsed) {
                // Разворачиваем
                headerElement.classList.remove('collapsed');
                contentElement.classList.remove('collapsed');
            } else {
                // Сворачиваем
                headerElement.classList.add('collapsed');
                contentElement.classList.add('collapsed');
            }
        }
        
        // --- Функция для переключения сворачиваемых технических параметров (для обратной совместимости) ---
        function toggleTechnicalParams(headerElement) {
            toggleSection(headerElement);
        }
        

</script>
