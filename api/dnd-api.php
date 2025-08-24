<?php
/**
 * Интеграция с внешними D&D API
 * Поддерживаемые API:
 * - D&D 5e API (dnd5eapi.co)
 * - Open5e API
 * - Custom NPC Generator API
 */

class DndApiManager {
    private $dnd5e_api_url = 'https://www.dnd5eapi.co/api';
    private $open5e_api_url = 'https://open5e.com/api';
    private $custom_npc_api_url = 'https://api.example.com/npc'; // Замените на реальный API
    
    /**
     * Получение списка классов из D&D 5e API
     */
    public function getClasses() {
        $url = $this->dnd5e_api_url . '/classes';
        $response = $this->makeRequest($url);
        
        if ($response && isset($response['results'])) {
            return array_map(function($class) {
                return [
                    'name' => $class['name'],
                    'url' => $class['url']
                ];
            }, $response['results']);
        }
        
        return [];
    }
    
    /**
     * Получение информации о классе
     */
    public function getClassInfo($className) {
        $url = $this->dnd5e_api_url . '/classes/' . strtolower($className);
        return $this->makeRequest($url);
    }
    
    /**
     * Получение списка рас
     */
    public function getRaces() {
        $url = $this->dnd5e_api_url . '/races';
        $response = $this->makeRequest($url);
        
        if ($response && isset($response['results'])) {
            return array_map(function($race) {
                return [
                    'name' => $race['name'],
                    'url' => $race['url']
                ];
            }, $response['results']);
        }
        
        return [];
    }
    
    /**
     * Получение информации о расе
     */
    public function getRaceInfo($raceName) {
        $url = $this->dnd5e_api_url . '/races/' . strtolower($raceName);
        return $this->makeRequest($url);
    }
    
    /**
     * Получение списка оружия
     */
    public function getWeapons() {
        $url = $this->dnd5e_api_url . '/equipment-categories/weapon';
        $response = $this->makeRequest($url);
        
        if ($response && isset($response['equipment'])) {
            return array_map(function($weapon) {
                return [
                    'name' => $weapon['name'],
                    'url' => $weapon['url']
                ];
            }, $response['equipment']);
        }
        
        return [];
    }
    
    /**
     * Получение информации об оружии
     */
    public function getWeaponInfo($weaponName) {
        $url = $this->dnd5e_api_url . '/equipment/' . strtolower(str_replace(' ', '-', $weaponName));
        return $this->makeRequest($url);
    }
    
    /**
     * Получение списка заклинаний
     */
    public function getSpells($level = null) {
        $url = $this->dnd5e_api_url . '/spells';
        if ($level !== null) {
            $url .= '?level=' . $level;
        }
        $response = $this->makeRequest($url);
        
        if ($response && isset($response['results'])) {
            return array_map(function($spell) {
                return [
                    'name' => $spell['name'],
                    'url' => $spell['url']
                ];
            }, $response['results']);
        }
        
        return [];
    }
    
    /**
     * Получение информации о заклинании
     */
    public function getSpellInfo($spellName) {
        $url = $this->dnd5e_api_url . '/spells/' . strtolower(str_replace(' ', '-', $spellName));
        return $this->makeRequest($url);
    }
    
    /**
     * Получение списка монстров
     */
    public function getMonsters($challenge_rating = null) {
        $url = $this->dnd5e_api_url . '/monsters';
        if ($challenge_rating !== null) {
            $url .= '?challenge_rating=' . $challenge_rating;
        }
        $response = $this->makeRequest($url);
        
        if ($response && isset($response['results'])) {
            return array_map(function($monster) {
                return [
                    'name' => $monster['name'],
                    'url' => $monster['url']
                ];
            }, $response['results']);
        }
        
        return [];
    }
    
    /**
     * Получение информации о монстре
     */
    public function getMonsterInfo($monsterName) {
        $url = $this->dnd5e_api_url . '/monsters/' . strtolower(str_replace(' ', '-', $monsterName));
        return $this->makeRequest($url);
    }
    
    /**
     * Генерация NPC с использованием внешнего API
     */
    public function generateNPC($params = []) {
        $defaultParams = [
            'race' => 'human',
            'class' => 'fighter',
            'level' => 1,
            'alignment' => 'neutral',
            'background' => 'soldier'
        ];
        
        $params = array_merge($defaultParams, $params);
        
        // Попытка использовать внешний API для генерации NPC
        $npcData = $this->generateFromExternalAPI($params);
        
        if ($npcData) {
            return $this->formatNPCData($npcData);
        }
        
        // Fallback: генерация на основе данных D&D 5e API
        return $this->generateFromDnd5eAPI($params);
    }
    
