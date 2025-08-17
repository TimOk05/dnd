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
    $systemInstruction = 'Всегда пиши ответы без оформления, без markdown, без кавычек и звёздочек. Разбивай текст на короткие строки для удобства чтения во время игры.';

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
        $prompt = "Создай NPC для DnD. Раса: $race. Класс: $class. Профессия: $prof. Уровень: $level. Добавь имя, особенности поведения, внешность, черты характера. Обязательно выведи отдельными строками: Оружие: ..., Урон: ..., Способность: ..., Хиты: ... (на основе уровня, класса и расы). $systemInstruction";
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
    $notesBlock .= '<div class="note-item">' . nl2br(htmlspecialchars($note)) . '<button class="note-remove" onclick="removeNote(' . $i . ')">×</button></div>';
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
    showModal('<b>Выберите тип кости:</b><br>' +
        ['d3','d4','d6','d8','d10','d12','d20','d100'].map(d => `<button onclick=\'openDiceStep2("${d}")\' class=\'fast-btn\'>${d}</button>`).join(' ')
    );
    document.getElementById('modal-save').style.display = 'none';
}
function openDiceStep2(dice) {
    showModal(`<b>Сколько бросков ${dice}?</b><br><input type=number id=dice-count value=1 min=1 max=20 style=\'width:60px\'><br><input type=text id=dice-label placeholder=\'Комментарий (необязательно)\' style=\'margin-top:8px;width:90%\'><br><button class=\'fast-btn\' onclick=\'getDiceResult("${dice}")\'>Бросить</button>`);
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
        document.getElementById('modal-content').innerHTML = formatResultSegments(txt);
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
    showModal('<b>Выберите расу NPC:</b><br>' + npcRaces.map(r => `<button onclick=\'openNpcStep2("${r}")\' class=\'fast-btn\'>${r}</button>`).join(' '));
    document.getElementById('modal-save').style.display = 'none';
}
function openNpcStep2(race) {
    npcRace = race;
    showModal('<b>Выберите класс NPC:</b><br>' + npcClasses.map(c => `<button onclick=\'openNpcStepLevel("${c}")\' class=\'fast-btn\'>${c}</button>`).join(' '));
    document.getElementById('modal-save').style.display = 'none';
}
function openNpcStepLevel(cls) {
    npcClass = cls;
    showModal('<b>Укажите уровень NPC (1-20):</b><br><input type=number id=npc-level value=1 min=1 max=20 style=\'width:60px\'><br><button class=\'fast-btn\' onclick=\'openNpcStep3WithLevel()\'>Далее</button>');
    document.getElementById('modal-save').style.display = 'none';
}
function openNpcStep3WithLevel() {
    npcLevel = document.getElementById('npc-level').value;
    showModal('<b>Выберите профессию NPC:</b><br>' + npcProfs.map(p => `<button onclick=\'getNpcResult("${p}")\' class=\'fast-btn\'>${p}</button>`).join(' '));
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
        document.getElementById('modal-content').innerHTML = formatResultSegments(txt);
        document.getElementById('modal-save').style.display = '';
        document.getElementById('modal-save').onclick = function() { saveNote(txt); closeModal(); };
    });
}
// --- Форматирование результата по сегментам ---
function formatResultSegments(txt) {
    const keys = [
        'Имя', 'Раса', 'Класс', 'Уровень', 'Профессия', 'Оружие', 'Урон', 'Хиты', 'Способность',
        'Результаты', 'Сумма', 'Комментарий'
    ];
    const lines = txt.split(/<br>|\n/).map(l => l.trim()).filter(Boolean);
    let out = '', alt = false;
    for (let line of lines) {
        let isKey = keys.some(k => line.toLowerCase().startsWith(k.toLowerCase() + ':'));
        let cls = alt ? 'result-segment-alt' : 'result-segment';
        out += `<div class="${cls}">${line}</div>`;
        alt = !alt;
    }
    return out;
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
    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'add_note=' + encodeURIComponent(1) + '&note_content=' + encodeURIComponent(content)
    }).then(() => location.reload());
}
function removeNote(idx) {
    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'remove_note=' + encodeURIComponent(idx)
    }).then(() => location.reload());
}
</script>
