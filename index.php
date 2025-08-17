<?php
session_start();

// Сброс чата
if (isset($_GET['reset'])) {
    $_SESSION['chat'] = [];
    header("Location: index.php");
    exit;
}

// Инициализация истории чата
if (!isset($_SESSION['chat'])) {
    $_SESSION['chat'] = [];
}

// Быстрые команды
$quickCommands = [
    'd20' => 'Брось d20 и выведи результат как мастер DnD.',
    'npc' => 'Сгенерируй случайного NPC для DnD с именем, внешностью и короткой историей.',
    'event' => 'Придумай интересное событие, которое может произойти с приключенцами в дороге.'
];

if (isset($_GET['quick']) && isset($quickCommands[$_GET['quick']])) {
    $_POST['message'] = $quickCommands[$_GET['quick']];
}

// Обработка отправки сообщения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $userMessage = trim($_POST['message']);
    if ($userMessage !== '') {
        $_SESSION['chat'][] = ['role' => 'user', 'content' => $userMessage];

        // Отправка запроса к DeepSeek API
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
        $_SESSION['chat'][] = ['role' => 'assistant', 'content' => $aiMessage];
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>DnD AI Чат</title>
    <style>
        body { font-family: sans-serif; background: #f8f8fa; }
        .chat { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #0001; padding: 24px; }
        .msg { margin-bottom: 12px; }
        .user { text-align: right; color: #1a237e; }
        .assistant { text-align: left; color: #388e3c; }
        .quick { margin: 0 4px; }
        form { display: flex; gap: 8px; margin-top: 16px; }
        input[type=text] { flex: 1; padding: 8px; border-radius: 4px; border: 1px solid #ccc; }
        button { padding: 8px 16px; border-radius: 4px; border: none; background: #1976d2; color: #fff; cursor: pointer; }
        button:disabled { background: #aaa; }
    </style>
</head>
<body>
<div class="chat">
    <h2>DnD AI Чат</h2>
    <div>
        <a class="quick" href="?quick=d20">🎲 Бросить d20</a>
        <a class="quick" href="?quick=npc">🗣️ NPC</a>
        <a class="quick" href="?quick=event">🚗 Событие</a>
        <a class="quick" href="?reset=1" style="float:right;color:#d32f2f;">Сбросить чат</a>
    </div>
    <hr>
    <div>
        <?php foreach ($_SESSION['chat'] as $msg): ?>
            <div class="msg <?= $msg['role'] ?>">
                <b><?= $msg['role'] === 'user' ? 'Вы' : 'AI' ?>:</b> <?= nl2br(htmlspecialchars($msg['content'])) ?>
            </div>
        <?php endforeach; ?>
    </div>
    <form method="post">
        <input type="text" name="message" placeholder="Введите сообщение..." autocomplete="off" required>
        <button type="submit">Отправить</button>
    </form>
</div>
</body>
</html>
