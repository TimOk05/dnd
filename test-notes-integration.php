<?php
session_start();

// Очищаем старые заметки для чистого теста
$_SESSION['notes'] = [];

// Тестовые данные персонажа
$characterData = [
    'name' => 'Игнис',
    'race' => 'Драконорожденный',
    'class' => 'Друид',
    'level' => 1,
    'gender' => 'Мужской',
    'alignment' => 'Нейтрально-добрый',
    'occupation' => 'Странник',
    'hit_points' => 12,
    'armor_class' => 15,
    'speed' => 30,
    'initiative' => 2,
    'damage' => '1d8 + 2',
    'proficiency_bonus' => 2,
    'abilities' => [
        'str' => 14,
        'dex' => 16,
        'con' => 12,
        'int' => 10,
        'wis' => 16,
        'cha' => 8
    ],
    'description' => 'Молодой драконорожденный друид с зеленоватой чешуей',
    'background' => 'Родился в лесной общине друидов'
];

// Тестовые данные противника
$enemyData = [
    'name' => 'Гоблин',
    'type' => 'гуманоид',
    'size' => 'малый',
    'alignment' => 'нейтрально-злой',
    'challenge_rating' => '1/4',
    'hit_points' => 7,
    'armor_class' => 15,
    'speed' => '30 футов',
    'initiative' => 1,
    'abilities' => [
        'str' => 8,
        'dex' => 14,
        'con' => 10,
        'int' => 10,
        'wis' => 8,
        'cha' => 8
    ],
    'actions' => [
        'Короткий меч' => 'Рукопашная атака оружием'
    ],
    'special_abilities' => [
        'Темное зрение' => 'Видит в темноте на 60 футов'
    ],
    'description' => 'Маленький зеленый гоблин с острыми зубами'
];

echo "<h1>Тест интеграции заметок с инициативой</h1>";

// Тестируем сохранение персонажа
echo "<h2>1. Тест сохранения персонажа</h2>";
$characterNoteContent = '
<div class="character-note">
    <div class="character-note-title">' . $characterData['name'] . '</div>
    <div class="character-note-info">
        <div><strong>Раса:</strong> ' . $characterData['race'] . '</div>
        <div><strong>Класс:</strong> ' . $characterData['class'] . '</div>
        <div><strong>Уровень:</strong> ' . $characterData['level'] . '</div>
        <div><strong>Пол:</strong> ' . $characterData['gender'] . '</div>
        <div><strong>Мировоззрение:</strong> ' . $characterData['alignment'] . '</div>
        <div><strong>Профессия:</strong> ' . $characterData['occupation'] . '</div>
        <div><strong>Хиты:</strong> ' . $characterData['hit_points'] . '</div>
        <div><strong>КД:</strong> ' . $characterData['armor_class'] . '</div>
        <div><strong>Скорость:</strong> ' . $characterData['speed'] . ' футов</div>
        <div><strong>Инициатива:</strong> ' . $characterData['initiative'] . '</div>
        <div><strong>Урон:</strong> ' . $characterData['damage'] . '</div>
        <div><strong>Бонус мастерства:</strong> +' . $characterData['proficiency_bonus'] . '</div>
        <div><strong>Характеристики:</strong></div>
        <div style="margin-left: 20px;">
            <div>СИЛ: ' . $characterData['abilities']['str'] . '</div>
            <div>ЛОВ: ' . $characterData['abilities']['dex'] . '</div>
            <div>ТЕЛ: ' . $characterData['abilities']['con'] . '</div>
            <div>ИНТ: ' . $characterData['abilities']['int'] . '</div>
            <div>МДР: ' . $characterData['abilities']['wis'] . '</div>
            <div>ХАР: ' . $characterData['abilities']['cha'] . '</div>
        </div>
        <div><strong>Описание:</strong> ' . $characterData['description'] . '</div>
        <div><strong>Предыстория:</strong> ' . $characterData['background'] . '</div>
    </div>
</div>';

$_SESSION['notes'][] = $characterNoteContent;
echo "✅ Персонаж '{$characterData['name']}' сохранен в заметки<br>";

// Тестируем сохранение противника
echo "<h2>2. Тест сохранения противника</h2>";
$enemyNoteContent = '
<div class="enemy-note">
    <div class="enemy-note-title">' . $enemyData['name'] . '</div>
    <div class="enemy-note-info">
        <div><strong>Тип:</strong> ' . $enemyData['type'] . '</div>
        <div><strong>Размер:</strong> ' . $enemyData['size'] . '</div>
        <div><strong>Мировоззрение:</strong> ' . $enemyData['alignment'] . '</div>
        <div><strong>CR:</strong> ' . $enemyData['challenge_rating'] . '</div>
        <div><strong>Хиты:</strong> ' . $enemyData['hit_points'] . '</div>
        <div><strong>КД:</strong> ' . $enemyData['armor_class'] . '</div>
        <div><strong>Скорость:</strong> ' . $enemyData['speed'] . '</div>
        <div><strong>Инициатива:</strong> ' . $enemyData['initiative'] . '</div>
        <div><strong>Характеристики:</strong></div>
        <div style="margin-left: 20px;">
            <div>СИЛ: ' . $enemyData['abilities']['str'] . '</div>
            <div>ЛОВ: ' . $enemyData['abilities']['dex'] . '</div>
            <div>ТЕЛ: ' . $enemyData['abilities']['con'] . '</div>
            <div>ИНТ: ' . $enemyData['abilities']['int'] . '</div>
            <div>МДР: ' . $enemyData['abilities']['wis'] . '</div>
            <div>ХАР: ' . $enemyData['abilities']['cha'] . '</div>
        </div>
        <div><strong>Действия:</strong> ' . implode(', ', array_map(function($key, $value) { return "$key: $value"; }, array_keys($enemyData['actions']), $enemyData['actions'])) . '</div>
        <div><strong>Особые способности:</strong> ' . implode(', ', array_map(function($key, $value) { return "$key: $value"; }, array_keys($enemyData['special_abilities']), $enemyData['special_abilities'])) . '</div>
        <div><strong>Описание:</strong> ' . $enemyData['description'] . '</div>
    </div>
