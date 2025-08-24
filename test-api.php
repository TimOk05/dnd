<?php
// Тест API с реальными POST данными
echo "<h1>Тест API с POST данными</h1>";

// Симулируем POST запрос
$_POST = [
    'race' => 'orc',
    'class' => 'monk',
    'level' => 1,
    'alignment' => 'neutral',
    'background' => 'soldier'
];

// Включаем вывод ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключаем API
require_once 'api/generate-npc.php';

// API должен выполниться и вывести JSON
?>