    /**
     * Генерация NPC из внешнего API
     */
    private function generateFromExternalAPI($params) {
        // Здесь можно подключить различные API для генерации NPC
        // Например: ChatGPT API, OpenAI API, или специализированные D&D API
        
        $apiData = [
            'race' => $params['race'],
            'class' => $params['class'],
            'level' => $params['level'],
            'alignment' => $params['alignment'],
            'background' => $params['background']
        ];
        
        // Пример запроса к внешнему API
        $response = $this->makeRequest($this->custom_npc_api_url, 'POST', $apiData);
        
        return $response;
    }
    
    /**
     * Генерация NPC на основе D&D 5e API
     */
    private function generateFromDnd5eAPI($params) {
        // Пытаемся получить данные из API
        $raceInfo = $this->getRaceInfo($params['race']);
        $classInfo = $this->getClassInfo($params['class']);
        
        // Если API недоступен, используем локальные данные
        if (!$raceInfo) {
            $raceInfo = $this->getLocalRaceInfo($params['race']);
        }
        if (!$classInfo) {
            $classInfo = $this->getLocalClassInfo($params['class']);
        }
        
        if (!$raceInfo || !$classInfo) {
            return null;
        }
        
        // Генерация базовых характеристик
        $abilities = $this->generateAbilities($raceInfo, $classInfo);
        
        // Генерация имени
        $name = $this->generateName($params['race']);
        
        // Генерация описания
        $description = $this->generateDescription($params);
        
        // Генерация технических параметров
        $technicalParams = $this->generateTechnicalParams($params, $abilities);
        
        return [
            'name' => $name,
            'race' => $raceInfo['name'],
            'class' => $classInfo['name'],
            'level' => $params['level'],
            'alignment' => $params['alignment'],
            'background' => $this->translateBackground($params['background']),
            'abilities' => $abilities,
            'description' => $description,
            'technical_params' => $technicalParams
        ];
    }
    
    /**
     * Генерация характеристик
     */
    private function generateAbilities($raceInfo, $classInfo) {
        $abilities = [
            'strength' => rand(8, 18),
            'dexterity' => rand(8, 18),
            'constitution' => rand(8, 18),
            'intelligence' => rand(8, 18),
            'wisdom' => rand(8, 18),
            'charisma' => rand(8, 18)
        ];
        
        // Применяем бонусы расы
        if (isset($raceInfo['ability_bonuses'])) {
            foreach ($raceInfo['ability_bonuses'] as $bonus) {
                $ability = $bonus['ability_score']['name'];
                $abilityKey = strtolower($ability);
                if (isset($abilities[$abilityKey])) {
                    $abilities[$abilityKey] += $bonus['bonus'];
                }
            }
        }
        
        return $abilities;
    }
    
    /**
     * Генерация имени
     */
    private function generateName($race) {
        $names = [
            'human' => ['Алексей', 'Мария', 'Дмитрий', 'Анна', 'Сергей', 'Елена'],
            'elf' => ['Леголас', 'Галадриэль', 'Элронд', 'Арвен', 'Трандуил', 'Келеборн'],
            'dwarf' => ['Гимли', 'Торин', 'Балин', 'Двалин', 'Бомбур', 'Глоин'],
            'halfling' => ['Бильбо', 'Фродо', 'Сэм', 'Пиппин', 'Мерри', 'Розмари'],
            'orc' => ['Гром', 'Железный Кулак', 'Кровавый Топор', 'Темный Дух', 'Волчья Грива']
        ];
        
        $raceNames = $names[$race] ?? $names['human'];
        return $raceNames[array_rand($raceNames)];
    }
    
