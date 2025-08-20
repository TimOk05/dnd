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
    // Ищем строку с именем по разным вариантам
    $plain = strip_tags(str_replace(['<br>', "\n"], "\n", $note));
    $lines = array_filter(array_map('trim', explode("\n", $plain)));
    $nameLine = '';
    foreach ($lines as $line) {
        if (preg_match('/^(Имя|Name|Имя NPC|Имя персонажа)\s*:/iu', $line)) {
            $nameLine = $line;
            break;
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
const npcProfs = ['Прохожий','Стражник','Тавернщик','Торговец','Кузнец','Наёмник','Жрец','Преступник','Ремесленник','Охотник','Повар','Писарь','Мастер гильдии','Путешественник','Мудрец'];
let npcRace = '', npcClass = '', npcProf = '', npcLevel = 1;
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
    showModal('<b class="mini-menu-title">Укажите уровень NPC (1-20):</b><div class="npc-level-wrap"><input type=number id=npc-level value=1 min=1 max=20 style=\'width:60px\'></div><button class=\'fast-btn\' onclick=\'openNpcStep3WithLevel()\'>Далее</button>');
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
        if (json.data && json.data.names && Array.isArray(json.data.names) && json.data.names.length > 0) {
          let filtered = race ? json.data.names.filter(n => n.race && n.race.toLowerCase().includes(race.toLowerCase())) : json.data.names;
          let pool = filtered.length ? filtered : json.data.names;
          let rnd = pool[Math.floor(Math.random() * pool.length)];
          name = rnd && rnd.name_ru ? rnd.name_ru : '';
        }
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
        const systemInstruction = 'Всегда пиши ответы без оформления, без markdown, без кавычек и звёздочек. Разделяй результат NPC на смысловые блоки с заголовками: Описание, Внешность, Черты характера, Особенности поведения, Короткая характеристика. В блоке Короткая характеристика обязательно выведи отдельными строками: Оружие: [название оружия], Урон: [формат урона, например 1d6], Хиты: [количество хитов], Способность: [основная способность]. Каждый блок начинай с заголовка. Технические параметры обязательны!';
        const prompt = `Создай NPC для DnD. Раса: ${race}. Класс: ${npcClass}. Профессия: ${prof}. Уровень: ${level}.${contextBlock}`;
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
function openNpcStep3WithLevel() {
    npcLevel = document.getElementById('npc-level').value;
    showModal('<b class="mini-menu-title">Выберите профессию NPC:</b><div class="mini-menu-btns">' + npcProfs.map(p => `<button onclick=\'fetchNpcFromAI("${npcRace}","${npcClass}","${p}","${npcLevel}")\' class=\'fast-btn\'>${p}</button>`).join(' ') + '</div>');
    document.getElementById('modal-save').style.display = 'none';
}
// --- Форматирование результата NPC по смысловым блокам ---
function formatNpcBlocks(txt, forcedName = '') {
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
    let name = '', race = '', cls = '', shortdesc = '', trait = '', weakness = '', summary = '', desc = '', appear = '', behavior = '';
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
            if (!techParams.weapon && /оружие|weapon|меч|топор|лук|кинжал|посох/i.test(lineLower)) {
                techParams.weapon = line;
            }
            if (!techParams.damage && /урон|damage|d\d+|1d\d+|2d\d+/i.test(lineLower)) {
                techParams.damage = line;
            }
            if (!techParams.hp && /хиты|hp|здоровье|health|жизн/i.test(lineLower)) {
                techParams.hp = line;
            }
            if (!techParams.ability && /способност|ability|skill|маги|заклинани/i.test(lineLower)) {
                techParams.ability = line;
            }
        }
    }
    
    // 3. Формируем строки для отображения
    if (techParams.weapon) summaryLines.push(techParams.weapon);
    if (techParams.damage) summaryLines.push(techParams.damage);
    if (techParams.hp) summaryLines.push(techParams.hp);
    if (techParams.ability) summaryLines.push(techParams.ability);
    
    // 4. Если нашли хотя бы 2 параметра - показываем результат
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
        out += `<div class='npc-col-block'><span style='font-size:1.2em;'>📜</span> <b>Краткое описание</b>${firstSentence(shortdesc)}</div>`;
    }
    if (trait && trait !== '-') {
        out += `<div class='npc-col-block'><span style='font-size:1.2em;'>🧠</span> <b>Черта характера</b>${firstSentence(trait)}</div>`;
    }
    if (weakness && weakness !== '-') {
        out += `<div class='npc-col-block'><span style='font-size:1.2em;'>⚡</span> <b>Слабость</b>${firstSentence(weakness)}</div>`;
    }
    // Кнопка показать описание
    if ((desc && desc !== '-') || (appear && appear !== '-') || (behavior && behavior !== '-')) {
        out += `<button class='npc-desc-toggle-btn' onclick='this.nextElementSibling.classList.toggle("active")'>Показать описание</button>`;
        out += `<div class='npc-modern-block npc-desc-detail' style='display:none;'>`;
        if (desc && desc !== '-') out += `<div style='margin-bottom:8px;'><b>Описание:</b> ${desc}</div>`;
        if (appear && appear !== '-') out += `<div style='margin-bottom:8px;'><b>Внешность:</b> ${appear}</div>`;
        if (behavior && behavior !== '-') out += `<div><b>Особенности поведения:</b> ${behavior}</div>`;
        out += `</div>`;
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
}
document.getElementById('modal-close').onclick = closeModal;
document.getElementById('modal-bg').onclick = function(e) { if (e.target === this) closeModal(); };
function saveNote(content) {
    // Сохраняем HTML содержимого модального окна
    var content = document.getElementById('modal-content').innerHTML;
    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'add_note=1&note_content=' + encodeURIComponent(content)
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
