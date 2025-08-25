<?php
// Убираем заголовки для использования в тестах
if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json');
}
require_once __DIR__ . '/../config.php';

class CharacterGenerator {
    private $dnd5e_api_url = 'https://www.dnd5eapi.co/api';
    private $deepseek_api_key;
    private $occupations = [];
    
    public function __construct() {
        $this->deepseek_api_key = getApiKey('deepseek');
        $this->loadOccupations();
    }
    
    /**
     * Загрузка профессий из JSON файла
     */
    private function loadOccupations() {
        $jsonFile = __DIR__ . '/../pdf/d100_unique_traders.json';
        if (file_exists($jsonFile)) {
            $jsonData = json_decode(file_get_contents($jsonFile), true);
            if (isset($jsonData['data']['occupations'])) {
                $this->occupations = $jsonData['data']['occupations'];
            }
        }
    }
    
    /**
     * База имён для разных рас
     */
    private function getNamesByRace($race) {
        $names = [
            'human' => [
                'male' => ['Алексей', 'Дмитрий', 'Иван', 'Михаил', 'Сергей', 'Андрей', 'Владимир', 'Николай', 'Петр', 'Александр'],
                'female' => ['Анна', 'Елена', 'Мария', 'Ольга', 'Татьяна', 'Ирина', 'Наталья', 'Светлана', 'Екатерина', 'Юлия']
            ],
            'elf' => [
                'male' => ['Леголас', 'Элронд', 'Галадриэль', 'Арвен', 'Элронд', 'Глорфиндель', 'Келеборн', 'Эрестор', 'Линдон', 'Трандуил'],
                'female' => ['Арвен', 'Галадриэль', 'Элвинг', 'Нимродэль', 'Идриль', 'Аредэль', 'Лутиэн', 'Мелиан', 'Эарвен', 'Финдуилас']
            ],
            'dwarf' => [
                'male' => ['Торин', 'Балин', 'Двалин', 'Глоин', 'Оин', 'Бифур', 'Бофур', 'Бомбур', 'Дори', 'Нори'],
                'female' => ['Дис', 'Фрида', 'Хельга', 'Ингрид', 'Сигрид', 'Астрид', 'Брунхильда', 'Гудрун', 'Хильда', 'Ранхильда']
            ],
            'orc' => [
                'male' => ['Гром', 'Железный Кулак', 'Кровавый Топор', 'Черный Зуб', 'Жестокий', 'Разрушитель', 'Гор', 'Мог', 'Трог', 'Зог'],
                'female' => ['Кровавая Сестра', 'Железная Дева', 'Черная Вдова', 'Громовая', 'Разрушительница', 'Горга', 'Мога', 'Трога', 'Зога', 'Рога']
            ],
            'halfling' => [
                'male' => ['Бильбо', 'Фродо', 'Сэм', 'Пиппин', 'Мерри', 'Том', 'Дик', 'Гарри', 'Боб', 'Роб'],
                'female' => ['Рози', 'Примула', 'Белладонна', 'Пимпернель', 'Пенни', 'Дейзи', 'Поппи', 'Вайолет', 'Ирис', 'Лили']
            ],
            'tiefling' => [
                'male' => ['Азариус', 'Малфеус', 'Зериус', 'Каликс', 'Нокс', 'Векс', 'Рекс', 'Лекс', 'Пекс', 'Текс'],
                'female' => ['Лилит', 'Морган', 'Рейвен', 'Шедоу', 'Ночная', 'Темная', 'Кровавая', 'Огненная', 'Демоническая', 'Адская']
            ],
            'dragonborn' => [
                'male' => ['Дракс', 'Вулькан', 'Игнис', 'Фламбер', 'Эмбер', 'Блейз', 'Файр', 'Смоук', 'Эш', 'Чар'],
                'female' => ['Эмбер', 'Флейм', 'Спарк', 'Блейз', 'Файр', 'Смоук', 'Эш', 'Чар', 'Киндл', 'Берн']
            ],
            'gnome' => [
                'male' => ['Гимли', 'Бимли', 'Димли', 'Фимли', 'Кимли', 'Лимли', 'Мимли', 'Нимли', 'Пимли', 'Римли'],
                'female' => ['Глими', 'Блими', 'Длими', 'Флими', 'Клими', 'Ллими', 'Млими', 'Нлими', 'Плими', 'Рлими']
            ],
            'half-elf' => [
                'male' => ['Элрон', 'Алдрион', 'Калиан', 'Эларион', 'Таларион', 'Маларион', 'Саларион', 'Валарион', 'Фаларион', 'Галарион'],
                'female' => ['Элара', 'Алдрия', 'Калия', 'Элария', 'Талария', 'Малария', 'Салария', 'Валария', 'Фалария', 'Галария']
            ],
            'half-orc' => [
                'male' => ['Гром', 'Железный Кулак', 'Кровавый Топор', 'Черный Зуб', 'Жестокий', 'Разрушитель', 'Гор', 'Мог', 'Трог', 'Зог'],
                'female' => ['Кровавая Сестра', 'Железная Дева', 'Черная Вдова', 'Громовая', 'Разрушительница', 'Горга', 'Мога', 'Трога', 'Зога', 'Рога']
            ]
        ];
        
        $race = strtolower($race);
        if (isset($names[$race])) {
            $gender = rand(0, 1) ? 'male' : 'female';
            $nameList = $names[$race][$gender];
            return $nameList[array_rand($nameList)];
        }
        
        // Fallback имена
        $fallbackNames = ['Алексей', 'Анна', 'Дмитрий', 'Елена', 'Иван', 'Мария', 'Михаил', 'Ольга', 'Сергей', 'Татьяна'];
        return $fallbackNames[array_rand($fallbackNames)];
    }
    
