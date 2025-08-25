<?php
/**
 * Финальный тест всех исправлений
 */

require_once 'config.php';
require_once 'api/fallback-data.php';
require_once 'api/dnd-api-working.php';
require_once 'api/generate-enemies.php';
require_once 'api/generate-characters.php';

echo "<h1>Финальный тест генераторов D&D</h1>\n";

// Тест 1: Проверка fallback данных
echo "<h2>1. Тест fallback данных</h2>\n";

$races = FallbackData::getRaces();
echo "✓ Доступно рас: " . count($races) . "<br>\n";

$classes = FallbackData::getClasses();
echo "✓ Доступно классов: " . count($classes) . "<br>\n";

$monsters = FallbackData::getMonsters();
echo "✓ Доступно монстров: " . count($monsters) . "<br>\n";

// Тест 2: Генерация NPC
echo "<h2>2. Тест генерации NPC</h2>\n";

try {
    $dndApi = new DndApiWorking();
    $npc = $dndApi->generateNPC([
        'race' => 'human',
        'class' => 'fighter',
        'level' => 1,
        'alignment' => 'neutral'
    ]);
    
    if ($npc) {
        echo "✓ NPC успешно сгенерирован<br>\n";
        echo "Имя: " . $npc['name'] . "<br>\n";
        echo "Раса: " . $npc['race'] . "<br>\n";
        echo "Класс: " . $npc['class'] . "<br>\n";
        echo "Уровень: " . $npc['level'] . "<br>\n";
    } else {
        echo "✗ Ошибка генерации NPC<br>\n";
    }
} catch (Exception $e) {
    echo "✗ Исключение при генерации NPC: " . $e->getMessage() . "<br>\n";
}

// Тест 3: Генерация противников
echo "<h2>3. Тест генерации противников</h2>\n";

try {
    $enemyGenerator = new EnemyGenerator();
    $enemies = $enemyGenerator->generateEnemies([
        'threat_level' => 'easy',
        'count' => 1
    ]);
    
    if ($enemies['success']) {
        echo "✓ Противники успешно сгенерированы<br>\n";
        echo "Количество: " . count($enemies['enemies']) . "<br>\n";
        foreach ($enemies['enemies'] as $enemy) {
            echo "- " . $enemy['name'] . " (CR: " . $enemy['challenge_rating'] . ")<br>\n";
            echo "  Тип: " . $enemy['type'] . "<br>\n";
            echo "  Размер: " . $enemy['size'] . "<br>\n";
            echo "  Мировоззрение: " . $enemy['alignment'] . "<br>\n";
        }
    } else {
        echo "✗ Ошибка генерации противников: " . $enemies['error'] . "<br>\n";
    }
} catch (Exception $e) {
    echo "✗ Исключение при генерации противников: " . $e->getMessage() . "<br>\n";
}

// Тест 4: Генерация персонажей
echo "<h2>4. Тест генерации персонажей</h2>\n";

try {
    $characterGenerator = new CharacterGenerator();
    $character = $characterGenerator->generateCharacter([
        'race' => 'elf',
        'class' => 'wizard',
        'level' => 5,
        'alignment' => 'lawful good'
    ]);
    
    if ($character['success']) {
        echo "✓ Персонаж успешно сгенерирован<br>\n";
        echo "Имя: " . $character['npc']['name'] . "<br>\n";
        echo "Раса: " . $character['npc']['race'] . "<br>\n";
        echo "Класс: " . $character['npc']['class'] . "<br>\n";
        echo "Уровень: " . $character['npc']['level'] . "<br>\n";
        echo "Профессия: " . $character['npc']['occupation'] . "<br>\n";
        echo "Хиты: " . $character['npc']['hit_points'] . "<br>\n";
        echo "КД: " . $character['npc']['armor_class'] . "<br>\n";
        echo "Скорость: " . $character['npc']['speed'] . " футов<br>\n";
        echo "Инициатива: " . $character['npc']['initiative'] . "<br>\n";
        echo "Бонус мастерства: +" . $character['npc']['proficiency_bonus'] . "<br>\n";
    } else {
        echo "✗ Ошибка генерации персонажа: " . $character['error'] . "<br>\n";
    }
} catch (Exception $e) {
    echo "✗ Исключение при генерации персонажа: " . $e->getMessage() . "<br>\n";
}

// Тест 5: Проверка валидации
echo "<h2>5. Тест валидации</h2>\n";

try {
    $characterGenerator = new CharacterGenerator();
    $character = $characterGenerator->generateCharacter([
        'race' => 'invalid_race',
        'class' => 'invalid_class',
        'level' => 25, // Неверный уровень
        'alignment' => 'invalid_alignment'
    ]);
    
    if (!$character['success']) {
        echo "✓ Валидация работает корректно<br>\n";
    } else {
        echo "✗ Валидация не работает<br>\n";
    }
} catch (Exception $e) {
    echo "✓ Валидация работает (исключение поймано)<br>\n";
}

echo "<h2>Тест завершен</h2>\n";
echo "<p>Все основные функции протестированы. Если все тесты прошли успешно, генераторы готовы к использованию.</p>\n";
?>
