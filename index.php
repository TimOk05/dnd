<?php
session_start();
// --- Секретный код ---
$SECRET_CODE = 'dndmaster';
if (!isset($_SESSION['access_granted'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['secret_code'])) {
        if (trim($_POST['secret_code']) === $SECRET_CODE) {
            $_SESSION['access_granted'] = true;
            header('Location: index.php');
            exit;
        } else {
            $error = 'Неверный код!';
        }
    }
    echo '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><title>Вход</title><style>body{background:#f8ecd0;font-family:Roboto,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;}form{background:#fffbe6;border:2px solid #a67c52;border-radius:12px;padding:32px 28px;box-shadow:0 4px 24px #0002;}input{padding:10px 18px;border-radius:8px;border:2px solid #a67c52;font-size:1.1em;}button{padding:10px 22px;border-radius:8px;border:2px solid #7c4a02;background:#a67c52;color:#fffbe6;font-size:1.1em;cursor:pointer;margin-left:8px;}button:hover{background:#7c4a02;color:#ffe0a3;}h2{margin-bottom:18px;}label{font-size:1.1em;}</style></head><body><form method="post"><h2>Вход в DnD Copilot</h2><label>Секретный код:<br><input type="password" name="secret_code" autofocus required></label><button type="submit">Войти</button>';
    if (isset($error)) echo '<div style="color:#b71c1c;margin-top:12px;">'.$error.'</div>';
    echo '</form></body></html>';
    exit;
}

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
$systemInstruction = "Ты — помощник мастера DnD. Твоя задача — сгенерировать NPC для быстрого и удобного вывода в игровом приложении. Каждый блок будет отображаться отдельно, поэтому не добавляй пояснений, не используй лишние слова, не пиши ничего кроме блоков.\nСтрого по шаблону, каждый блок с новой строки:\nИмя: ...\nКраткое описание: ...\nЧерта характера: ...\nСлабость: ...\nКороткая характеристика: Оружие: ... Урон: ... Хиты: ... Способность: ...\nТехнические параметры (Оружие, Урон, Хиты, Способность) обязательны и всегда идут первым блоком. Если не можешь заполнить какой-то параметр — напиши ‘-’. Не добавляй ничего лишнего.";
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
$fastBtns .= '<button class="fast-btn" onclick="openNpcStep1()">🗣️ NPC</button>';

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
    
    // Сначала ищем в специальном заголовке
    if (preg_match('/<div class="npc-name-header">([^<]+)<\/div>/iu', $note, $matches)) {
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
    
    $previewSrc = $nameLine ?: (count($lines) ? $lines[0] : '(нет данных)');
    // Обрезаем превью до 30 символов или 3 слов
    $words = preg_split('/\s+/', $previewSrc);
    if (count($words) > 3) {
        $preview = implode(' ', array_slice($words, 0, 3)) . '…';
    } else if (mb_strlen($previewSrc) > 30) {
        $preview = mb_substr($previewSrc, 0, 30) . '…';
    } else {
        $preview = $previewSrc;
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
// --- NPC Modal Steps ---
const npcRaces = ['Человек','Эльф','Гном','Полуорк','Полурослик','Тифлинг','Драконорожденный','Полуэльф','Дворф','Гоблин','Орк','Кобольд','Ящеролюд','Гоблин','Гном','Хоббит'];
const npcClasses = ['Без класса','Воин','Паладин','Колдун','Маг','Разбойник','Следопыт','Жрец','Бард','Варвар','Плут','Монах','Чародей','Друид'];
// Убираем массив профессий - AI сам выберет
let npcRace = '', npcClass = '', npcProf = '', npcLevel = 1;
let lastGeneratedParams = {}; // Для хранения параметров последней генерации
function openNpcStep1() {
    showModal('<b class="mini-menu-title">Выберите расу NPC:</b><div class="mini-menu-btns">' + npcRaces.map(r => `<button onclick=\'openNpcStep2("${r}")\' class=\'fast-btn\'>${r}</button>`).join(' ') + '</div>');
    document.getElementById('modal-save').style.display = 'none';
}
function openNpcStep2(race) {
    npcRace = race;
    showModal('<b class="mini-menu-title">Выберите класс NPC:</b><div class="mini-menu-btns">' + npcClasses.map(c => `<button onclick=\'openNpcStepLevel("${c}")\' class=\'fast-btn\'>${c}</button>`).join(' ') + '</div>');
    document.getElementById('modal-save').style.display = 'none';
}
function openNpcStepLevel(cls) {
    npcClass = cls;
    showModal('<b class="mini-menu-title">Укажите уровень NPC (1-20):</b><div class="npc-level-wrap"><input type=number id=npc-level value=1 min=1 max=20 style=\'width:60px\'></div><button class=\'fast-btn\' onclick=\'generateNpcWithLevel()\'>Создать NPC</button>');
    document.getElementById('modal-save').style.display = 'none';
}
// --- Загрузка базы уникальных торговцев ---
window.uniqueTraders = [];
fetch('pdf/d100_unique_traders.json')
  .then(r => r.json())
  .then(data => { window.uniqueTraders = data; });
function fetchNpcFromAI(race, npcClass, prof, level) {
    showModal('Генерация NPC...');
    fetch('pdf/d100_unique_traders.json')
      .then(r => r.json())
      .then(json => {
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
        let namePool = raceNames[raceKey] || raceNames['человек'];
        name = namePool[Math.floor(Math.random() * namePool.length)];
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
        const systemInstruction = 'Всегда пиши ответы без оформления, без markdown, без кавычек и звёздочек. Разделяй результат NPC на смысловые блоки с заголовками: Описание, Внешность, Черты характера, Короткая характеристика. В блоке Короткая характеристика обязательно выведи отдельными строками: Оружие: [название оружия], Урон: [формат урона, например 1d6], Хиты: [количество хитов], Способность: [основная способность]. Черты характера - это личностные качества (храбрый, мудрый, вспыльчивый). Описание должно быть кратким и содержать основную информацию о персонаже. Внешность - описание внешнего вида. Придумай подходящую профессию для NPC. Каждый блок начинай с заголовка. Технические параметры обязательны!';
        const prompt = `Создай NPC для DnD. Раса: ${race}. Класс: ${npcClass}. Уровень: ${level}. Придумай подходящую профессию для этого персонажа.${contextBlock}`;
        fetch('ai.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'prompt=' + encodeURIComponent(prompt) + '&system=' + encodeURIComponent(systemInstruction) + '&type=npc'
        })
        .then(r => r.json())
        .then(data => {
            if (data && data.result) {
                // Отладочная информация
                console.log('AI Response:', data.result);
                document.getElementById('modal-content').innerHTML = formatNpcBlocks(data.result, name);
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
                document.getElementById('modal-content').innerHTML = '<div class="result-segment">[Ошибка AI: ' + (data.error || 'нет ответа') + ']</div>';
                document.getElementById('modal-save').style.display = 'none';
            }
        })
        .catch((e) => {
            document.getElementById('modal-content').innerHTML = '<div class="result-segment">[Ошибка соединения с сервером]</div>';
            document.getElementById('modal-save').style.display = 'none';
        });
      });
}
function generateNpcWithLevel() {
    npcLevel = document.getElementById('npc-level').value;
    // Сохраняем параметры для повторной генерации
    lastGeneratedParams = {
        race: npcRace,
        class: npcClass,
        level: npcLevel
    };
    // AI сам выберет профессию
    fetchNpcFromAI(npcRace, npcClass, '', npcLevel);
}

function regenerateNpc() {
    if (lastGeneratedParams.race && lastGeneratedParams.class && lastGeneratedParams.level) {
        fetchNpcFromAI(lastGeneratedParams.race, lastGeneratedParams.class, '', lastGeneratedParams.level);
    } else {
        alert('Нет сохраненных параметров для повторной генерации');
    }
}
// --- Форматирование результата NPC по смысловым блокам ---
function formatNpcBlocks(txt, forcedName = '') {
    // Функция для извлечения только названия способности
    function extractAbilityName(text) {
        let lowerText = text.toLowerCase();
        
        // Если текст слишком длинный, это скорее всего описание, а не название
        if (text.length > 60) {
            return null;
        }
        
        // Ищем короткие названия способностей
        let abilityPatterns = [
            /(стихийн\s+удар)/i,
            /(ярость)/i,
            /(неистовая\s+ярость)/i,
            /(маги\w*\s+барда)/i,
            /(вдохновение)/i,
            /(манипуляция)/i,
            /(боевой\s+стиль)/i,
            /(защита)/i,
            /(атака)/i,
            /(оборона)/i,
            /(предсказание)/i,
            /(интуиция)/i,
            /(маги\w*)/i,
            /(заклинани\w*)/i,
            /(божественная\s+кара)/i,
            /(лечение)/i,
            /(исцеление)/i,
            /(невидимость)/i,
            /(телепортация)/i,
            /(иллюзия)/i,
            /(превращение)/i,
            /(призыв)/i,
            /(контроль)/i,
            /(проклятие)/i,
            /(благословение)/i
        ];
        
        for (let pattern of abilityPatterns) {
            let match = text.match(pattern);
            if (match) {
                return match[1] || match[0];
            }
        }
        
        // Если не нашли паттерн, но текст содержит ключевые слова способностей
        if (/маги|стихийн|удар|ярость|вдохновение|защита|атака|предсказание|интуиция|способност|лечение|исцеление|невидимость|телепортация|иллюзия|превращение|призыв|контроль|проклятие|благословение|кара|стиль/i.test(lowerText)) {
            // Берем первые 2-3 слова как название способности
            let words = text.split(/\s+/).slice(0, 3).join(' ');
            if (words.length > 3 && words.length < 40) {
                return words;
            }
        }
        
        return null;
    }
    
    txt = txt.replace(/[\#\*`>]+/g, '');
    const blockTitles = [
        'Имя', 'Раса', 'Класс', 'Краткое описание', 'Черта характера', 'Слабость', 'Короткая характеристика', 'Описание', 'Внешность', 'Особенности поведения'
    ];
    let blocks = [];
    let regex = /(Имя|Раса|Класс|Краткое описание|Черта характера|Слабость|Короткая характеристика|Описание|Внешность|Особенности поведения)\s*[:\- ]/gi;
    let matches = [...txt.matchAll(regex)];
    if (matches.length > 0) {
        for (let i = 0; i < matches.length; i++) {
            let start = matches[i].index + matches[i][0].length;
            let end = (i + 1 < matches.length) ? matches[i + 1].index : txt.length;
            let title = matches[i][1];
            let content = txt.slice(start, end).replace(/^\s+|\s+$/g, '');
            if (content) blocks.push({ title, content });
        }
    }
    let name = '', race = '', cls = '', shortdesc = '', trait = '', weakness = '', summary = '', desc = '', appear = '', behavior = '', other = '';
    if (blocks.length === 0) {
        let sentences = txt.split(/(?<=[.!?])\s+/);
        if (sentences.length > 0) name = sentences[0];
        if (sentences.length > 1) shortdesc = sentences[1];
        if (sentences.length > 2) trait = sentences[2];
        if (sentences.length > 3) weakness = sentences[3];
        if (sentences.length > 4) summary = sentences[4];
        if (sentences.length > 5) desc = sentences.slice(5).join(' ');
    } else {
        for (let block of blocks) {
            if (block.title === 'Имя') name = block.content;
            if (block.title === 'Раса') race = block.content;
            if (block.title === 'Класс') cls = block.content;
            if (block.title === 'Краткое описание') shortdesc = block.content;
            if (block.title === 'Черта характера') trait = block.content;
            if (block.title === 'Слабость') weakness = block.content;
            if (block.title === 'Короткая характеристика') summary = block.content;
            if (block.title === 'Описание') desc = block.content;
            if (block.title === 'Внешность') appear = block.content;
            if (block.title === 'Особенности поведения') behavior = block.content;
        }
    }
    
    // Исправляем неправильную классификацию блоков
    if (trait && /служит|академи|обучает|преподает|мастерская|мешок|инструменты|станок|разбирает|носит|мечтает|стать|известным|советником|влияния|события|работает|рынке|призвание|собирать|слухи|истории|рубит|мясо|управляет|лавкой|продаёт|продукты|травы|ингредиенты/i.test(trait)) {
        // Это не черта характера, а описание деятельности, целей или предметов
        if (!desc) desc = trait;
        trait = '';
    }
    
    if (weakness && /преданность|ценный|союзник|знания|стабильности/i.test(weakness)) {
        // Это не слабость, а положительная характеристика
        if (!trait) trait = weakness;
        weakness = '';
    }
    
    // Если в слабости внешность - убираем слабость
    if (weakness && /высокий|низкий|стройный|полный|волосы|глаза|лицо|одежда/i.test(weakness)) {
        if (!appear) appear = weakness;
        weakness = '';
    }
    
    // Если в черте характера описание внешности - переносим
    if (trait && /высокий|низкий|стройный|полный|волосы|глаза|лицо|одежда|длинные|короткие|светлые|темные|крепкий|мужчина|плечи|руки|шрамы|фартук|хвост|внешность|стройная|женщина|собранными|тёмными|волосами|пучок|форменном|платье|формария|следят|движения|точны|экономны|кулон|амулет|кольцо|ожерелье|браслет|пояс|мешок|зерно|носит|висит|за спиной|на поясе|на шее/i.test(trait)) {
        if (!appear) appear = trait;
        trait = '';
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
    // Улучшенное извлечение технических параметров
    let summaryLines = [];
    let techParams = { weapon: '', damage: '', hp: '', ability: '' };
    
    // 1. Сначала ищем в блоке "Короткая характеристика"
    if (summary && summary !== '-') {
        let lines = summary.split(/\n|\r|•|-/).map(s => s.trim()).filter(Boolean);
        for (let line of lines) {
            if (/оружие|weapon/i.test(line)) techParams.weapon = line;
            if (/урон|damage/i.test(line)) techParams.damage = line;
            if (/хиты|hp|здоровье|health/i.test(line)) techParams.hp = line;
            if (/способност|ability|skill/i.test(line)) techParams.ability = line;
        }
    }
    
    // 2. Если не нашли в блоке, ищем во всем тексте
    if (!techParams.weapon || !techParams.damage || !techParams.hp || !techParams.ability) {
        let allText = txt.toLowerCase();
        let lines = txt.split(/\n|\r|•|-/).map(s => s.trim()).filter(Boolean);
        
        for (let line of lines) {
            let lineLower = line.toLowerCase();
            // Ищем только краткие технические параметры
            if (!techParams.weapon && /оружие\s*:/i.test(lineLower) && line.length < 50) {
                techParams.weapon = line;
            }
            if (!techParams.damage && /урон\s*:/i.test(lineLower) && line.length < 30) {
                techParams.damage = line;
            }
            if (!techParams.hp && /хиты\s*:/i.test(lineLower) && line.length < 30) {
                techParams.hp = line;
            }
            if (!techParams.ability && /способност\s*:/i.test(lineLower) && line.length < 100) {
                // Очищаем способность от дублирования технических параметров
                let cleanAbility = line;
                
                // Убираем только если есть явное дублирование
                if (cleanAbility.includes('Короткая характеристика')) {
                    cleanAbility = cleanAbility.replace(/короткая характеристика.*?способность\s*:/i, 'Способность:').trim();
                }
                
                // Убираем повторение оружия, урона и хитов только если они есть
                if (cleanAbility.includes('Оружие:') && cleanAbility.includes('Хиты:')) {
                    cleanAbility = cleanAbility.replace(/оружие\s*:.*?хиты\s*:\s*\d+/i, '').trim();
                }
                
                // Убираем повторение способности в конце
                if (cleanAbility.includes('Способность:') && cleanAbility.split('Способность:').length > 2) {
                    let parts = cleanAbility.split('Способность:');
                    cleanAbility = 'Способность:' + parts[parts.length - 1];
                }
                
                // Радикальная очистка от дублирования технических параметров
                if (cleanAbility.includes('Короткая характеристика')) {
                    // Ищем последнее вхождение "Способность:" и берем только его
                    let lastAbilityIndex = cleanAbility.lastIndexOf('Способность:');
                    if (lastAbilityIndex !== -1) {
                        cleanAbility = cleanAbility.substring(lastAbilityIndex);
                    }
                    
                    // Если все еще есть дублирование оружия, убираем его
                    if (cleanAbility.includes('Оружие:')) {
                        let weaponIndex = cleanAbility.indexOf('Оружие:');
                        if (weaponIndex > 0) {
                            cleanAbility = cleanAbility.substring(0, weaponIndex).trim();
                        }
                    }
                }
                
                // Убираем повторение способности только если оно есть
                if ((cleanAbility.match(/способность\s*:/gi) || []).length > 1) {
                    cleanAbility = cleanAbility.replace(/способность\s*:.*?способность\s*:/i, 'Способность:').trim();
                }
                
                // Убираем лишние пробелы и точки
                cleanAbility = cleanAbility.replace(/\s+/g, ' ').replace(/^\s+|\s+$/g, '');
                
                // Проверяем, что способность не пустая и не содержит дублирования
                if (cleanAbility.length > 10) {
                                    // Финальная проверка на дублирование
                if (cleanAbility.includes('Оружие:') || cleanAbility.includes('Хиты:') || cleanAbility.includes('Урон:')) {
                    // Если все еще есть дублирование технических параметров, убираем их
                    let parts = cleanAbility.split(/\s+(?:Оружие|Урон|Хиты):/);
                    if (parts.length > 1) {
                        cleanAbility = parts[0].trim();
                    }
                    
                    // Если способность стала слишком короткой, ищем альтернативу
                    if (cleanAbility.length < 5) {
                        cleanAbility = null;
                    }
                }
                    techParams.ability = cleanAbility;
                }
            }
        }
    }
    
    // 3. Если способность не найдена, ищем в описании
    if (!techParams.ability && desc) {
        let descLines = desc.split(/[.!?]/).map(s => s.trim()).filter(Boolean);
        for (let line of descLines) {
            // Ищем способности в описании
            if (/стихийн|удар|способност|маги|заклинани|ярость|неистовая|барда|вдохновение|манипуляция|шантаж|компрометирующей|информации|боевой стиль|защита|атака|оборона|лечение|исцеление|невидимость|телепортация|иллюзия|превращение|призыв|контроль|проклятие|благословение|кара/i.test(line.toLowerCase()) && line.length < 80) {
                // Извлекаем только название способности, а не полное описание
                let abilityName = extractAbilityName(line);
                if (abilityName) {
                    techParams.ability = 'Способность: ' + abilityName;
                    break;
                }
            }
        }
    }
    
    // 4. Если способность все еще не найдена, ищем во всем тексте
    if (!techParams.ability) {
        let allText = txt.toLowerCase();
        let lines = txt.split(/[.!?]/).map(s => s.trim()).filter(Boolean);
        
        for (let line of lines) {
            let lineLower = line.toLowerCase();
            if (/ярость|неистовая|барда|вдохновение|манипуляция|шантаж|компрометирующей|информации|стихийн|удар|способност|маги|заклинани|боевой стиль|защита|атака|оборона|лечение|исцеление|невидимость|телепортация|иллюзия|превращение|призыв|контроль|проклятие|благословение|кара/i.test(lineLower) && line.length > 5 && line.length < 80) {
                // Извлекаем только название способности, а не полное описание
                let abilityName = extractAbilityName(line);
                if (abilityName) {
                    techParams.ability = 'Способность: ' + abilityName;
                    break;
                }
            }
        }
    }
    
    // 5. Ищем внешность в тексте, если не найдена в блоках
    if (!appear || appear === '-') {
        let allText = txt.toLowerCase();
        let lines = txt.split(/[.!?]/).map(s => s.trim()).filter(Boolean);
        
        for (let line of lines) {
            let lineLower = line.toLowerCase();
            if (/высокий|низкий|стройный|полный|волосы|глаза|лицо|одежда|длинные|короткие|светлые|темные|красивые|острые|широкие|узкие|борода|усы|морщины|крепкий|мужчина|плечи|руки|шрамы|фартук|хвост|серебристые|заплетённые|косы|ярко-голубые|проницательные|внешность|стройная|женщина|собранными|тёмными|пучок|форменном|платье|формария|следят|движения|точны|экономны|мускулистым|телосложением|покрытым|старыми|шрамами|доспехов|брони/i.test(lineLower) && line.length > 5 && line.length < 250) {
                if (!appear || appear === '-') {
                    appear = line;
                } else {
                    // Объединяем описания внешности
                    appear = appear + '. ' + line;
                }
            }
        }
    }
    
    // 5.5. Резервный поиск способности - если не нашли, ищем любую строку с ключевыми словами
    if (!techParams.ability) {
        let allLines = txt.split(/[.!?]/).map(s => s.trim()).filter(Boolean);
        for (let line of allLines) {
            let lineLower = line.toLowerCase();
            if (/способност|маги|стихийн|удар|ярость|вдохновение|защита|атака|лечение|исцеление|невидимость|телепортация|иллюзия|превращение|призыв|контроль|проклятие|благословение|кара|стиль/i.test(lineLower) && line.length > 10 && line.length < 100) {
                // Берем первые 3-4 слова как способность
                let words = line.split(/\s+/).slice(0, 4).join(' ');
                if (words.length > 5 && words.length < 50) {
                    techParams.ability = 'Способность: ' + words;
                    break;
                }
            }
        }
    }
    
    // 6. Очищаем описание и извлекаем прочее
    if (desc) {
        let descLines = desc.split(/[.!?]/).map(s => s.trim()).filter(Boolean);
        let cleanLines = [];
        let otherLines = [];
        
        for (let line of descLines) {
            let lineLower = line.toLowerCase();
            // Пропускаем строки с техническими параметрами, длинные описания и описания рас
            if (/оружие|урон|хиты|способност|стихийн|удар|d\d+|1d\d+|2d\d+/i.test(lineLower) || 
                line.length > 200 || 
                /эльфийка|эльф|человек|гном|полуорк|полурослик|тифлинг|драконорожденный|полуэльф|дворф|гоблин|орк|кобольд|ящеролюд|хоббит|который|которая|нашел|нашла|оставил|оставила/i.test(lineLower)) {
                continue;
            }
            
            // Если строка содержит черты характера - переносим в прочее
            if (/харизматичный|проницательный|ответственный|надменный|артистичный|дипломатичный|преданный|терпеливый|внимательный|мечтательный|общительный|находчивый|рассеянный|хитрый|наблюдательный|амбициозный|осторожный|циничный/i.test(lineLower)) {
                otherLines.push(line);
            } else {
                cleanLines.push(line);
            }
        }
        
        desc = cleanLines.join('. ');
        if (desc.endsWith('. ')) desc = desc.slice(0, -2);
        
        if (otherLines.length > 0) {
            other = otherLines.join('. ');
        }
    }
    
    // 7. Если в блоке "Дополнительно" есть черты характера, переносим их в "Черта характера"
    if (other && /черты характера|любознательный|обаятельный|нетерпеливый|преданный|наивный|хитрый|наблюдательный|амбициозный|артистичный|осторожный|циничный|обаятельный/i.test(other.toLowerCase())) {
        if (!trait || trait === '-') {
            trait = other;
            other = '';
        } else {
            // Если уже есть черты характера, объединяем
            trait = trait + '. ' + other;
            other = '';
        }
    }
    
    // 8. Формируем строки для отображения
    if (techParams.weapon) summaryLines.push(techParams.weapon);
    if (techParams.damage) summaryLines.push(techParams.damage);
    if (techParams.hp) summaryLines.push(techParams.hp);
    if (techParams.ability) summaryLines.push(techParams.ability);
    
    // 9. Если нашли хотя бы 2 параметра - показываем результат
    const foundParams = [techParams.weapon, techParams.damage, techParams.hp, techParams.ability].filter(p => p).length;
    if (foundParams < 2) {
        return `<div class='npc-block-modern'><div class='npc-modern-header'>Ошибка</div><div class='npc-modern-block'>AI не вернул достаточно технических параметров. Найдено: ${foundParams}/4. Попробуйте сгенерировать NPC ещё раз.</div></div>`;
    }
    function firstSentence(str) {
        if (!str || str === '-') return '';
        let m = str.match(/^[^.?!]+[.?!]?/);
        return m ? m[0].trim() : str.trim();
    }
    let out = '';
    out += `<div class='npc-block-modern'>`;
    out += `<div class='npc-modern-header'>${name ? name : 'NPC'}</div>`;
    if (race || cls) {
        out += `<div class='npc-modern-sub'>${race ? race : ''}${race && cls ? ' · ' : ''}${cls ? cls : ''}</div>`;
    }
    // Адаптивные карточки
    if (summaryLines.length) {
        let listHtml = '<ul class="npc-modern-list">' + summaryLines.map(s => `<li>${s}</li>`).join('') + '</ul>';
        out += `<div class='npc-col-block'><span style='font-size:1.2em;'>⚔️</span> <b>Технические параметры</b>${listHtml}</div>`;
    }
    if (shortdesc && shortdesc !== '-') {
        out += `<div class='npc-col-block'><span style='font-size:1.2em;'>📜</span> <b>Описание</b>${firstSentence(shortdesc)}</div>`;
    }
    if (trait && trait !== '-' && trait.trim().length > 0) {
        // Проверяем, что это действительно черта характера, а не описание предметов или внешности
        let traitLower = trait.toLowerCase();
        if (!/флейта|пояс|мешок|зерно|носит|висит|за спиной|на поясе|внешность|стройная|женщина|собранными|тёмными|волосами|пучок|форменном|платье|формария|глаза|следят|движения|точны|экономны|кулон|амулет|кольцо|ожерелье|браслет|на шее/i.test(traitLower)) {
            // Очищаем текст от лишних заголовков
            let traitText = trait;
            if (trait.includes('Черты характера')) {
                traitText = trait.replace(/^черты характера\s*/i, '').trim();
            }
            out += `<div class='npc-col-block'><span style='font-size:1.2em;'>🧠</span> <b>Черта характера</b>${firstSentence(traitText)}</div>`;
        }
    }
    if (appear && appear !== '-') {
        // Объединяем описания внешности, если их несколько
        let appearText = appear;
        if (appear.includes('Внешность')) {
            appearText = appear.replace(/^внешность\s*/i, '').trim();
        }
        // Убираем дублирование описаний внешности
        let sentences = appearText.split(/[.!?]/).map(s => s.trim()).filter(Boolean);
        let uniqueSentences = [];
        for (let sentence of sentences) {
            // Проверяем на дублирование более точно
            let isDuplicate = uniqueSentences.some(s => {
                let sLower = s.toLowerCase();
                let sentenceLower = sentence.toLowerCase();
                return sLower.includes(sentenceLower.substring(0, 30)) || sentenceLower.includes(sLower.substring(0, 30));
            });
            if (!isDuplicate) {
                uniqueSentences.push(sentence);
            }
        }
        appearText = uniqueSentences.join('. ');
        out += `<div class='npc-col-block'><span style='font-size:1.2em;'>👤</span> <b>Внешность</b>${firstSentence(appearText)}</div>`;
    }
    if (desc && desc !== '-') {
        // Объединяем описания, если их несколько
        let descText = desc;
        if (desc.includes('Описание')) {
            descText = desc.replace(/^описание\s*/i, '').trim();
        }
        // Убираем дублирование описаний
        let sentences = descText.split(/[.!?]/).map(s => s.trim()).filter(Boolean);
        let uniqueSentences = [];
        for (let sentence of sentences) {
            // Проверяем на дублирование более точно
            let isDuplicate = uniqueSentences.some(s => {
                let sLower = s.toLowerCase();
                let sentenceLower = sentence.toLowerCase();
                return sLower.includes(sentenceLower.substring(0, 40)) || sentenceLower.includes(sLower.substring(0, 40));
            });
            if (!isDuplicate) {
                uniqueSentences.push(sentence);
            }
        }
        descText = uniqueSentences.join('. ');
        out += `<div class='npc-col-block'><span style='font-size:1.2em;'>📜</span> <b>Описание</b>${firstSentence(descText)}</div>`;
    }
    if (behavior && behavior !== '-') {
        out += `<div class='npc-col-block'><span style='font-size:1.2em;'>🎭</span> <b>Прочее</b>${firstSentence(behavior)}</div>`;
    }
    if (other && other !== '-' && other.trim().length > 0) {
        out += `<div class='npc-col-block'><span style='font-size:1.2em;'>📋</span> <b>Дополнительно</b>${firstSentence(other)}</div>`;
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
            document.getElementById('modal-content').innerHTML = content;
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
        let nameLine = lines.find(l => /^(Имя|Name|Имя NPC|Имя персонажа)\s*:/i.test(l));
        let preview = nameLine || (lines.length ? lines[0] : '(нет данных)');
        console.log('Заметка', i, 'превью:', preview);
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
</script>
