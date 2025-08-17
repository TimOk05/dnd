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
    $prompts = [
        'npc' => 'Сгенерируй случайного NPC для DnD с именем, внешностью и короткой историей. ' . $systemInstruction,
        'name' => 'Придумай уникальное фэнтези-имя для персонажа. ' . $systemInstruction,
        'race' => 'Назови случайную расу для персонажа DnD. ' . $systemInstruction,
        'class' => 'Назови случайный класс для персонажа DnD. ' . $systemInstruction,
        'tavern' => 'Придумай название для таверны в стиле DnD. ' . $systemInstruction,
        'event' => 'Придумай интересное событие для приключенцев в дороге. ' . $systemInstruction
    ];
    if ($action === 'dice') {
        $dice = $_POST['dice'] ?? '1d20';
        $label = $_POST['label'] ?? '';
        // Кидаем кости на PHP
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
    if (isset($prompts[$action])) {
        $data = [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'system', 'content' => $systemInstruction],
                ['role' => 'user', 'content' => $prompts[$action]]
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
$quickCommands = [
    'd20' => 'Брось d20 и выведи результат как мастер DnD. Ответ должен быть без оформления, без markdown, без кавычек и звёздочек. Разбей текст на короткие строки для удобства чтения во время игры.',
    'npc' => 'Сгенерируй случайного NPC для DnD с именем, внешностью и короткой историей. Ответ должен быть без оформления, без markdown, без кавычек и звёздочек. Разбей текст на короткие строки для удобства чтения во время игры.',
    'event' => 'Придумай интересное событие, которое может произойти с приключенцами в дороге. Ответ должен быть без оформления, без markdown, без кавычек и звёздочек. Разбей текст на короткие строки для удобства чтения во время игры.'
];
if (isset($_GET['quick']) && isset($quickCommands[$_GET['quick']])) {
    $_POST['message'] = $quickCommands[$_GET['quick']];
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

// --- Генерация быстрых кнопок (только для чата) ---
$quickBtns = '';
foreach ($quickCommands as $key => $prompt) {
    $labels = [
        'd20' => '🎲 Бросить d20',
        'npc' => '🗣️ NPC',
        'event' => '🚗 Событие'
    ];
    $quickBtns .= '<a class="quick-btn" href="?quick=' . $key . '">' . $labels[$key] . '</a>';
}

// --- Генерация быстрых генераций вне чата ---
$fastBtns = '';
$fastBtns .= '<button class="fast-btn" onclick="openFastModal(\'npc\')">🗣️ NPC</button>';
$fastBtns .= '<button class="fast-btn" onclick="openFastModal(\'name\')">📝 Имя</button>';
$fastBtns .= '<button class="fast-btn" onclick="openFastModal(\'race\')">👤 Раса</button>';
$fastBtns .= '<button class="fast-btn" onclick="openFastModal(\'class\')">⚔️ Класс</button>';
$fastBtns .= '<button class="fast-btn" onclick="openFastModal(\'tavern\')">🏪 Таверна</button>';
$fastBtns .= '<button class="fast-btn" onclick="openFastModal(\'event\')">🚗 Событие</button>';
$fastBtns .= '<button class="fast-btn" onclick="openDiceModal()">🎲 Кости</button>';

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
$template = str_replace('{{quick_buttons}}', $quickBtns, $template);
$template = str_replace('{{chat_messages}}', $chatMsgs, $template);
$template = str_replace('{{notes_block}}', $notesBlock, $template);
echo $template;
?>
<script>
function openFastModal(action) {
    showModal('Генерация...');
    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'fast_action=' + encodeURIComponent(action)
    })
    .then(r => r.text())
    .then(txt => {
        document.getElementById('modal-content').innerHTML = txt;
        document.getElementById('modal-save').onclick = function() { saveNote(txt); closeModal(); };
    });
}
function openDiceModal() {
    showModal('<form id="dice-form" onsubmit="return rollDice()">Бросить <input type="number" id="dice-count" value="1" min="1" max="20" style="width:40px;">d<input type="number" id="dice-sides" value="20" min="2" max="100" style="width:50px;"> <br><input type="text" id="dice-label" placeholder="Комментарий (необязательно)" style="margin-top:8px;width:90%"><br><button type="submit" class="modal-save" style="margin-top:10px;">Бросить</button></form>');
    document.getElementById('modal-save').style.display = 'none';
}
function rollDice() {
    let count = document.getElementById('dice-count').value;
    let sides = document.getElementById('dice-sides').value;
    let label = document.getElementById('dice-label').value;
    let dice = count + 'd' + sides;
    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'fast_action=dice&dice=' + encodeURIComponent(dice) + '&label=' + encodeURIComponent(label)
    })
    .then(r => r.text())
    .then(txt => {
        document.getElementById('modal-content').innerHTML = txt;
        document.getElementById('modal-save').style.display = '';
        document.getElementById('modal-save').onclick = function() { saveNote(txt); closeModal(); };
    });
    return false;
}
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