    /**
     * Получение случайной профессии
     */
    private function getRandomOccupation() {
        if (empty($this->occupations)) {
            return 'Странник';
        }
        
        $occupation = $this->occupations[array_rand($this->occupations)];
        return $occupation['name_ru'] ?? 'Странник';
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
                'name' => $this->generateName($race),
                'race' => $race_data['name'],
                'class' => $class_data['name'],
                'level' => $level,
                'alignment' => $this->getAlignmentText($alignment),
                'occupation' => $this->getRandomOccupation(),
                'abilities' => $abilities,
                'hit_points' => $this->calculateHP($class_data, $abilities['con'], $level),
                'armor_class' => $this->calculateAC($class_data, $abilities['dex']),
                'speed' => $this->getSpeed($race_data),
                'initiative' => $this->calculateInitiative($abilities['dex']),
                'proficiency_bonus' => $this->calculateProficiencyBonus($level),
                'proficiencies' => $this->getProficiencies($class_data),
                'spells' => $this->getSpells($class_data, $level, $abilities['int'], $abilities['wis'], $abilities['cha']),
                'features' => $this->getFeatures($class_data, $level),
                'equipment' => $this->getEquipment($class_data),
                'saving_throws' => $this->getSavingThrows($class_data, $abilities)
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
        if (isset($race_data['ability_bonuses'])) {
            foreach ($race_data['ability_bonuses'] as $ability => $bonus) {
                if (isset($abilities[$ability])) {
                    $abilities[$ability] += $bonus;
                    // Ограничиваем максимальное значение 20
                    $abilities[$ability] = min(20, $abilities[$ability]);
                }
            }
        }
        
        // Логируем для отладки
        error_log("Generated abilities: " . json_encode($abilities));
        
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
                error_log("Missing ability: $ability");
                return false;
            }
        }
        
        // Проверяем, что все характеристики находятся в разумном диапазоне 3-20
        foreach ($abilities as $ability_name => $ability_value) {
            if ($ability_value < 3 || $ability_value > 20) {
                error_log("Invalid ability value for $ability_name: $ability_value");
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
     * Получение скорости
     */
    private function getSpeed($race_data) {
        $speed = 30; // Базовая скорость
        if (isset($race_data['traits']) && in_array('Транс', $race_data['traits'])) {
            $speed = 60; // Транс
        }
        return $speed;
    }

    /**
     * Расчет инициативы
     */
    private function calculateInitiative($dex_modifier) {
        return floor(($dex_modifier - 10) / 2);
    }

    /**
     * Расчет бонуса мастерства
     */
    private function calculateProficiencyBonus($level) {
        return floor(($level - 1) / 4) + 2;
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
     * Получение бросков способностей
     */
    private function getSavingThrows($class_data, $abilities) {
        $saving_throws = [];
        
        if (isset($class_data['spellcasting']) && $class_data['spellcasting']) {
            $spellcasting_ability = $class_data['spellcasting_ability'] ?? 'int';
            $spellcasting_ability_score = $abilities[$spellcasting_ability] ?? 10;
            $spellcasting_ability_modifier = floor(($spellcasting_ability_score - 10) / 2);
            $saving_throws[] = ['name' => 'Заклинания', 'modifier' => $spellcasting_ability_modifier];
        }

        $saving_throws[] = ['name' => 'Сила', 'modifier' => floor(($abilities['str'] - 10) / 2)];
        $saving_throws[] = ['name' => 'Ловкость', 'modifier' => floor(($abilities['dex'] - 10) / 2)];
        $saving_throws[] = ['name' => 'Телосложение', 'modifier' => floor(($abilities['con'] - 10) / 2)];
        $saving_throws[] = ['name' => 'Интеллект', 'modifier' => floor(($abilities['int'] - 10) / 2)];
        $saving_throws[] = ['name' => 'Мудрость', 'modifier' => floor(($abilities['wis'] - 10) / 2)];
        $saving_throws[] = ['name' => 'Харизма', 'modifier' => floor(($abilities['cha'] - 10) / 2)];

        return $saving_throws;
    }
    
    /**
     * Генерация имени персонажа
     */
    private function generateName($race) {
        return $this->getNamesByRace($race);
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
        // Формируем полную информацию о персонаже для AI
        $characterInfo = "Персонаж: {$character['name']}, {$character['race']} {$character['class']} {$character['level']} уровня.\n";
        $characterInfo .= "Профессия: {$character['occupation']}\n";
        $characterInfo .= "Мировоззрение: {$character['alignment']}\n";
        $characterInfo .= "Характеристики: СИЛ {$character['abilities']['str']}, ЛОВ {$character['abilities']['dex']}, ТЕЛ {$character['abilities']['con']}, ИНТ {$character['abilities']['int']}, МДР {$character['abilities']['wis']}, ХАР {$character['abilities']['cha']}\n";
        $characterInfo .= "Боевые параметры: Хиты {$character['hit_points']}, КД {$character['armor_class']}, Скорость {$character['speed']} футов, Инициатива {$character['initiative']}, Бонус мастерства +{$character['proficiency_bonus']}\n";
        
        if (!empty($character['proficiencies'])) {
            $characterInfo .= "Владения: " . implode(', ', $character['proficiencies']) . "\n";
        }
        
        if (!empty($character['spells'])) {
            $characterInfo .= "Заклинания: " . implode(', ', $character['spells']) . "\n";
        }
        
        $prompt = "Опиши внешность и характер персонажа на основе его полных данных:\n\n" . $characterInfo . "\n" .
                 "Включи детали внешности, особенности поведения и характерные черты, связанные с его расой, классом, профессией и характеристиками. " .
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
        // Формируем полную информацию о персонаже для AI
        $characterInfo = "Персонаж: {$character['name']}, {$character['race']} {$character['class']} {$character['level']} уровня.\n";
        $characterInfo .= "Профессия: {$character['occupation']}\n";
        $characterInfo .= "Мировоззрение: {$character['alignment']}\n";
        $characterInfo .= "Характеристики: СИЛ {$character['abilities']['str']}, ЛОВ {$character['abilities']['dex']}, ТЕЛ {$character['abilities']['con']}, ИНТ {$character['abilities']['int']}, МДР {$character['abilities']['wis']}, ХАР {$character['abilities']['cha']}\n";
        $characterInfo .= "Боевые параметры: Хиты {$character['hit_points']}, КД {$character['armor_class']}, Скорость {$character['speed']} футов, Инициатива {$character['initiative']}, Бонус мастерства +{$character['proficiency_bonus']}\n";
        
        if (!empty($character['proficiencies'])) {
            $characterInfo .= "Владения: " . implode(', ', $character['proficiencies']) . "\n";
        }
        
        if (!empty($character['spells'])) {
            $characterInfo .= "Заклинания: " . implode(', ', $character['spells']) . "\n";
        }
        
        $prompt = "Создай краткую предысторию персонажа на основе его полных данных:\n\n" . $characterInfo . "\n" .
                 "Включи мотивацию, ключевое событие из прошлого и цель персонажа, связанные с его расой, классом, профессией и характеристиками. " .
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
