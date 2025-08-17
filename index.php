<?php
session_start();

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
    $apiKey = 'sk-1e898ddba737411e948af435d767e893';
    $apiUrl = 'https://api.deepseek.com/v1/chat/completions';
    $systemInstruction = 'Всегда пиши ответы без оформления, без markdown, без кавычек и звёздочек. Разделяй результат NPC на смысловые блоки с заголовками: Описание, Внешность, Черты характера, Особенности поведения, Короткая характеристика. В блоке Короткая характеристика выведи отдельными строками: Оружие, Урон, Способность, Хиты. Каждый блок начинай с заголовка.';

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

    // --- NPC ---
    if ($action === 'npc_result') {
        $race = $_POST['race'] ?? '';
        $class = $_POST['class'] ?? '';
        $prof = $_POST['prof'] ?? '';
        $level = $_POST['level'] ?? '1';
        $prompt = "Создай NPC для DnD. Раса: $race. Класс: $class. Профессия: $prof. Уровень: $level. Добавь имя. $systemInstruction";
        $data = [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'system', 'content' => $systemInstruction],
                ['role' => 'user', 'content' => $prompt]
            ]
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
        $curlError = curl_error($ch);
        curl_close($ch);
        $result = json_decode($response, true);
        $aiMessage = $result['choices'][0]['message']['content'] ?? '';
        if (!$aiMessage) {
            echo '<div class="result-segment"><b>DEBUG:</b><br><pre>' . htmlspecialchars("CURL ERROR: $curlError\nRESPONSE: $response") . '</pre></div>';
            exit;
        }
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
        $aiMessage = implode("<br>", $formatted);
        echo $aiMessage;
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
$systemInstruction = 'Всегда пиши ответы без оформления, без markdown, без кавычек и звёздочек. Разбивай текст на короткие строки для удобства чтения во время игры.';
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
    // Извлекаем первую строку из HTML (strip_tags, explode по <br> или \n)
    $plain = strip_tags(str_replace(['<br>', "\n"], "\n", $note));
    $firstLine = explode("\n", $plain)[0];
    $notesBlock .= '<div class="note-item" onclick="expandNote(' . $i . ')">' . htmlspecialchars($firstLine, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '<button class="note-remove" onclick="event.stopPropagation();removeNote(' . $i . ')">×</button></div>';
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
function openNpcStep3WithLevel() {
    npcLevel = document.getElementById('npc-level').value;
    showModal('<b class="mini-menu-title">Выберите профессию NPC:</b><div class="mini-menu-btns">' + npcProfs.map(p => `<button onclick=\'getNpcResult("${p}")\' class=\'fast-btn\'>${p}</button>`).join(' ') + '</div>');
    document.getElementById('modal-save').style.display = 'none';
}
function getNpcResult(prof) {
    npcProf = prof;
    showModal('Генерация NPC...');
    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'fast_action=npc_result&race=' + encodeURIComponent(npcRace) + '&class=' + encodeURIComponent(npcClass) + '&prof=' + encodeURIComponent(npcProf) + '&level=' + encodeURIComponent(npcLevel)
    })
    .then(r => r.text())
    .then(txt => {
        document.getElementById('modal-content').innerHTML = formatNpcBlocks(txt);
        document.getElementById('modal-save').style.display = '';
        document.getElementById('modal-save').onclick = function() { saveNote(txt); closeModal(); };
    });
}
// --- Форматирование результата NPC по смысловым блокам ---
function formatNpcBlocks(txt) {
    // Блоки: Описание, Внешность, Черты характера, Особенности поведения, Короткая характеристика
    const blockTitles = [
        'Описание', 'Внешность', 'Черты характера', 'Особенности поведения', 'Короткая характеристика'
    ];
    let blocks = [];
    let current = null;
    let lines = txt.split(/<br>|\n/).map(l => l.trim());
    for (let line of lines) {
        if (!line) continue;
        let found = blockTitles.find(t => line.toLowerCase().startsWith(t.toLowerCase() + ':'));
        if (found) {
            if (current) blocks.push(current);
            current = {title: found, content: line.slice(found.length+1).trim()};
        } else if (current) {
            current.content += (current.content ? '<br>' : '') + line;
        }
    }
    if (current) blocks.push(current);
    let out = '';
    for (let block of blocks) {
        if (block.title === 'Короткая характеристика') {
            out += `<div class="npc-summary"><b>${block.title}:</b><br>${block.content}</div>`;
        } else {
            out += `<div class="result-segment"><b>${block.title}:</b> ${block.content}</div>`;
        }
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
</script>