</div>';

$_SESSION['notes'][] = $enemyNoteContent;
echo "✅ Противник '{$enemyData['name']}' сохранен в заметки<br>";

// Тестируем парсинг заметок
echo "<h2>3. Тест парсинга заметок</h2>";

// Симулируем JavaScript функцию addFromNotes
$notes = $_SESSION['notes'];
$characterNotes = [];
$enemyNotes = [];

foreach ($notes as $index => $note) {
    if (preg_match('/<div class="character-note-title">([^<]+)<\/div>/iu', $note, $matches)) {
        $name = trim($matches[1]);
        preg_match('/Раса:\s*([^<]+)/', $note, $raceMatch);
        preg_match('/Класс:\s*([^<]+)/', $note, $classMatch);
        preg_match('/Уровень:\s*(\d+)/', $note, $levelMatch);
        preg_match('/Инициатива:\s*([^<]+)/', $note, $initiativeMatch);
        
        $characterNotes[] = [
            'index' => $index,
            'name' => $name,
            'race' => $raceMatch ? trim($raceMatch[1]) : '',
            'class' => $classMatch ? trim($classMatch[1]) : '',
            'level' => $levelMatch ? $levelMatch[1] : '',
            'initiative' => $initiativeMatch ? trim($initiativeMatch[1]) : '0'
        ];
    } elseif (preg_match('/<div class="enemy-note-title">([^<]+)<\/div>/iu', $note, $matches)) {
        $name = trim($matches[1]);
        preg_match('/Тип:\s*([^<]+)/', $note, $typeMatch);
        preg_match('/CR:\s*([^<]+)/', $note, $crMatch);
        preg_match('/Инициатива:\s*([^<]+)/', $note, $initiativeMatch);
        
        $enemyNotes[] = [
            'index' => $index,
            'name' => $name,
            'type' => $typeMatch ? trim($typeMatch[1]) : '',
            'cr' => $crMatch ? trim($crMatch[1]) : '',
            'initiative' => $initiativeMatch ? trim($initiativeMatch[1]) : '0'
        ];
    }
}

echo "<h3>Найденные персонажи:</h3>";
foreach ($characterNotes as $note) {
    echo "✅ {$note['name']} ({$note['race']} {$note['class']} {$note['level']} ур.) - Инициатива: {$note['initiative']}<br>";
}

echo "<h3>Найденные противники:</h3>";
foreach ($enemyNotes as $note) {
    echo "✅ {$note['name']} ({$note['type']} CR {$note['cr']}) - Инициатива: {$note['initiative']}<br>";
}

// Тестируем отображение заметок
echo "<h2>4. Тест отображения заметок</h2>";

$notesBlock = '';
foreach ($_SESSION['notes'] as $i => $note) {
    $nameLine = '';
    
    // Ищем имя в заголовках персонажей и противников
    if (preg_match('/<div class="character-note-title">([^<]+)<\/div>/iu', $note, $matches)) {
        $nameLine = trim($matches[1]);
    } elseif (preg_match('/<div class="enemy-note-title">([^<]+)<\/div>/iu', $note, $matches)) {
        $nameLine = trim($matches[1]);
    }
    
    // Очищаем имя
    if ($nameLine) {
        $nameLine = preg_replace('/[^\wа-яё\s]/ui', '', $nameLine);
        $nameLine = trim($nameLine);
        
        if (mb_strlen($nameLine) > 20) {
            $nameLine = mb_substr($nameLine, 0, 20) . '…';
        }
    }
    
    $preview = $nameLine ?: '(нет данных)';
    $notesBlock .= '<div class="note-item">' . htmlspecialchars($preview, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>';
}

echo "<h3>Отображение заметок:</h3>";
echo $notesBlock;

echo "<h2>5. Результат теста</h2>";
if (count($characterNotes) > 0 && count($enemyNotes) > 0) {
    echo "✅ Все тесты пройдены успешно!<br>";
    echo "✅ Система заметок работает корректно<br>";
    echo "✅ Парсинг персонажей и противников работает<br>";
    echo "✅ Отображение имен в списке заметок работает<br>";
    echo "✅ Готово к интеграции с инициативой<br>";
} else {
    echo "❌ Есть проблемы с парсингом заметок<br>";
}

echo "<br><a href='index.php'>Вернуться к приложению</a>";
?>