    /**
     * Генерация описания
     */
    private function generateDescription($params) {
        $class = $params['class'];
        $alignment = $params['alignment'];
        
        // Базовые описания классов
        $classDescriptions = [
            'fighter' => 'Опытный воин, закаленный в боях и сражениях',
            'wizard' => 'Мудрый маг, изучающий древние тайны магии',
            'rogue' => 'Ловкий плут, мастер скрытности и точных ударов',
            'cleric' => 'Благочестивый жрец, служащий своему божеству',
            'ranger' => 'Следопыт, знающий дикие земли как свои пять пальцев',
            'barbarian' => 'Дикий варвар, чья ярость в бою не знает границ',
            'bard' => 'Обаятельный бард, чьи песни и истории покоряют сердца',
            'druid' => 'Друид, связанный с природой и её силами',
            'monk' => 'Дисциплинированный монах, владеющий боевыми искусствами',
            'paladin' => 'Благородный паладин, защитник справедливости и добра',
            'sorcerer' => 'Сорсерер, чья магия рождается из внутренней силы',
            'warlock' => 'Колдун, заключивший договор с могущественным покровителем'
        ];
        
        // Описания мировоззрений
        $alignmentDescriptions = [
            'lawful good' => 'Следует строгим моральным принципам и всегда стремится помочь другим',
            'neutral good' => 'Добросердечный и отзывчивый, готов помочь нуждающимся',
            'chaotic good' => 'Свободолюбивый и добрый, действует по велению сердца',
            'lawful neutral' => 'Следует порядку и традициям, ценит структуру и дисциплину',
            'neutral' => 'Балансирует между добром и злом, порядком и хаосом',
            'chaotic neutral' => 'Свободолюбивый и непредсказуемый, избегает ограничений',
            'lawful evil' => 'Жестокий и организованный, использует порядок для достижения злых целей',
            'neutral evil' => 'Эгоистичный и безжалостный, готов на все ради личной выгоды',
            'chaotic evil' => 'Разрушительный и непредсказуемый, сеет хаос и страдания'
        ];
        
        $classDesc = $classDescriptions[$class] ?? 'Загадочный персонаж с интересным прошлым';
        $alignmentDesc = $alignmentDescriptions[$alignment] ?? 'с неопределенным мировоззрением';
        
        return $classDesc . '. ' . $alignmentDesc . '.';
    }
    
    /**
     * Перевод профессии на русский язык
     */
    private function translateBackground($background) {
        $translations = [
            'soldier' => 'Солдат',
            'criminal' => 'Преступник',
            'sage' => 'Мудрец',
            'noble' => 'Благородный',
            'merchant' => 'Торговец',
            'artisan' => 'Ремесленник',
            'farmer' => 'Фермер',
            'hermit' => 'Отшельник',
            'entertainer' => 'Артист',
            'acolyte' => 'Послушник',
            'outlander' => 'Чужеземец',
            'urchin' => 'Бродяга'
        ];
        
        return $translations[$background] ?? $background;
    }
    
    /**
     * Генерация технических параметров
     */
    private function generateTechnicalParams($params, $abilities) {
        $level = $params['level'];
        $class = $params['class'];
        
        // Базовые хиты по правилам D&D 5e
        $hitDie = $this->getBaseHP($class);
        $conMod = floor(($abilities['constitution'] - 10) / 2);
        
        // Хиты 1 уровня = максимальный результат hit die + модификатор телосложения
        $hp = $hitDie + $conMod;
        
        // Хиты за каждый дополнительный уровень
        for ($i = 2; $i <= $level; $i++) {
            $hp += rand(1, $hitDie) + $conMod;
        }
        
        // Класс доспеха (базовый 10 + модификатор ЛОВ + бонус доспеха)
        $dexMod = floor(($abilities['dexterity'] - 10) / 2);
        $armorBonus = $this->getArmorBonus($class, $level);
        $ac = 10 + $dexMod + $armorBonus;
        
        // Оружие и урон
        $weapon = $this->getClassWeapon($class);
        $weaponDamage = $this->getWeaponDamage($weapon);
        
        // Бонус к атаке
        $attackBonus = $this->getAttackBonus($class, $level, $abilities);
        
        // Спасброски
        $savingThrows = $this->getSavingThrows($class, $abilities);
        
        // Инициатива
        $initiative = $dexMod;
        
        // Скорость
        $speed = $this->getSpeed($params['race']);
        
        return [
            "Хиты: {$hp}",
            "Класс доспеха: {$ac}",
            "Инициатива: {$initiative}",
            "Скорость: {$speed} футов",
            "Оружие: {$weapon} ({$weaponDamage})",
            "Бонус к атаке: +{$attackBonus}",
            "Спасброски: " . implode(', ', $savingThrows),
            "Характеристики: СИЛ " . $abilities['strength'] . 
                " (" . $this->getModifier($abilities['strength']) . "), " .
                "ЛОВ " . $abilities['dexterity'] . 
                " (" . $this->getModifier($abilities['dexterity']) . "), " .
                "ТЕЛ " . $abilities['constitution'] . 
                " (" . $this->getModifier($abilities['constitution']) . "), " .
                "ИНТ " . $abilities['intelligence'] . 
                " (" . $this->getModifier($abilities['intelligence']) . "), " .
                "МДР " . $abilities['wisdom'] . 
                " (" . $this->getModifier($abilities['wisdom']) . "), " .
                "ХАР " . $abilities['charisma'] . 
                " (" . $this->getModifier($abilities['charisma']) . ")"
        ];
    }
    
