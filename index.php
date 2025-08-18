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

// --- Новый systemInstruction с примером ---
const systemInstruction = 'Всегда пиши параметры NPC строго в формате: Имя: ..., Раса: ..., Класс: ..., Черты характера: ... (через запятую), Особенности поведения: ... (через запятую), Короткая характеристика: ... (через запятую или отдельными строками). Пример:\nИмя: Борис Громовержец\nРаса: Человек\nКласс: Воин\nЧерты характера: Храбрый, Упрямый, Верит в справедливость\nОсобенности поведения: Часто шутит, Любит рассказывать истории\nКороткая характеристика: Оружие — меч, Урон — 1d8+2, Способность — Ярость, Хиты — 32. Не используй markdown, не добавляй лишних символов.';
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
    let traderExamples = '';
    if (window.uniqueTraders && window.uniqueTraders.length > 0) {
        let shuffled = window.uniqueTraders.slice().sort(() => Math.random() - 0.5);
        let examples = shuffled.slice(0, 3).map(e => e.description).join('\n---\n');
        traderExamples = `Вот примеры необычных NPC-торговцев и персонажей для вдохновения (не копируй их, а придумай нового на их основе):\n${examples}`;
    }
    const systemInstruction = 'Всегда пиши ответы без оформления, без markdown, без кавычек и звёздочек. Разделяй результат NPC на смысловые блоки с заголовками: Описание, Внешность, Черты характера, Особенности поведения, Короткая характеристика. В блоке Короткая характеристика выведи отдельными строками: Оружие, Урон, Способность, Хиты. Каждый блок начинай с заголовка.';
    const prompt = `Создай NPC для DnD. Раса: ${race}. Класс: ${npcClass}. Профессия: ${prof}. Уровень: ${level}. Добавь имя. ${traderExamples}`;
    fetch('ai.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'prompt=' + encodeURIComponent(prompt) + '&system=' + encodeURIComponent(systemInstruction) + '&type=npc'
    })
    .then(r => r.json())
    .then(data => {
        if (data && data.result) {
            document.getElementById('modal-content').innerHTML = formatNpcBlocks(data.result);
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
}
function openNpcStep3WithLevel() {
    npcLevel = document.getElementById('npc-level').value;
    showModal('<b class="mini-menu-title">Выберите профессию NPC:</b><div class="mini-menu-btns">' + npcProfs.map(p => `<button onclick=\'fetchNpcFromAI("${npcRace}","${npcClass}","${p}","${npcLevel}")\' class=\'fast-btn\'>${p}</button>`).join(' ') + '</div>');
    document.getElementById('modal-save').style.display = 'none';
}
// --- Форматирование результата NPC по смысловым блокам ---
function formatNpcBlocks(txt) {
    txt = txt.replace(/[\#\*`>]+/g, '');
    const blockTitles = [
        'Имя', 'Раса', 'Класс', 'Описание', 'Внешность', 'Черты характера', 'Особенности поведения', 'Короткая характеристика'
    ];
    let blocks = [];
    let current = null;
    let lines = txt.split(/<br>|\n/).map(l => l.trim());
    let foundBlock = false;
    for (let line of lines) {
        if (!line) continue;
        let found = blockTitles.find(t => line.toLowerCase().startsWith(t.toLowerCase() + ':'));
        if (found) {
            foundBlock = true;
            if (current) blocks.push(current);
            current = {title: found, content: line.slice(found.length+1).trim()};
        } else if (current) {
            current.content += (current.content ? ' ' : '') + line;
        }
    }
    if (current) blocks.push(current);
    let out = '';
    let alt = false;
    if (blocks.length) {
        for (let block of blocks) {
            if (block.title === 'Черты характера' || block.title === 'Особенности поведения' || block.title === 'Короткая характеристика') {
                let items = block.content.split(',').map(s => s.trim()).filter(Boolean);
                let listHtml = '<ul class="traits-list">' + items.map(s => `<li>${s}</li>`).join('') + '</ul>';
                out += `<div class=\"result-segment-alt\"><b>${block.title}</b></div>`;
                out += `<div class=\"traits-block\">${listHtml}</div>`;
            } else {
                out += `<div class=\"${alt ? 'result-segment-alt' : 'result-segment'}\"><b>${block.title}:</b> ${block.content}</div>`;
                alt = !alt;
            }
        }
    } else if (txt && txt.trim()) {
        // Fallback: просто разбить по строкам
        let fallbackLines = txt.split(/<br>|\n/).map(l => l.trim()).filter(Boolean);
        out = fallbackLines.map(l => `<div class='result-segment'>${l}</div>`).join('');
    } else {
        // Если совсем пусто — показать raw-ответ (для отладки)
        out = `<div class='result-segment-alt'><b>AI не вернул результат. Raw-ответ:</b><br>${txt}</div>`;
    }
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
