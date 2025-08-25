<?php
require_once __DIR__ . '/api/generate-characters.php';

echo "<h1>Тест исправлений генерации персонажей</h1>\n";

// Тестируем генерацию персонажа
$generator = new CharacterGenerator();

$testParams = [
    'race' => 'tabaxi',
    'class' => 'artificer',
    'level' => 5,
    'gender' => 'male',
    'alignment' => 'random',
    'use_ai' => 'off'
];

echo "<h2>Тест генерации табакси изобретателя</h2>\n";
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
    echo "<strong>Мировоззрение:</strong> " . $character['alignment'] . "<br>\n";
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
    
    echo "</div>\n";
} else {
    echo "<div style='background: #ffebee; padding: 15px; border-radius: 8px; margin: 10px 0;'>\n";
    echo "<strong>Ошибка:</strong> " . $result['error'] . "\n";
    echo "</div>\n";
}

// Тестируем случайное мировоззрение
echo "<h2>Тест случайного мировоззрения</h2>\n";
for ($i = 0; $i < 5; $i++) {
    $testParams = [
        'race' => 'human',
        'class' => 'fighter',
        'level' => 1,
        'gender' => 'random',
        'alignment' => 'random',
        'use_ai' => 'off'
    ];
    
    $result = $generator->generateCharacter($testParams);
    if ($result['success']) {
        $character = $result['npc'];
        echo "<div style='background: #e8f5e8; padding: 8px; border-radius: 6px; margin: 5px 0; display: inline-block; width: 300px;'>\n";
        echo "<strong>" . $character['name'] . "</strong> - " . $character['race'] . " " . $character['class'] . "<br>\n";
        echo "Мировоззрение: " . $character['alignment'] . "<br>\n";
        echo "Пол: " . $character['gender'] . "<br>\n";
        echo "</div>\n";
    }
}

// Тестируем новые расы
echo "<h2>Тест новых рас</h2>\n";
$newRaces = ['tabaxi', 'aarakocra', 'goblin', 'kenku', 'lizardfolk', 'triton', 'yuan-ti', 'goliath', 'firbolg', 'bugbear', 'hobgoblin', 'kobold'];

foreach ($newRaces as $race) {
    $testParams = [
        'race' => $race,
        'class' => 'fighter',
        'level' => 1,
        'gender' => 'random',
        'alignment' => 'neutral',
        'use_ai' => 'off'
    ];
    
    $result = $generator->generateCharacter($testParams);
    if ($result['success']) {
        $character = $result['npc'];
        echo "<div style='background: #fff3e0; padding: 8px; border-radius: 6px; margin: 5px 0; display: inline-block; width: 300px;'>\n";
        echo "<strong>" . $character['name'] . "</strong> - " . $character['race'] . " " . $character['class'] . "<br>\n";
        echo "Профессия: " . $character['occupation'] . "<br>\n";
        echo "Урон: " . $character['damage'] . "<br>\n";
        echo "</div>\n";
    }
}

// Тестируем новый класс
echo "<h2>Тест нового класса</h2>\n";
$testParams = [
    'race' => 'human',
    'class' => 'artificer',
    'level' => 3,
    'gender' => 'female',
    'alignment' => 'lawful-good',
    'use_ai' => 'off'
];

$result = $generator->generateCharacter($testParams);
if ($result['success']) {
    $character = $result['npc'];
    echo "<div style='background: #f3e5f5; padding: 15px; border-radius: 8px; margin: 10px 0;'>\n";
    echo "<h3>Результат генерации изобретателя:</h3>\n";
    echo "<strong>Имя:</strong> " . $character['name'] . "<br>\n";
    echo "<strong>Раса:</strong> " . $character['race'] . "<br>\n";
    echo "<strong>Класс:</strong> " . $character['class'] . "<br>\n";
    echo "<strong>Уровень:</strong> " . $character['level'] . "<br>\n";
    echo "<strong>Пол:</strong> " . $character['gender'] . "<br>\n";
    echo "<strong>Мировоззрение:</strong> " . $character['alignment'] . "<br>\n";
    echo "<strong>Профессия:</strong> " . $character['occupation'] . "<br>\n";
    echo "<strong>Хиты:</strong> " . $character['hit_points'] . "<br>\n";
    echo "<strong>КД:</strong> " . $character['armor_class'] . "<br>\n";
    echo "<strong>Урон:</strong> " . $character['damage'] . "<br>\n";
    echo "</div>\n";
}

echo "<h2>Тест завершен!</h2>\n";
?>