    /**
     * Получение базовых хитов для класса
     */
    private function getBaseHP($class) {
        $hpTable = [
            'barbarian' => 12,
            'fighter' => 10,
            'paladin' => 10,
            'ranger' => 10,
            'monk' => 8,
            'rogue' => 8,
            'bard' => 8,
            'cleric' => 8,
            'druid' => 8,
            'warlock' => 8,
            'sorcerer' => 6,
            'wizard' => 6
        ];
        
        return $hpTable[$class] ?? 8;
    }
    
    /**
     * Получение оружия для класса
     */
    private function getClassWeapon($class) {
        $weapons = [
            'barbarian' => 'Топор',
            'fighter' => 'Меч',
            'paladin' => 'Меч',
            'ranger' => 'Лук',
            'monk' => 'Кулаки',
            'rogue' => 'Кинжал',
            'bard' => 'Рапира',
            'cleric' => 'Булава',
            'druid' => 'Посох',
            'warlock' => 'Кинжал',
            'sorcerer' => 'Посох',
            'wizard' => 'Посох'
        ];
        
        return $weapons[$class] ?? 'Кулаки';
    }
    
    /**
     * Получение урона оружия
     */
    private function getWeaponDamage($weapon) {
        $damage = [
            'Топор' => '1d12 рубящий',
            'Меч' => '1d8 рубящий',
            'Лук' => '1d8 колющий',
            'Кулаки' => '1d4 дробящий',
            'Кинжал' => '1d4 колющий',
            'Рапира' => '1d8 колющий',
            'Булава' => '1d6 дробящий',
            'Посох' => '1d6 дробящий'
        ];
        
        return $damage[$weapon] ?? '1d4 дробящий';
    }
    
    /**
     * Получение бонуса доспеха для класса
     */
    private function getArmorBonus($class, $level) {
        $armorBonuses = [
            'fighter' => 2, // Кольчуга
            'paladin' => 2, // Кольчуга
            'ranger' => 1,  // Кожаная броня
            'barbarian' => 1, // Кожаная броня
            'monk' => 0,    // Без доспеха
            'rogue' => 1,   // Кожаная броня
            'bard' => 1,    // Кожаная броня
            'cleric' => 2,  // Кольчуга
            'druid' => 1,   // Кожаная броня
            'warlock' => 0, // Без доспеха
            'sorcerer' => 0, // Без доспеха
            'wizard' => 0   // Без доспеха
        ];
        
        return $armorBonuses[$class] ?? 0;
    }
    
    /**
     * Получение бонуса к атаке
     */
    private function getAttackBonus($class, $level, $abilities) {
        $proficiencyBonus = floor(($level - 1) / 4) + 2;
        
        // Определяем основную характеристику для атаки
        $primaryAbility = $this->getPrimaryAbility($class);
        $abilityModifier = floor(($abilities[$primaryAbility] - 10) / 2);
        
        return $proficiencyBonus + $abilityModifier;
    }
    
    /**
     * Получение основной характеристики для класса
     */
    private function getPrimaryAbility($class) {
        $primaryAbilities = [
            'fighter' => 'strength',
            'paladin' => 'strength',
            'ranger' => 'dexterity',
            'barbarian' => 'strength',
            'monk' => 'dexterity',
            'rogue' => 'dexterity',
            'bard' => 'charisma',
            'cleric' => 'wisdom',
            'druid' => 'wisdom',
            'warlock' => 'charisma',
            'sorcerer' => 'charisma',
            'wizard' => 'intelligence'
        ];
        
        return $primaryAbilities[$class] ?? 'strength';
    }
    
