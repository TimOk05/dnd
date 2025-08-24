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
        // Получаем список монстров из API
        $monsters = $this->getMonstersList();
        
        if (!$monsters) {
            throw new Exception('Не удалось получить список монстров');
        }
        
        // Фильтруем монстров по CR и типу
        $filtered_monsters = $this->filterMonsters($monsters, $cr_range, $enemy_type, $environment);
        
        if (empty($filtered_monsters)) {
            // Если не найдено подходящих, берем случайного монстра
            $filtered_monsters = $monsters;
        }
        
        // Выбираем случайного монстра
        $monster = $filtered_monsters[array_rand($filtered_monsters)];
        
        // Получаем детальную информацию о монстре
        $monster_details = $this->getMonsterDetails($monster['index']);
        
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
            ['index' => 'goblin', 'name' => 'Goblin'],
            ['index' => 'orc', 'name' => 'Orc'],
            ['index' => 'wolf', 'name' => 'Wolf'],
            ['index' => 'bandit', 'name' => 'Bandit'],
            ['index' => 'cultist', 'name' => 'Cultist'],
            ['index' => 'dragon', 'name' => 'Dragon'],
            ['index' => 'troll', 'name' => 'Troll'],
            ['index' => 'ogre', 'name' => 'Ogre'],
            ['index' => 'skeleton', 'name' => 'Skeleton'],
            ['index' => 'zombie', 'name' => 'Zombie']
        ];
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
            
            // Проверяем тип
            if ($enemy_type && strpos(strtolower($details['type']), strtolower($enemy_type)) === false) {
                continue;
            }
            
            // Проверяем среду (если указана)
            if ($environment && !$this->checkEnvironment($details, $environment)) {
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
                'size' => 'Small',
                'alignment' => 'Neutral Evil',
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
                'size' => 'Medium',
                'alignment' => 'Chaotic Evil',
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
                'size' => 'Medium',
                'alignment' => 'Unaligned',
                'desc' => 'Дикий волк с серой шерстью и острыми клыками.',
                'stats' => ['str' => 12, 'dex' => 15, 'con' => 12, 'int' => 3, 'wis' => 12, 'cha' => 6],
                'armor_class' => [['value' => 13]],
                'hit_points' => ['average' => 11],
                'speed' => ['walk' => '40'],
                'actions' => [
                    ['name' => 'Укус', 'desc' => 'Атака оружием ближнего боя: +4 к попаданию, досягаемость 5 футов, одна цель. Попадание: 7 (2d4 + 2) колющего урона.']
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
