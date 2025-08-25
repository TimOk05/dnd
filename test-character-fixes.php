<?php
require_once __DIR__ . '/api/generate-characters.php';

echo "<h1>Тест исправлений генерации персонажей</h1>\n";

// Тестируем генерацию персонажа
$generator = new CharacterGenerator();

$testParams = [
    'race' => 'dragonborn',
    'class' => 'druid',
    'level' => 5,
    'gender' => 'male',
    'alignment' => 'neutral-good',
    'use_ai' => 'off'
];

echo "<h2>Тест генерации драконорожденного друида</h2>\n";
$result = $generator->generateCharacter($testParams);

if ($result['success']) {
    $character = $result['npc'];
    echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 8px; margin: 10px 0;'>\n";
    echo "<h3>Результат генерации:</h3>\n";
    echo "<strong>Имя:</strong> " . $character['name'] . "<br>\n";
    echo "<strong>Раса:</strong> " . $character['race'] . "<br>\n";
    echo "<strong>Класс:</strong> " . $character['class'] . "<br>\n";
    echo "<strong>Уровень:</strong> " . $character['level'] . "<br>\n";
    echo "<strong>Пол:</strong> " . $character['gender'] . "<br>\n";
    echo "<strong>Профессия:</strong> " . $character['occupation'] . "<br>\n";
    echo "<strong>Хиты:</strong> " . $character['hit_points'] . "<br>\n";
    echo "<strong>КД:</strong> " . $character['armor_class'] . "<br>\n";
    echo "<strong>Скорость:</strong> " . $character['speed'] . " футов<br>\n";
    echo "<strong>Инициатива:</strong> " . $character['initiative'] . "<br>\n";
    echo "<strong>Урон:</strong> " . $character['damage'] . "<br>\n";
    echo "<strong>Бонус мастерства:</strong> +" . $character['proficiency_bonus'] . "<br>\n";
    
    echo "<h4>Характеристики:</h4>\n";
    echo "СИЛ: " . $character['abilities']['str'] . "<br>\n";
    echo "ЛОВ: " . $character['abilities']['dex'] . "<br>\n";
    echo "ТЕЛ: " . $character['abilities']['con'] . "<br>\n";
    echo "ИНТ: " . $character['abilities']['int'] . "<br>\n";
    echo "МДР: " . $character['abilities']['wis'] . "<br>\n";
    echo "ХАР: " . $character['abilities']['cha'] . "<br>\n";
    
    if (isset($character['description'])) {
        echo "<h4>Описание:</h4>\n";
        echo "<p>" . $character['description'] . "</p>\n";
    }
    
    if (isset($character['background'])) {
        echo "<h4>Предыстория:</h4>\n";
        echo "<p>" . $character['background'] . "</p>\n";
    }
    
    echo "</div>\n";
} else {
    echo "<div style='background: #ffebee; padding: 15px; border-radius: 8px; margin: 10px 0;'>\n";
    echo "<strong>Ошибка:</strong> " . $result['error'] . "\n";
    echo "</div>\n";
}

// Тестируем разные расы и классы
echo "<h2>Тест разных рас и классов</h2>\n";
$races = ['human', 'elf', 'dwarf', 'halfling', 'orc', 'tiefling', 'dragonborn', 'gnome', 'half-elf', 'half-orc'];
$classes = ['fighter', 'wizard', 'rogue', 'cleric', 'ranger', 'barbarian', 'bard', 'druid', 'monk', 'paladin', 'sorcerer', 'warlock'];

foreach ($races as $race) {
    foreach ($classes as $class) {
        $testParams = [
            'race' => $race,
            'class' => $class,
            'level' => 1,
            'gender' => 'random',
            'alignment' => 'neutral',
            'use_ai' => 'off'
        ];
        
        $result = $generator->generateCharacter($testParams);
        if ($result['success']) {
            $character = $result['npc'];
            echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 6px; margin: 5px 0; display: inline-block; width: 300px;'>\n";
            echo "<strong>" . $character['name'] . "</strong> - " . $character['race'] . " " . $character['class'] . "<br>\n";
            echo "Профессия: " . $character['occupation'] . "<br>\n";
            echo "Урон: " . $character['damage'] . "<br>\n";
            echo "</div>\n";
        }
    }
}

echo "<h2>Тест завершен!</h2>\n";
?>