    /**
     * Получение спасбросков
     */
    private function getSavingThrows($class, $abilities) {
        $savingThrows = [
            'fighter' => ['strength', 'constitution'],
            'paladin' => ['wisdom', 'charisma'],
            'ranger' => ['strength', 'dexterity'],
            'barbarian' => ['strength', 'constitution'],
            'monk' => ['strength', 'dexterity'],
            'rogue' => ['dexterity', 'intelligence'],
            'bard' => ['dexterity', 'charisma'],
            'cleric' => ['wisdom', 'charisma'],
            'druid' => ['intelligence', 'wisdom'],
            'warlock' => ['wisdom', 'charisma'],
            'sorcerer' => ['constitution', 'charisma'],
            'wizard' => ['intelligence', 'wisdom']
        ];
        
        $throws = $savingThrows[$class] ?? ['strength', 'dexterity'];
        $result = [];
        
        foreach ($throws as $ability) {
            $modifier = $this->getModifier($abilities[$ability]);
            $modStr = $modifier >= 0 ? "+$modifier" : "$modifier";
            $result[] = ucfirst($ability) . " $modStr";
        }
        
        return $result;
    }
    
    /**
     * Получение модификатора характеристики
     */
    private function getModifier($score) {
        return floor(($score - 10) / 2);
    }
    
    /**
     * Получение скорости для расы
     */
    private function getSpeed($race) {
        $speeds = [
            'human' => 30,
            'elf' => 30,
            'dwarf' => 25,
            'halfling' => 25,
            'orc' => 30,
            'tiefling' => 30,
            'dragonborn' => 30,
            'gnome' => 25,
            'half-elf' => 30,
            'half-orc' => 30
        ];
        
        return $speeds[$race] ?? 30;
    }
    
    /**
     * Форматирование данных NPC для отображения
     */
    private function formatNPCData($npcData) {
        $abilities = $npcData['abilities'];
        $tech = $npcData['technical_params'];
        
        // Перевод названий характеристик
        $abilityTranslations = [
            'strength' => 'Сила',
            'dexterity' => 'Ловкость',
            'constitution' => 'Телосложение',
            'intelligence' => 'Интеллект',
            'wisdom' => 'Мудрость',
            'charisma' => 'Харизма'
        ];
        
        $abilityScores = [];
        foreach ($abilities as $ability => $score) {
            $modifier = floor(($score - 10) / 2);
            $modStr = $modifier >= 0 ? "+$modifier" : "$modifier";
            $abilityName = $abilityTranslations[$ability] ?? ucfirst($ability);
            $abilityScores[] = "$abilityName: $score ($modStr)";
        }
        
        return [
            'name' => $npcData['name'],
            'description' => $npcData['description'],
            'appearance' => $this->generateAppearance($npcData['race']),
            'traits' => $this->generateTraits($npcData['alignment']),
            'technical_params' => [
                'Хиты: ' . $tech['hit_points'],
                'Класс доспеха: ' . $tech['armor_class'],
                'Оружие: ' . $tech['weapon'],
                'Урон: ' . $tech['damage'],
                'Характеристики: ' . implode(', ', $abilityScores)
            ]
        ];
    }
    
    /**
     * Генерация внешности
     */
    private function generateAppearance($race) {
        $appearances = [
            'human' => 'Среднего роста с крепким телосложением и уверенной походкой.',
            'elf' => 'Высокий и стройный, с острыми чертами лица и внимательным взглядом.',
            'dwarf' => 'Коренастый и сильный, с широкими плечами и грубыми руками.',
            'halfling' => 'Невысокий и проворный, с добродушным выражением лица.',
            'orc' => 'Мощный и мускулистый, с грубыми чертами лица и решительным взглядом.'
        ];
        
        return $appearances[$race] ?? 'Среднего роста с крепким телосложением.';
    }
    
    /**
     * Генерация черт характера
     */
    private function generateTraits($alignment) {
        $traits = [
            'lawful good' => 'Честный и справедливый, всегда следует закону и защищает слабых.',
            'neutral good' => 'Добрый и сострадательный, помогает другим без оглядки на закон.',
            'chaotic good' => 'Свободолюбивый и добрый, действует по велению сердца.',
            'lawful neutral' => 'Дисциплинированный и организованный, следует порядку и традициям.',
            'neutral' => 'Уравновешенный и спокойный, избегает крайностей.',
            'chaotic neutral' => 'Свободолюбивый и непредсказуемый, ценит личную свободу.',
            'lawful evil' => 'Жестокий и организованный, использует закон для достижения целей.',
            'neutral evil' => 'Эгоистичный и беспринципный, заботится только о себе.',
            'chaotic evil' => 'Жестокий и непредсказуемый, разрушает всё вокруг себя.'
        ];
        
        return $traits[$alignment] ?? 'Сбалансированный характер с разными чертами.';
    }
    
