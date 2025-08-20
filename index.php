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

ВАЖНО: Способность — это конкретный навык персонажа в D&D, например: 'Двойная атака', 'Исцеление ран', 'Скрытность', 'Божественная кара', 'Ярость', 'Вдохновение', 'Магическая защита', 'Элементальная магия', 'Боевой стиль', 'Связь с природой', 'Боевые искусства', 'Скрытные способности', 'Магическое исследование', 'Общение с животными', 'Магическая обработка', 'Магическое красноречие'. НЕ пиши описания, только название способности. ОБЯЗАТЕЛЬНО указывай способность для каждого класса кроме 'Без класса'.\nТехнические параметры (Оружие, Урон, Хиты, Способность) обязательны и всегда идут первым блоком. Если не можешь заполнить какой-то параметр — напиши ‘-’. Не добавляй ничего лишнего.";
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
$fastBtns .= '<button class="fast-btn" onclick="openInitiativeModal()">⚡ Инициатива</button>';

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
        const systemInstruction = 'Создай уникального NPC для D&D. СТРОГО следуй этому формату:\n\nИмя и Профессия\n[только имя и профессия, например: "Торин Каменщик"]\n\nОписание\n[3-4 предложения о прошлом, мотивации, целях персонажа БЕЗ упоминания имени]\n\nВнешность\n[2-3 предложения о внешнем виде, одежде, особенностях]\n\nЧерты характера\n[1-2 предложения о личности, поведении, привычках]\n\nТехнические параметры\nОружие: [подходящее оружие для класса]\nУрон: [формат урона, например 1d6 рубящий]\nХиты: [количество хитов]\n\nВАЖНО: Имя указывай ТОЛЬКО в блоке "Имя и Профессия". НЕ используй имя в других блоках.';
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

// --- Инициатива ---
let initiativeList = [];
let currentInitiativeIndex = 0;
let currentRound = 1;

function openInitiativeModal() {
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

function nextInitiative() {
    if (initiativeList.length > 0) {
        currentInitiativeIndex = (currentInitiativeIndex + 1) % initiativeList.length;
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
    
    // Технические параметры
    if (summaryLines.length) {
        let listHtml = '<ul class="npc-modern-list">' + summaryLines.map(s => `<li>${s}</li>`).join('') + '</ul>';
        out += `<div class='npc-col-block'><span style='font-size:1.2em;'>⚔️</span> <b>Технические параметры</b>${listHtml}</div>`;
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
</script>
