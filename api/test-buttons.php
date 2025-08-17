<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Тестовые данные для проверки кнопок
$testData = [
    'npc' => [
        'name' => 'Тестовый NPC',
        'race' => 'Человек',
        'profession' => 'Тестер',
        'personality' => 'Любит тестировать'
    ],
    'tavern' => [
        'name' => 'Тестовая таверна',
        'description' => 'Место для тестирования',
        'atmosphere' => 'Тестовая'
    ],
    'potion' => [
        'name' => 'Тестовое зелье',
        'effect' => 'Тестирует функции',
        'rarity' => 'Тестовая'
    ]
];

echo json_encode([
    'status' => 'success',
    'message' => 'Кнопки работают!',
    'data' => $testData
]);
?>
