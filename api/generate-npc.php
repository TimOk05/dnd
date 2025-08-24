<?php
session_start();
require_once '../users.php';
require_once 'dnd-api.php';

// Проверяем авторизацию
if (!isLoggedIn()) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'POST only']);
    exit;
}

// Инициализируем менеджер D&D API
$dndApi = new DndApiManager();

// Получаем параметры запроса
$race = $_POST['race'] ?? 'human';
$class = $_POST['class'] ?? 'fighter';
$level = (int)($_POST['level'] ?? 1);
$alignment = $_POST['alignment'] ?? 'neutral';
$background = $_POST['background'] ?? 'soldier';
$useExternalApi = isset($_POST['use_external_api']) ? (bool)$_POST['use_external_api'] : false;

// Отладочная информация
error_log("NPC Generation Debug - POST data: " . print_r($_POST, true));
error_log("NPC Generation Debug - Parameters: race=$race, class=$class, level=$level, alignment=$alignment, background=$background");

// Валидация параметров
$validRaces = ['human', 'elf', 'dwarf', 'halfling', 'orc', 'tiefling', 'dragonborn', 'gnome', 'half-elf', 'half-orc'];
$validClasses = ['fighter', 'wizard', 'rogue', 'cleric', 'ranger', 'barbarian', 'bard', 'druid', 'monk', 'paladin', 'sorcerer', 'warlock'];
$validAlignments = ['lawful good', 'neutral good', 'chaotic good', 'lawful neutral', 'neutral', 'chaotic neutral', 'lawful evil', 'neutral evil', 'chaotic evil'];

if (!in_array($race, $validRaces)) {
    echo json_encode(['error' => 'Invalid race']);
    exit;
}

if (!in_array($class, $validClasses)) {
    echo json_encode(['error' => 'Invalid class']);
    exit;
}

if ($level < 1 || $level > 20) {
    echo json_encode(['error' => 'Invalid level']);
    exit;
}

if (!in_array($alignment, $validAlignments)) {
    echo json_encode(['error' => 'Invalid alignment']);
    exit;
}

try {
    // Генерируем NPC
    $npcData = $dndApi->generateNPC([
        'race' => $race,
        'class' => $class,
        'level' => $level,
        'alignment' => $alignment,
        'background' => $background
    ]);
    
    if (!$npcData) {
        echo json_encode(['error' => 'Failed to generate NPC']);
        exit;
    }
    
    // Форматируем результат для отображения
    $result = [
        'success' => true,
        'npc' => $npcData,
        'generated_at' => date('Y-m-d H:i:s'),
        'api_used' => $useExternalApi ? 'external' : 'dnd5e'
    ];
    
    // Отладочная информация о результате
    error_log("NPC Generation Result - background: " . ($npcData['background'] ?? 'NULL'));
    error_log("NPC Generation Result - full data: " . print_r($npcData, true));
    
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("NPC Generation Error: " . $e->getMessage());
    echo json_encode(['error' => 'Internal server error']);
}
?>