    /**
     * Получение локальной информации о расе
     */
    private function getLocalRaceInfo($race) {
        $races = [
            'human' => [
                'name' => 'Human',
                'ability_bonuses' => [
                    ['ability_score' => ['name' => 'STR'], 'bonus' => 1],
                    ['ability_score' => ['name' => 'DEX'], 'bonus' => 1],
                    ['ability_score' => ['name' => 'CON'], 'bonus' => 1],
                    ['ability_score' => ['name' => 'INT'], 'bonus' => 1],
                    ['ability_score' => ['name' => 'WIS'], 'bonus' => 1],
                    ['ability_score' => ['name' => 'CHA'], 'bonus' => 1]
                ]
            ],
            'elf' => [
                'name' => 'Elf',
                'ability_bonuses' => [
                    ['ability_score' => ['name' => 'DEX'], 'bonus' => 2]
                ]
            ],
            'dwarf' => [
                'name' => 'Dwarf',
                'ability_bonuses' => [
                    ['ability_score' => ['name' => 'CON'], 'bonus' => 2]
                ]
            ],
            'halfling' => [
                'name' => 'Halfling',
                'ability_bonuses' => [
                    ['ability_score' => ['name' => 'DEX'], 'bonus' => 2]
                ]
            ],
            'orc' => [
                'name' => 'Orc',
                'ability_bonuses' => [
                    ['ability_score' => ['name' => 'STR'], 'bonus' => 2],
                    ['ability_score' => ['name' => 'CON'], 'bonus' => 1]
                ]
            ],
            'tiefling' => [
                'name' => 'Tiefling',
                'ability_bonuses' => [
                    ['ability_score' => ['name' => 'CHA'], 'bonus' => 2],
                    ['ability_score' => ['name' => 'INT'], 'bonus' => 1]
                ]
            ],
            'dragonborn' => [
                'name' => 'Dragonborn',
                'ability_bonuses' => [
                    ['ability_score' => ['name' => 'STR'], 'bonus' => 2],
                    ['ability_score' => ['name' => 'CHA'], 'bonus' => 1]
                ]
            ],
            'gnome' => [
                'name' => 'Gnome',
                'ability_bonuses' => [
                    ['ability_score' => ['name' => 'INT'], 'bonus' => 2]
                ]
            ],
            'half-elf' => [
                'name' => 'Half-Elf',
                'ability_bonuses' => [
                    ['ability_score' => ['name' => 'CHA'], 'bonus' => 2]
                ]
            ],
            'half-orc' => [
                'name' => 'Half-Orc',
                'ability_bonuses' => [
                    ['ability_score' => ['name' => 'STR'], 'bonus' => 2],
                    ['ability_score' => ['name' => 'CON'], 'bonus' => 1]
                ]
            ]
        ];
        
        return $races[$race] ?? $races['human'];
    }
    
    /**
     * Получение локальной информации о классе
     */
    private function getLocalClassInfo($class) {
        $classes = [
            'fighter' => ['name' => 'Fighter', 'hit_die' => 10],
            'wizard' => ['name' => 'Wizard', 'hit_die' => 6],
            'rogue' => ['name' => 'Rogue', 'hit_die' => 8],
            'cleric' => ['name' => 'Cleric', 'hit_die' => 8],
            'ranger' => ['name' => 'Ranger', 'hit_die' => 10],
            'barbarian' => ['name' => 'Barbarian', 'hit_die' => 12],
            'bard' => ['name' => 'Bard', 'hit_die' => 8],
            'druid' => ['name' => 'Druid', 'hit_die' => 8],
            'monk' => ['name' => 'Monk', 'hit_die' => 8],
            'paladin' => ['name' => 'Paladin', 'hit_die' => 10],
            'sorcerer' => ['name' => 'Sorcerer', 'hit_die' => 6],
            'warlock' => ['name' => 'Warlock', 'hit_die' => 8]
        ];
        
        return $classes[$class] ?? $classes['fighter'];
    }
    
    /**
     * Выполнение HTTP запроса
     */
    private function makeRequest($url, $method = 'GET', $data = null) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: DnD-Copilot/1.0'
        ]);
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            error_log("DnD API Error: $error");
            return null;
        }
        
        if ($httpCode !== 200) {
            error_log("DnD API HTTP Error: $httpCode");
            return null;
        }
        
        return json_decode($response, true);
    }
}

// Экспорт класса для использования в других файлах
if (isset($GLOBALS['dnd_api_manager'])) {
    $GLOBALS['dnd_api_manager'] = new DndApiManager();
}
?>
