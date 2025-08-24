<?php
header('Content-Type: application/json');
require_once '../config.php';

class EnemyGenerator {
    private $dnd5e_api_url = 'https://www.dnd5eapi.co/api';
    private $deepseek_api_key;
    
    public function __construct() {
        $this->deepseek_api_key = getApiKey('deepseek');
    }
    
    /**
     * Генерация противников на основе уровня угрозы
     */
    public function generateEnemies($params) {
        $threat_level = $params['threat_level'] ?? 'medium';
        $count = (int)($params['count'] ?? 1);
        $enemy_type = $params['enemy_type'] ?? '';
        $environment = $params['environment'] ?? '';
        $use_ai = isset($params['use_ai']) && $params['use_ai'] === 'on';
        
        // Определяем CR на основе уровня угрозы
        $cr_range = $this->getCRRange($threat_level);
        
        try {
            $enemies = [];
            
            for ($i = 0; $i < $count; $i++) {
                $enemy = $this->generateSingleEnemy($cr_range, $enemy_type, $environment, $use_ai);
                if ($enemy) {
                    $enemies[] = $enemy;
                }
            }
            
            if (empty($enemies)) {
                throw new Exception('Не удалось найти подходящих противников');
            }
            
            return [
                'success' => true,
                'enemies' => $enemies,
                'threat_level' => $threat_level,
                'count' => count($enemies)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Получение диапазона CR на основе уровня угрозы
     */
    private function getCRRange($threat_level) {
        switch ($threat_level) {
            case 'easy':
                return ['min' => 0, 'max' => 3]; // CR 0-3
            case 'medium':
                return ['min' => 2, 'max' => 7]; // CR 2-7
            case 'hard':
                return ['min' => 5, 'max' => 12]; // CR 5-12
            case 'deadly':
                return ['min' => 10, 'max' => 20]; // CR 10-20
            default:
                return ['min' => 2, 'max' => 7];
        }
    }
    
    /**
     * Генерация одного противника
     */
    private function generateSingleEnemy($cr_range, $enemy_type, $environment, $use_ai) {
        // Используем fallback данные для надежности
        $fallback_monsters = $this->getFallbackMonsters();
        
        // Фильтруем монстров по CR и типу
        $filtered_monsters = $this->filterFallbackMonsters($fallback_monsters, $cr_range, $enemy_type, $environment);
        
        if (empty($filtered_monsters)) {
            // Если не найдено подходящих, берем случайного монстра
            $filtered_monsters = $fallback_monsters;
        }
        
        // Выбираем случайного монстра
        $monster = $filtered_monsters[array_rand($filtered_monsters)];
        
        // Получаем детальную информацию о монстре
        $monster_details = $this->getFallbackMonsterDetails($monster['index']);
        
        if (!$monster_details) {
            throw new Exception('Не удалось получить информацию о монстре');
        }
        
        // Формируем результат
        $enemy = [
            'name' => $monster_details['name'],
            'challenge_rating' => $monster_details['challenge_rating'],
            'type' => $monster_details['type'],
            'size' => $monster_details['size'],
            'alignment' => $monster_details['alignment'],
            'environment' => $this->getEnvironment($monster_details),
            'description' => $monster_details['desc'] ?? '',
            'abilities' => $monster_details['stats'],
            'combat_stats' => $this->extractCombatStats($monster_details),
            'actions' => $this->extractActions($monster_details)
        ];
        
        // Добавляем AI-описание тактики если включено
        if ($use_ai) {
            $enemy['tactics'] = $this->generateTactics($enemy);
        }
        
        return $enemy;
    }
    
    /**
     * Получение списка монстров
     */
    private function getMonstersList() {
        $url = $this->dnd5e_api_url . '/monsters';
        $response = $this->makeRequest($url);
        
        if ($response && isset($response['results'])) {
            return $response['results'];
        }
        
        // Fallback: возвращаем базовый список монстров если API недоступен
        return $this->getFallbackMonsters();
    }
    
    /**
     * Fallback список монстров
     */
    private function getFallbackMonsters() {
        return [
            // Легкие противники (CR 1/8 - 1/4)
            ['index' => 'bandit', 'name' => 'Бандит'],
            ['index' => 'cultist', 'name' => 'Культист'],
            ['index' => 'goblin', 'name' => 'Гоблин'],
            ['index' => 'kobold', 'name' => 'Кобольд'],
            ['index' => 'rat', 'name' => 'Гигантская крыса'],
            ['index' => 'spider', 'name' => 'Гигантский паук'],
            ['index' => 'wolf', 'name' => 'Волк'],
            ['index' => 'zombie', 'name' => 'Зомби'],
            ['index' => 'skeleton', 'name' => 'Скелет'],
            
            // Средние противники (CR 1/2 - 2)
            ['index' => 'orc', 'name' => 'Орк'],
            ['index' => 'hobgoblin', 'name' => 'Хобгоблин'],
            ['index' => 'bugbear', 'name' => 'Багбир'],
            ['index' => 'gnoll', 'name' => 'Гнолл'],
            ['index' => 'ogre', 'name' => 'Огр'],
            ['index' => 'bear', 'name' => 'Медведь'],
            ['index' => 'tiger', 'name' => 'Тигр'],
            ['index' => 'ghoul', 'name' => 'Гуль'],
            ['index' => 'wight', 'name' => 'Вайт'],
            
            // Сложные противники (CR 3 - 7)
            ['index' => 'troll', 'name' => 'Тролль'],
            ['index' => 'minotaur', 'name' => 'Минотавр'],
            ['index' => 'cyclops', 'name' => 'Циклоп'],
            ['index' => 'wyvern', 'name' => 'Виверна'],
            ['index' => 'manticore', 'name' => 'Мантикора'],
            ['index' => 'vampire', 'name' => 'Вампир'],
            ['index' => 'wraith', 'name' => 'Призрак'],
            
            // Смертельные противники (CR 8+)
            ['index' => 'dragon', 'name' => 'Молодой дракон'],
            ['index' => 'giant', 'name' => 'Гигант'],
            ['index' => 'beholder', 'name' => 'Наблюдатель'],
            ['index' => 'lich', 'name' => 'Лич']
        ];
    }
    
    /**
     * Фильтрация fallback монстров
     */
    private function filterFallbackMonsters($monsters, $cr_range, $enemy_type, $environment) {
        $filtered = [];
        
        foreach ($monsters as $monster) {
            $details = $this->getFallbackMonsterDetails($monster['index']);
            
            if (!$details) continue;
            
            // Проверяем CR
            $cr = $this->parseCR($details['challenge_rating']);
            if ($cr < $cr_range['min'] || $cr > $cr_range['max']) {
                continue;
            }
            
            // Проверяем тип (если указан)
            if ($enemy_type && $enemy_type !== '' && strpos(strtolower($details['type']), strtolower($enemy_type)) === false) {
                continue;
            }
            
            // Проверяем среду (если указана)
            if ($environment && $environment !== '' && !$this->checkEnvironment($details, $environment)) {
                continue;
            }
            
            $filtered[] = $monster;
        }
        
        return $filtered;
    }
    
    /**
     * Фильтрация монстров по параметрам
     */
    private function filterMonsters($monsters, $cr_range, $enemy_type, $environment) {
        $filtered = [];
        
        foreach ($monsters as $monster) {
            // Получаем детали монстра для фильтрации
            $details = $this->getMonsterDetails($monster['index']);
            
            if (!$details) continue;
            
            // Проверяем CR
            $cr = $this->parseCR($details['challenge_rating']);
            if ($cr < $cr_range['min'] || $cr > $cr_range['max']) {
                continue;
            }
            
            // Проверяем тип (если указан)
            if ($enemy_type && $enemy_type !== '' && strpos(strtolower($details['type']), strtolower($enemy_type)) === false) {
                continue;
            }
            
            // Проверяем среду (если указана)
            if ($environment && $environment !== '' && !$this->checkEnvironment($details, $environment)) {
                continue;
            }
            
            $filtered[] = $monster;
        }
        
        return $filtered;
    }
    
    /**
     * Получение детальной информации о монстре
     */
    private function getMonsterDetails($monster_index) {
        $url = $this->dnd5e_api_url . '/monsters/' . $monster_index;
        $response = $this->makeRequest($url);
        
        if ($response) {
            return $response;
        }
        
        // Fallback: возвращаем базовые данные если API недоступен
        return $this->getFallbackMonsterDetails($monster_index);
    }
    
    /**
     * Fallback данные монстров
     */
    private function getFallbackMonsterDetails($monster_index) {
        $fallback_data = [
            'goblin' => [
                'name' => 'Гоблин',
                'challenge_rating' => '1/4',
                'type' => 'humanoid',
                'size' => 'Маленький',
                'alignment' => 'Нейтрально-злой',
                'desc' => 'Маленькое зеленокожее существо с острыми ушами и желтыми глазами.',
                'stats' => ['str' => 8, 'dex' => 14, 'con' => 10, 'int' => 10, 'wis' => 8, 'cha' => 8],
                'armor_class' => [['value' => 15]],
                'hit_points' => ['average' => 7],
                'speed' => ['walk' => '30'],
                'actions' => [
                    ['name' => 'Короткий меч', 'desc' => 'Рукопашная атака оружием: +4 к попаданию, досягаемость 5 футов, одна цель. Попадание: 5 (1d6 + 2) колющего урона.']
                ]
            ],
            'orc' => [
                'name' => 'Орк',
                'challenge_rating' => '1/2',
                'type' => 'humanoid',
                'size' => 'Средний',
                'alignment' => 'Хаотично-злой',
                'desc' => 'Крупное мускулистое существо с зеленой кожей и клыками.',
                'stats' => ['str' => 16, 'dex' => 12, 'con' => 16, 'int' => 7, 'wis' => 11, 'cha' => 10],
                'armor_class' => [['value' => 13]],
                'hit_points' => ['average' => 15],
                'speed' => ['walk' => '30'],
                'actions' => [
                    ['name' => 'Грейсворд', 'desc' => 'Рукопашная атака оружием: +5 к попаданию, досягаемость 5 футов, одна цель. Попадание: 9 (1d12 + 3) рубящего урона.']
                ]
            ],
            'wolf' => [
                'name' => 'Волк',
                'challenge_rating' => '1/4',
                'type' => 'beast',
                'size' => 'Средний',
                'alignment' => 'Без мировоззрения',
                'desc' => 'Дикий волк с серой шерстью и острыми клыками.',
                'stats' => ['str' => 12, 'dex' => 15, 'con' => 12, 'int' => 3, 'wis' => 12, 'cha' => 6],
                'armor_class' => [['value' => 13]],
                'hit_points' => ['average' => 11],
                'speed' => ['walk' => '40'],
                'actions' => [
                    ['name' => 'Укус', 'desc' => 'Атака оружием ближнего боя: +4 к попаданию, досягаемость 5 футов, одна цель. Попадание: 7 (2d4 + 2) колющего урона.']
                ]
            ],
            'bandit' => [
                'name' => 'Бандит',
                'challenge_rating' => '1/8',
                'type' => 'humanoid',
                'size' => 'Средний',
                'alignment' => 'Любое не-законное',
                'desc' => 'Обычный разбойник с кинжалом и луком.',
                'stats' => ['str' => 12, 'dex' => 12, 'con' => 12, 'int' => 10, 'wis' => 10, 'cha' => 10],
                'armor_class' => [['value' => 12]],
                'hit_points' => ['average' => 11],
                'speed' => ['walk' => '30'],
                'actions' => [
                    ['name' => 'Кинжал', 'desc' => 'Рукопашная атака оружием: +3 к попаданию, досягаемость 5 футов, одна цель. Попадание: 3 (1d4 + 1) колющего урона.']
                ]
            ],
            'cultist' => [
                'name' => 'Культист',
                'challenge_rating' => '1/8',
                'type' => 'humanoid',
                'size' => 'Средний',
                'alignment' => 'Любое не-доброе',
                'desc' => 'Последователь темного культа с кинжалом.',
                'stats' => ['str' => 11, 'dex' => 12, 'con' => 10, 'int' => 10, 'wis' => 8, 'cha' => 8],
                'armor_class' => [['value' => 12]],
                'hit_points' => ['average' => 9],
                'speed' => ['walk' => '30'],
                'actions' => [
                    ['name' => 'Кинжал', 'desc' => 'Рукопашная атака оружием: +3 к попаданию, досягаемость 5 футов, одна цель. Попадание: 3 (1d4 + 1) колющего урона.']
                ]
            ],
            'skeleton' => [
                'name' => 'Скелет',
                'challenge_rating' => '1/4',
                'type' => 'undead',
                'size' => 'Средний',
                'alignment' => 'Законно-злой',
                'desc' => 'Анимированный скелет с коротким мечом.',
                'stats' => ['str' => 10, 'dex' => 14, 'con' => 15, 'int' => 6, 'wis' => 8, 'cha' => 5],
                'armor_class' => [['value' => 13]],
                'hit_points' => ['average' => 13],
                'speed' => ['walk' => '30'],
                'actions' => [
                    ['name' => 'Короткий меч', 'desc' => 'Рукопашная атака оружием: +4 к попаданию, досягаемость 5 футов, одна цель. Попадание: 5 (1d6 + 2) колющего урона.']
                ]
            ],
            'zombie' => [
                'name' => 'Зомби',
                'challenge_rating' => '1/4',
                'type' => 'undead',
                'size' => 'Средний',
                'alignment' => 'Нейтрально-злой',
                'desc' => 'Медлительный зомби с дубиной.',
                'stats' => ['str' => 13, 'dex' => 6, 'con' => 16, 'int' => 3, 'wis' => 6, 'cha' => 5],
                'armor_class' => [['value' => 8]],
                'hit_points' => ['average' => 22],
                'speed' => ['walk' => '20'],
                'actions' => [
                    ['name' => 'Дубина', 'desc' => 'Рукопашная атака оружием: +3 к попаданию, досягаемость 5 футов, одна цель. Попадание: 3 (1d6) дробящего урона.']
                ]
            ],
            'troll' => [
                'name' => 'Тролль',
                'challenge_rating' => '5',
                'type' => 'giant',
                'size' => 'Большой',
                'alignment' => 'Хаотично-злой',
                'desc' => 'Мощное регенерирующее существо с длинными когтями.',
                'stats' => ['str' => 18, 'dex' => 13, 'con' => 20, 'int' => 7, 'wis' => 9, 'cha' => 7],
                'armor_class' => [['value' => 15]],
                'hit_points' => ['average' => 84],
                'speed' => ['walk' => '30'],
                'actions' => [
                    ['name' => 'Когти', 'desc' => 'Рукопашная атака оружием: +7 к попаданию, досягаемость 5 футов, одна цель. Попадание: 11 (2d6 + 4) рубящего урона.']
                ]
            ],
            'ogre' => [
                'name' => 'Огр',
                'challenge_rating' => '2',
                'type' => 'giant',
                'size' => 'Большой',
                'alignment' => 'Хаотично-злой',
                'desc' => 'Огромное тупое существо с дубиной.',
                'stats' => ['str' => 19, 'dex' => 8, 'con' => 16, 'int' => 5, 'wis' => 7, 'cha' => 7],
                'armor_class' => [['value' => 11]],
                'hit_points' => ['average' => 59],
                'speed' => ['walk' => '40'],
                'actions' => [
                    ['name' => 'Дубина', 'desc' => 'Рукопашная атака оружием: +6 к попаданию, досягаемость 5 футов, одна цель. Попадание: 13 (2d8 + 4) дробящего урона.']
                ]
            ],
            'dragon' => [
                'name' => 'Молодой дракон',
                'challenge_rating' => '10',
                'type' => 'dragon',
                'size' => 'Большой',
                'alignment' => 'Хаотично-злой',
                'desc' => 'Молодой красный дракон с огненным дыханием.',
                'stats' => ['str' => 23, 'dex' => 10, 'con' => 21, 'int' => 14, 'wis' => 11, 'cha' => 19],
                'armor_class' => [['value' => 18]],
                'hit_points' => ['average' => 178],
                'speed' => ['walk' => '40', 'fly' => '80'],
                'actions' => [
                    ['name' => 'Укус', 'desc' => 'Рукопашная атака оружием: +11 к попаданию, досягаемость 10 футов, одна цель. Попадание: 17 (2d10 + 6) колющего урона плюс 3 (1d6) огненного урона.']
                ]
            ],
            'kobold' => [
                'name' => 'Кобольд',
                'challenge_rating' => '1/8',
                'type' => 'humanoid',
                'size' => 'Маленький',
                'alignment' => 'Законно-злой',
                'desc' => 'Маленькое чешуйчатое существо с рогами и хвостом.',
                'stats' => ['str' => 7, 'dex' => 15, 'con' => 9, 'int' => 8, 'wis' => 7, 'cha' => 8],
                'armor_class' => [['value' => 12]],
                'hit_points' => ['average' => 5],
                'speed' => ['walk' => '30'],
                'actions' => [
                    ['name' => 'Кинжал', 'desc' => 'Рукопашная атака оружием: +4 к попаданию, досягаемость 5 футов, одна цель. Попадание: 4 (1d4 + 2) колющего урона.']
                ]
            ],
            'rat' => [
                'name' => 'Гигантская крыса',
                'challenge_rating' => '1/8',
                'type' => 'beast',
                'size' => 'Маленький',
                'alignment' => 'Без мировоззрения',
                'desc' => 'Огромная крыса размером с собаку.',
                'stats' => ['str' => 7, 'dex' => 15, 'con' => 11, 'int' => 2, 'wis' => 10, 'cha' => 4],
                'armor_class' => [['value' => 12]],
                'hit_points' => ['average' => 7],
                'speed' => ['walk' => '30'],
                'actions' => [
                    ['name' => 'Укус', 'desc' => 'Атака оружием ближнего боя: +4 к попаданию, досягаемость 5 футов, одна цель. Попадание: 4 (1d4 + 2) колющего урона.']
                ]
            ],
            'spider' => [
                'name' => 'Гигантский паук',
                'challenge_rating' => '1',
                'type' => 'beast',
                'size' => 'Большой',
                'alignment' => 'Без мировоззрения',
                'desc' => 'Огромный паук с ядовитыми клыками.',
                'stats' => ['str' => 14, 'dex' => 16, 'con' => 12, 'int' => 2, 'wis' => 11, 'cha' => 4],
                'armor_class' => [['value' => 14]],
                'hit_points' => ['average' => 26],
                'speed' => ['walk' => '30', 'climb' => '30'],
                'actions' => [
                    ['name' => 'Укус', 'desc' => 'Атака оружием ближнего боя: +5 к попаданию, досягаемость 5 футов, одна цель. Попадание: 7 (1d8 + 3) колющего урона плюс 9 (2d8) ядовитого урона.']
                ]
            ],
            'hobgoblin' => [
                'name' => 'Хобгоблин',
                'challenge_rating' => '1/2',
                'type' => 'humanoid',
                'size' => 'Средний',
                'alignment' => 'Законно-злой',
                'desc' => 'Дисциплинированный воин-гоблиноид с длинным мечом.',
                'stats' => ['str' => 13, 'dex' => 12, 'con' => 12, 'int' => 10, 'wis' => 10, 'cha' => 9],
                'armor_class' => [['value' => 18]],
                'hit_points' => ['average' => 11],
                'speed' => ['walk' => '30'],
                'actions' => [
                    ['name' => 'Длинный меч', 'desc' => 'Рукопашная атака оружием: +3 к попаданию, досягаемость 5 футов, одна цель. Попадание: 5 (1d8 + 1) рубящего урона.']
                ]
            ],
            'bugbear' => [
                'name' => 'Багбир',
                'challenge_rating' => '1',
                'type' => 'humanoid',
                'size' => 'Средний',
                'alignment' => 'Хаотично-злой',
                'desc' => 'Крупный волосатый гоблиноид с утренней звездой.',
                'stats' => ['str' => 15, 'dex' => 14, 'con' => 13, 'int' => 8, 'wis' => 11, 'cha' => 9],
                'armor_class' => [['value' => 16]],
                'hit_points' => ['average' => 27],
                'speed' => ['walk' => '30'],
                'actions' => [
                    ['name' => 'Утренняя звезда', 'desc' => 'Рукопашная атака оружием: +4 к попаданию, досягаемость 5 футов, одна цель. Попадание: 11 (2d8 + 2) дробящего урона.']
                ]
            ],
            'gnoll' => [
                'name' => 'Гнолл',
                'challenge_rating' => '1/2',
                'type' => 'humanoid',
                'size' => 'Средний',
                'alignment' => 'Хаотично-злой',
                'desc' => 'Гиенообразное существо с копьем.',
                'stats' => ['str' => 14, 'dex' => 12, 'con' => 11, 'int' => 6, 'wis' => 10, 'cha' => 7],
                'armor_class' => [['value' => 15]],
                'hit_points' => ['average' => 22],
                'speed' => ['walk' => '30'],
                'actions' => [
                    ['name' => 'Копье', 'desc' => 'Рукопашная атака оружием: +4 к попаданию, досягаемость 5 футов, одна цель. Попадание: 5 (1d6 + 2) колющего урона.']
                ]
            ],
            'bear' => [
                'name' => 'Медведь',
                'challenge_rating' => '1',
                'type' => 'beast',
                'size' => 'Большой',
                'alignment' => 'Без мировоззрения',
                'desc' => 'Мощный бурый медведь с острыми когтями.',
                'stats' => ['str' => 19, 'dex' => 10, 'con' => 16, 'int' => 2, 'wis' => 13, 'cha' => 7],
                'armor_class' => [['value' => 11]],
                'hit_points' => ['average' => 34],
                'speed' => ['walk' => '40'],
                'actions' => [
                    ['name' => 'Когти', 'desc' => 'Рукопашная атака оружием: +6 к попаданию, досягаемость 5 футов, одна цель. Попадание: 11 (2d6 + 4) рубящего урона.']
                ]
            ],
            'tiger' => [
                'name' => 'Тигр',
                'challenge_rating' => '1',
                'type' => 'beast',
                'size' => 'Большой',
                'alignment' => 'Без мировоззрения',
                'desc' => 'Полосатый хищник с острыми клыками.',
                'stats' => ['str' => 17, 'dex' => 15, 'con' => 14, 'int' => 3, 'wis' => 12, 'cha' => 8],
                'armor_class' => [['value' => 12]],
                'hit_points' => ['average' => 37],
                'speed' => ['walk' => '40'],
                'actions' => [
                    ['name' => 'Укус', 'desc' => 'Атака оружием ближнего боя: +5 к попаданию, досягаемость 5 футов, одна цель. Попадание: 8 (1d10 + 3) колющего урона.']
                ]
            ],
            'ghoul' => [
                'name' => 'Гуль',
                'challenge_rating' => '1',
                'type' => 'undead',
                'size' => 'Средний',
                'alignment' => 'Хаотично-злой',
                'desc' => 'Нежить, питающаяся плотью мертвых.',
                'stats' => ['str' => 13, 'dex' => 15, 'con' => 10, 'int' => 7, 'wis' => 10, 'cha' => 6],
                'armor_class' => [['value' => 12]],
                'hit_points' => ['average' => 22],
                'speed' => ['walk' => '30'],
                'actions' => [
                    ['name' => 'Когти', 'desc' => 'Рукопашная атака оружием: +4 к попаданию, досягаемость 5 футов, одна цель. Попадание: 7 (2d4 + 2) рубящего урона.']
                ]
            ],
            'wight' => [
                'name' => 'Вайт',
                'challenge_rating' => '3',
                'type' => 'undead',
                'size' => 'Средний',
                'alignment' => 'Нейтрально-злой',
                'desc' => 'Нежить, высасывающая жизненную силу.',
                'stats' => ['str' => 15, 'dex' => 14, 'con' => 16, 'int' => 10, 'wis' => 13, 'cha' => 15],
                'armor_class' => [['value' => 14]],
                'hit_points' => ['average' => 45],
                'speed' => ['walk' => '30'],
                'actions' => [
                    ['name' => 'Длинный меч', 'desc' => 'Рукопашная атака оружием: +4 к попаданию, досягаемость 5 футов, одна цель. Попадание: 6 (1d8 + 2) рубящего урона плюс 10 (3d6) некротического урона.']
                ]
            ],
            'minotaur' => [
                'name' => 'Минотавр',
                'challenge_rating' => '3',
                'type' => 'monstrosity',
                'size' => 'Большой',
                'alignment' => 'Хаотично-злой',
                'desc' => 'Существо с телом человека и головой быка.',
                'stats' => ['str' => 18, 'dex' => 11, 'con' => 16, 'int' => 6, 'wis' => 16, 'cha' => 9],
                'armor_class' => [['value' => 14]],
                'hit_points' => ['average' => 76],
                'speed' => ['walk' => '40'],
                'actions' => [
                    ['name' => 'Боевой топор', 'desc' => 'Рукопашная атака оружием: +6 к попаданию, досягаемость 5 футов, одна цель. Попадание: 17 (2d12 + 4) рубящего урона.']
                ]
            ],
            'cyclops' => [
                'name' => 'Циклоп',
                'challenge_rating' => '6',
                'type' => 'giant',
                'size' => 'Огромный',
                'alignment' => 'Хаотично-нейтральный',
                'desc' => 'Одноглазый великан с дубиной.',
                'stats' => ['str' => 22, 'dex' => 11, 'con' => 20, 'int' => 8, 'wis' => 6, 'cha' => 10],
                'armor_class' => [['value' => 14]],
                'hit_points' => ['average' => 138],
                'speed' => ['walk' => '30'],
                'actions' => [
                    ['name' => 'Дубина', 'desc' => 'Рукопашная атака оружием: +9 к попаданию, досягаемость 10 футов, одна цель. Попадание: 19 (3d8 + 6) дробящего урона.']
                ]
            ],
            'wyvern' => [
                'name' => 'Виверна',
                'challenge_rating' => '6',
                'type' => 'dragon',
                'size' => 'Большой',
                'alignment' => 'Нейтрально-злой',
                'desc' => 'Драконоподобное существо с ядовитым хвостом.',
                'stats' => ['str' => 19, 'dex' => 10, 'con' => 16, 'int' => 5, 'wis' => 12, 'cha' => 6],
                'armor_class' => [['value' => 13]],
                'hit_points' => ['average' => 110],
                'speed' => ['walk' => '20', 'fly' => '80'],
                'actions' => [
                    ['name' => 'Укус', 'desc' => 'Атака оружием ближнего боя: +7 к попаданию, досягаемость 10 футов, одна цель. Попадание: 11 (2d6 + 4) колющего урона.']
                ]
            ],
            'manticore' => [
                'name' => 'Мантикора',
                'challenge_rating' => '3',
                'type' => 'monstrosity',
                'size' => 'Большой',
                'alignment' => 'Законно-злой',
                'desc' => 'Существо с телом льва, крыльями и хвостом скорпиона.',
                'stats' => ['str' => 17, 'dex' => 16, 'con' => 17, 'int' => 7, 'wis' => 12, 'cha' => 8],
                'armor_class' => [['value' => 14]],
                'hit_points' => ['average' => 68],
                'speed' => ['walk' => '30', 'fly' => '50'],
                'actions' => [
                    ['name' => 'Когти', 'desc' => 'Рукопашная атака оружием: +5 к попаданию, досягаемость 5 футов, одна цель. Попадание: 8 (1d8 + 4) рубящего урона.']
                ]
            ],
            'vampire' => [
                'name' => 'Вампир',
                'challenge_rating' => '13',
                'type' => 'undead',
                'size' => 'Средний',
                'alignment' => 'Законно-злой',
                'desc' => 'Бессмертное существо, питающееся кровью.',
                'stats' => ['str' => 18, 'dex' => 18, 'con' => 18, 'int' => 17, 'wis' => 15, 'cha' => 18],
                'armor_class' => [['value' => 16]],
                'hit_points' => ['average' => 144],
                'speed' => ['walk' => '30'],
                'actions' => [
                    ['name' => 'Укус', 'desc' => 'Атака оружием ближнего боя: +9 к попаданию, досягаемость 5 футов, одна цель. Попадание: 8 (1d6 + 5) колющего урона плюс 10 (3d6) некротического урона.']
                ]
            ],
            'wraith' => [
                'name' => 'Призрак',
                'challenge_rating' => '5',
                'type' => 'undead',
                'size' => 'Средний',
                'alignment' => 'Нейтрально-злой',
                'desc' => 'Призрачное существо из теневого плана.',
                'stats' => ['str' => 6, 'dex' => 16, 'con' => 16, 'int' => 12, 'wis' => 14, 'cha' => 15],
                'armor_class' => [['value' => 13]],
                'hit_points' => ['average' => 67],
                'speed' => ['walk' => '0', 'fly' => '60'],
                'actions' => [
                    ['name' => 'Life Drain', 'desc' => 'Атака оружием ближнего боя: +6 к попаданию, досягаемость 5 футов, одна цель. Попадание: 21 (4d8 + 3) некротического урона.']
                ]
            ],
            'giant' => [
                'name' => 'Гигант',
                'challenge_rating' => '9',
                'type' => 'giant',
                'size' => 'Огромный',
                'alignment' => 'Хаотично-нейтральный',
                'desc' => 'Огромное человекообразное существо.',
                'stats' => ['str' => 25, 'dex' => 8, 'con' => 20, 'int' => 10, 'wis' => 12, 'cha' => 9],
                'armor_class' => [['value' => 15]],
                'hit_points' => ['average' => 138],
                'speed' => ['walk' => '40'],
                'actions' => [
                    ['name' => 'Дубина', 'desc' => 'Рукопашная атака оружием: +12 к попаданию, досягаемость 15 футов, одна цель. Попадание: 21 (3d8 + 7) дробящего урона.']
                ]
            ],
            'beholder' => [
                'name' => 'Наблюдатель',
                'challenge_rating' => '13',
                'type' => 'aberration',
                'size' => 'Большой',
                'alignment' => 'Законно-злой',
                'desc' => 'Сферическое существо с множеством глаз на стеблях.',
                'stats' => ['str' => 10, 'dex' => 14, 'con' => 18, 'int' => 17, 'wis' => 15, 'cha' => 17],
                'armor_class' => [['value' => 18]],
                'hit_points' => ['average' => 180],
                'speed' => ['walk' => '0', 'fly' => '20'],
                'actions' => [
                    ['name' => 'Eye Ray', 'desc' => 'Атака заклинанием: +8 к попаданию, досягаемость 120 футов, одна цель. Попадание: 27 (5d10) урона различного типа.']
                ]
            ],
            'lich' => [
                'name' => 'Лич',
                'challenge_rating' => '21',
                'type' => 'undead',
                'size' => 'Средний',
                'alignment' => 'Любое злое',
                'desc' => 'Бессмертный некромант с огромной магической силой.',
                'stats' => ['str' => 11, 'dex' => 16, 'con' => 16, 'int' => 20, 'wis' => 14, 'cha' => 16],
                'armor_class' => [['value' => 17]],
                'hit_points' => ['average' => 135],
                'speed' => ['walk' => '30'],
                'actions' => [
                    ['name' => 'Disrupt Life', 'desc' => 'Каждый живой в радиусе 20 футов должен пройти спасбросок Телосложения. При провале получает 21 (6d6) некротического урона.']
                ]
            ]
        ];
        
        return $fallback_data[$monster_index] ?? $fallback_data['goblin'];
    }
    
    /**
     * Парсинг CR в числовое значение
     */
    private function parseCR($cr_string) {
        if (is_numeric($cr_string)) {
            return (float)$cr_string;
        }
        
        // Обработка специальных случаев
        $cr_map = [
            '0' => 0,
            '1/8' => 0.125,
            '1/4' => 0.25,
            '1/2' => 0.5
        ];
        
        return $cr_map[$cr_string] ?? 1;
    }
    
    /**
     * Проверка среды обитания
     */
    private function checkEnvironment($monster_details, $environment) {
        // Простая проверка по названию монстра
        $name = strtolower($monster_details['name']);
        $type = strtolower($monster_details['type']);
        
        $environment_keywords = [
            'arctic' => ['ice', 'snow', 'frost', 'arctic'],
            'coastal' => ['sea', 'coast', 'beach', 'water'],
            'desert' => ['desert', 'sand', 'dune'],
            'forest' => ['forest', 'wood', 'tree'],
            'grassland' => ['grass', 'plain', 'field'],
            'hill' => ['hill', 'mountain'],
            'mountain' => ['mountain', 'peak', 'cliff'],
            'swamp' => ['swamp', 'marsh', 'bog'],
            'underdark' => ['underdark', 'cave', 'underground'],
            'urban' => ['city', 'town', 'urban']
        ];
        
        if (isset($environment_keywords[$environment])) {
            foreach ($environment_keywords[$environment] as $keyword) {
                if (strpos($name, $keyword) !== false || strpos($type, $keyword) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Получение среды обитания
     */
    private function getEnvironment($monster_details) {
        // Определяем среду по типу и названию
        $name = strtolower($monster_details['name']);
        $type = strtolower($monster_details['type']);
        
        if (strpos($name, 'dragon') !== false || strpos($type, 'dragon') !== false) {
            return 'Горы/Подземелье';
        } elseif (strpos($name, 'goblin') !== false || strpos($name, 'orc') !== false) {
            return 'Подземелье/Холмы';
        } elseif (strpos($name, 'wolf') !== false || strpos($name, 'bear') !== false) {
            return 'Лес/Холмы';
        } else {
            return 'Различные';
        }
    }
    
    /**
     * Извлечение боевых параметров
     */
    private function extractCombatStats($monster_details) {
        $stats = [];
        
        if (isset($monster_details['armor_class'])) {
            $stats['Класс доспеха'] = $monster_details['armor_class'][0]['value'] ?? '10';
        }
        
        if (isset($monster_details['hit_points'])) {
            $stats['Хиты'] = $monster_details['hit_points']['average'] ?? '10';
        }
        
        if (isset($monster_details['speed'])) {
            $speed_parts = [];
            foreach ($monster_details['speed'] as $type => $value) {
                $speed_parts[] = "$type: $value";
            }
            $stats['Скорость'] = implode(', ', $speed_parts);
        }
        
        return $stats;
    }
    
    /**
     * Извлечение действий
     */
    private function extractActions($monster_details) {
        $actions = [];
        
        if (isset($monster_details['actions'])) {
            foreach ($monster_details['actions'] as $action) {
                $actions[] = [
                    'name' => $action['name'],
                    'description' => $action['desc'] ?? 'Нет описания'
                ];
            }
        }
        
        return $actions;
    }
    
    /**
     * Генерация тактики с помощью AI
     */
    private function generateTactics($enemy) {
        $prompt = "Опиши тактику боя для {$enemy['name']} (CR {$enemy['challenge_rating']}, {$enemy['type']}). " .
                 "Включи основные действия, предпочтения в бою, слабости и как лучше использовать этого противника. " .
                 "Ответ должен быть кратким (2-3 предложения) и практичным для мастера D&D.";
        
        try {
            $response = $this->callDeepSeek($prompt);
            return $response ?: 'Тактика не определена';
        } catch (Exception $e) {
            return 'Тактика не определена';
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
                ['role' => 'system', 'content' => 'Ты помощник мастера D&D. Давай краткие и практичные советы.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 200,
            'temperature' => 0.7
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
    
    /**
     * Выполнение HTTP запроса
     */
    private function makeRequest($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_USERAGENT, 'DnD-Copilot/1.0');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("CURL Error for $url: $error");
            return null;
        }
        
        if ($http_code === 200 && $response) {
            $decoded = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            } else {
                error_log("JSON decode error for $url: " . json_last_error_msg());
                return null;
            }
        }
        
        error_log("HTTP Error for $url: $http_code");
        return null;
    }
}

// Обработка запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $generator = new EnemyGenerator();
    $result = $generator->generateEnemies($_POST);
    
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Метод не поддерживается'
    ]);
}
?>
