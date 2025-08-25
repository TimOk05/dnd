<?php
// Убираем заголовки для использования в тестах
if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json');
}
require_once __DIR__ . '/../config.php';

class CharacterGenerator {
    private $dnd5e_api_url = 'https://www.dnd5eapi.co/api';
    private $deepseek_api_key;
    
    public function __construct() {
        $this->deepseek_api_key = getApiKey('deepseek');
    }
    
    /**
     * Генерация персонажа
     */
    public function generateCharacter($params) {
        $race = $params['race'] ?? 'human';
        $class = $params['class'] ?? 'fighter';
        $level = (int)($params['level'] ?? 1);
        $alignment = $params['alignment'] ?? 'neutral';
        $use_ai = isset($params['use_ai']) && $params['use_ai'] === 'on';
        
        // Валидация параметров
        if ($level < 1 || $level > 20) {
            throw new Exception('Уровень персонажа должен быть от 1 до 20');
        }
        
        $valid_races = ['human', 'elf', 'dwarf', 'halfling', 'orc', 'tiefling', 'dragonborn', 'gnome', 'half-elf', 'half-orc'];
        if (!in_array($race, $valid_races)) {
            throw new Exception('Неверная раса персонажа');
        }
        
        $valid_classes = ['fighter', 'wizard', 'rogue', 'cleric', 'ranger', 'barbarian', 'bard', 'druid', 'monk', 'paladin', 'sorcerer', 'warlock'];
        if (!in_array($class, $valid_classes)) {
            throw new Exception('Неверный класс персонажа');
        }
        
        try {
            // Получаем данные расы
            $race_data = $this->getRaceData($race);
            if (!$race_data) {
                throw new Exception('Не удалось получить данные расы');
            }
            
            // Получаем данные класса
            $class_data = $this->getClassData($class);
            if (!$class_data) {
                throw new Exception('Не удалось получить данные класса');
            }
            
            // Генерируем характеристики
            $abilities = $this->generateAbilities($race_data);
            
            // Проверяем корректность характеристик
            if (!$this->validateAbilities($abilities)) {
                throw new Exception('Ошибка генерации характеристик персонажа');
            }
            
            // Рассчитываем параметры
            $character = [
                'name' => $this->generateName($race_data['name']),
                'race' => $race_data['name'],
                'class' => $class_data['name'],
                'level' => $level,
                'alignment' => $this->getAlignmentText($alignment),
                'abilities' => $abilities,
                'hit_points' => $this->calculateHP($class_data, $abilities['con'], $level),
                'armor_class' => $this->calculateAC($class_data, $abilities['dex']),
                'proficiencies' => $this->getProficiencies($class_data),
                'spells' => $this->getSpells($class_data, $level, $abilities['int'], $abilities['wis'], $abilities['cha']),
                'features' => $this->getFeatures($class_data, $level),
                'equipment' => $this->getEquipment($class_data)
            ];
            
            // Добавляем AI-описание если включено
            if ($use_ai) {
                $character['description'] = $this->generateDescription($character);
                $character['background'] = $this->generateBackground($character);
            }
            
            return [
                'success' => true,
                'npc' => $character
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Получение данных расы
     */
    private function getRaceData($race_index) {
        $fallback_races = [
            'human' => [
                'name' => 'Человек',
                'ability_bonuses' => ['str' => 1, 'dex' => 1, 'con' => 1, 'int' => 1, 'wis' => 1, 'cha' => 1],
                'traits' => ['Универсальность', 'Дополнительное владение навыком']
            ],
            'elf' => [
                'name' => 'Эльф',
                'ability_bonuses' => ['dex' => 2],
                'traits' => ['Темное зрение', 'Келебрас', 'Иммунитет к усыплению', 'Транс']
            ],
            'dwarf' => [
                'name' => 'Дварф',
                'ability_bonuses' => ['con' => 2],
                'traits' => ['Темное зрение', 'Устойчивость к яду', 'Владение боевым топором']
            ],
            'halfling' => [
                'name' => 'Полурослик',
                'ability_bonuses' => ['dex' => 2],
                'traits' => ['Удача', 'Смелость', 'Ловкость полурослика']
            ],
            'orc' => [
                'name' => 'Орк',
                'ability_bonuses' => ['str' => 2, 'con' => 1],
                'traits' => ['Темное зрение', 'Угрожающий', 'Мощная атака']
            ]
        ];
        
        return $fallback_races[$race_index] ?? $fallback_races['human'];
    }
    
    /**
     * Получение данных класса
     */
    private function getClassData($class_index) {
        $fallback_classes = [
            'fighter' => [
                'name' => 'Воин',
                'hit_die' => 10,
                'proficiencies' => ['Все доспехи', 'Щиты', 'Простое оружие', 'Воинское оружие'],
                'features' => ['Боевой стиль', 'Second Wind'],
                'spellcasting' => false
            ],
            'wizard' => [
                'name' => 'Волшебник',
                'hit_die' => 6,
                'proficiencies' => ['Кинжалы', 'Посохи', 'Арбалеты'],
                'features' => ['Заклинания', 'Восстановление заклинаний'],
                'spellcasting' => true,
                'spellcasting_ability' => 'int'
            ],
            'rogue' => [
                'name' => 'Плут',
                'hit_die' => 8,
                'proficiencies' => ['Легкие доспехи', 'Простое оружие', 'Короткие мечи', 'Длинные мечи'],
                'features' => ['Скрытность', 'Sneak Attack'],
                'spellcasting' => false
            ],
            'cleric' => [
                'name' => 'Жрец',
                'hit_die' => 8,
                'proficiencies' => ['Легкие доспехи', 'Средние доспехи', 'Щиты', 'Простое оружие'],
                'features' => ['Заклинания', 'Божественный домен'],
                'spellcasting' => true,
                'spellcasting_ability' => 'wis'
            ],
            'monk' => [
                'name' => 'Монах',
                'hit_die' => 8,
                'proficiencies' => ['Простое оружие', 'Короткие мечи'],
                'features' => ['Безоружная защита', 'Боевые искусства'],
                'spellcasting' => false
            ]
        ];
        
        return $fallback_classes[$class_index] ?? $fallback_classes['fighter'];
    }
    
    /**
     * Генерация характеристик
     */
    private function generateAbilities($race_data) {
        $abilities = [
            'str' => $this->rollAbilityScore(),
            'dex' => $this->rollAbilityScore(),
            'con' => $this->rollAbilityScore(),
            'int' => $this->rollAbilityScore(),
            'wis' => $this->rollAbilityScore(),
            'cha' => $this->rollAbilityScore()
        ];
        
        // Применяем бонусы расы
        foreach ($race_data['ability_bonuses'] as $ability => $bonus) {
            $abilities[$ability] += $bonus;
        }
        
        return $abilities;
    }
    
    /**
     * Бросок характеристики (4d6, убираем минимальный)
     */
    private function rollAbilityScore() {
        $rolls = [];
        for ($i = 0; $i < 4; $i++) {
            $rolls[] = rand(1, 6);
        }
        sort($rolls);
        array_shift($rolls); // Убираем минимальный
        return array_sum($rolls);
    }
    
    /**
     * Проверка корректности характеристик
     */
    private function validateAbilities($abilities) {
        $required_abilities = ['str', 'dex', 'con', 'int', 'wis', 'cha'];
        
        // Проверяем наличие всех характеристик
        foreach ($required_abilities as $ability) {
            if (!isset($abilities[$ability])) {
                return false;
            }
        }
        
        // Проверяем, что все характеристики находятся в диапазоне 8-18
        foreach ($abilities as $ability) {
            if ($ability < 8 || $ability > 18) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Расчет хитов
     */
    private function calculateHP($class_data, $con_modifier, $level) {
        $con_bonus = floor(($con_modifier - 10) / 2);
        $base_hp = $class_data['hit_die'] + $con_bonus;
        $additional_hp = 0;
        
        for ($i = 2; $i <= $level; $i++) {
            $additional_hp += rand(1, $class_data['hit_die']) + $con_bonus;
        }
        
        return max(1, $base_hp + $additional_hp);
    }
    
    /**
     * Расчет класса доспеха
     */
    private function calculateAC($class_data, $dex_modifier) {
        $dex_bonus = floor(($dex_modifier - 10) / 2);
        
        if (in_array('Все доспехи', $class_data['proficiencies'])) {
            return 16 + min(2, $dex_bonus); // Кольчуга
        } elseif (in_array('Средние доспехи', $class_data['proficiencies'])) {
            return 14 + min(2, $dex_bonus); // Кожаный доспех
        } else {
            return 10 + $dex_bonus; // Без доспеха
        }
    }
    
    /**
     * Получение владений
     */
    private function getProficiencies($class_data) {
        return $class_data['proficiencies'];
    }
    
    /**
     * Получение заклинаний
     */
    private function getSpells($class_data, $level, $int, $wis, $cha) {
        if (!$class_data['spellcasting']) {
            return [];
        }
        
        $spellcasting_ability = $class_data['spellcasting_ability'] ?? 'int';
        $ability_score = $$spellcasting_ability;
        $ability_modifier = floor(($ability_score - 10) / 2);
        
        $spells = [];
        if ($level >= 1) {
            $spells[] = 'Свет';
        }
        if ($level >= 3) {
            $spells[] = 'Магическая стрела';
        }
        if ($level >= 5) {
            $spells[] = 'Огненный шар';
        }
        
        return $spells;
    }
    
    /**
     * Получение способностей
     */
    private function getFeatures($class_data, $level) {
        $features = $class_data['features'];
        
        if ($level >= 2) {
            $features[] = 'Дополнительная атака';
        }
        if ($level >= 5) {
            $features[] = 'Улучшенная критическая атака';
        }
        
        return $features;
    }
    
    /**
     * Получение снаряжения
     */
    private function getEquipment($class_data) {
        $equipment = [];
        
        if (in_array('Все доспехи', $class_data['proficiencies'])) {
            $equipment[] = 'Кольчуга';
        } elseif (in_array('Средние доспехи', $class_data['proficiencies'])) {
            $equipment[] = 'Кожаный доспех';
        }
        
        if (in_array('Воинское оружие', $class_data['proficiencies'])) {
            $equipment[] = 'Длинный меч';
        } elseif (in_array('Простое оружие', $class_data['proficiencies'])) {
            $equipment[] = 'Булава';
        }
        
        $equipment[] = 'Рюкзак исследователя';
        $equipment[] = '10 золотых монет';
        
        return $equipment;
    }
    
    /**
     * Генерация имени
     */
    private function generateName($race_name) {
        $names = [
            'Человек' => ['Алексей', 'Мария', 'Дмитрий', 'Анна', 'Сергей'],
            'Эльф' => ['Леголас', 'Галадриэль', 'Элронд', 'Арвен', 'Трандуил'],
            'Дварф' => ['Гимли', 'Торин', 'Балин', 'Двалин', 'Бомбур'],
            'Полурослик' => ['Бильбо', 'Фродо', 'Сэм', 'Пиппин', 'Мерри'],
            'Орк' => ['Гром', 'Клык', 'Топор', 'Кровь', 'Сталь']
        ];
        
        $race_names = $names[$race_name] ?? $names['Человек'];
        return $race_names[array_rand($race_names)];
    }
    
    /**
     * Получение текста мировоззрения
     */
    private function getAlignmentText($alignment) {
        $alignments = [
            'lawful-good' => 'Законно-добрый',
            'neutral-good' => 'Нейтрально-добрый',
            'chaotic-good' => 'Хаотично-добрый',
            'lawful-neutral' => 'Законно-нейтральный',
            'neutral' => 'Нейтральный',
            'chaotic-neutral' => 'Хаотично-нейтральный',
            'lawful-evil' => 'Законно-злой',
            'neutral-evil' => 'Нейтрально-злой',
            'chaotic-evil' => 'Хаотично-злой'
        ];
        
        return $alignments[$alignment] ?? 'Нейтральный';
    }
    
    /**
     * Генерация описания с помощью AI
     */
    private function generateDescription($character) {
        $prompt = "Опиши внешность и характер персонажа: {$character['name']}, {$character['race']} {$character['class']} {$character['level']} уровня. " .
                 "Включи детали внешности, особенности поведения и характерные черты. " .
                 "Ответ должен быть кратким (2-3 предложения) и атмосферным.";
        
        try {
            $response = $this->callDeepSeek($prompt);
            return $response ?: 'Описание не определено';
        } catch (Exception $e) {
            return 'Описание не определено';
        }
    }
    
    /**
     * Генерация предыстории с помощью AI
     */
    private function generateBackground($character) {
        $prompt = "Создай краткую предысторию для персонажа: {$character['name']}, {$character['race']} {$character['class']} {$character['level']} уровня. " .
                 "Включи мотивацию, ключевое событие из прошлого и цель персонажа. " .
                 "Ответ должен быть кратким (2-3 предложения) и интересным.";
        
        try {
            $response = $this->callDeepSeek($prompt);
            return $response ?: 'Предыстория не определена';
        } catch (Exception $e) {
            return 'Предыстория не определена';
        }
    }
    
    /**
     * Вызов DeepSeek API
     */
    private function callDeepSeek($prompt) {
        if (!$this->deepseek_api_key) {
            return null;
        }
        
        $data = [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'system', 'content' => 'Ты помощник мастера D&D. Создавай интересных и атмосферных персонажей.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 200,
            'temperature' => 0.8
        ];
        
        $ch = curl_init('https://api.deepseek.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->deepseek_api_key
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if (isset($result['choices'][0]['message']['content'])) {
            return trim($result['choices'][0]['message']['content']);
        }
        
        return null;
    }
}

// Обработка запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $generator = new CharacterGenerator();
    $result = $generator->generateCharacter($_POST);
    
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Метод не поддерживается'
    ]);
}
?>
