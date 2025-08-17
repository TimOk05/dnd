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

// Генерация быстрых кнопок
$quickBtns = '';
foreach ($quickCommands as $key => $prompt) {
    $labels = [
        'd20' => '🎲 Бросить d20',
        'npc' => '🗣️ NPC',
        'event' => '🚗 Событие'
    ];
    $quickBtns .= '<a class="quick-btn" href="?quick=' . $key . '">' . $labels[$key] . '</a>';
}

// Генерация сообщений чата
$chatMsgs = '';
foreach ($_SESSION['chat'] as $msg) {
    $who = $msg['role'] === 'user' ? 'Вы' : 'AI';
    $class = $msg['role'];
    $chatMsgs .= '<div class="msg ' . $class . '"><b>' . $who . ':</b> ' . nl2br(htmlspecialchars($msg['content'])) . '</div>';
}

// Загрузка шаблона и подстановка контента
$template = file_get_contents(__DIR__ . '/template.html');
$template = str_replace('{{quick_buttons}}', $quickBtns, $template);
$template = str_replace('{{chat_messages}}', $chatMsgs, $template);
echo $template;
