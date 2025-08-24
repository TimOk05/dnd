<?php
/**
 * Рабочая интеграция с D&D API
 * Использует только реальные внешние API
 */

class DndApiWorking {
    private $dnd5e_api_url = 'https://www.dnd5eapi.co/api';
    private $open5e_api_url = 'https://open5e.com/api';
    
    /**
     * Генерация NPC с использованием реальных API
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
        
        try {
            // Получаем данные из D&D 5e API
            $raceInfo = $this->getRaceInfo($params['race']);
            $classInfo = $this->getClassInfo($params['class']);
            
            if (!$raceInfo || !$classInfo) {
                throw new Exception('Не удалось получить данные из API');
            }
            
            // Генерируем характеристики на основе правил D&D 5e
            $abilities = $this->generateAbilitiesFromRules($raceInfo, $classInfo, $params['level']);
            
            // Генерируем имя
            $name = $this->generateName($params['race']);
            
            // Генерируем описание
            $description = $this->generateDescription($params);
            
            // Генерируем технические параметры
            $technicalParams = $this->generateTechnicalParams($params, $abilities, $classInfo);
            
            return [
                'name' => $name,
                'race' => $raceInfo['name'],
                'class' => $classInfo['name'],
                'level' => $params['level'],
                'alignment' => $params['alignment'],
                'background' => $params['background'],
                'abilities' => $abilities,
                'description' => $description,
                'technical_params' => $technicalParams,
                'api_source' => 'D&D 5e API'
            ];
            
        } catch (Exception $e) {
            error_log("NPC Generation Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Получение информации о расе из API
     */
    public function getRaceInfo($raceName) {
        $url = $this->dnd5e_api_url . '/races/' . strtolower($raceName);
        $response = $this->makeRequest($url);
        
        if (!$response) {
            // Fallback на альтернативный API
            $url = $this->open5e_api_url . '/races/' . strtolower($raceName) . '/';
            $response = $this->makeRequest($url);
        }
        
        return $response;
    }
    
    /**
     * Получение информации о классе из API
     */
    public function getClassInfo($className) {
        $url = $this->dnd5e_api_url . '/classes/' . strtolower($className);
        $response = $this->makeRequest($url);
        
        if (!$response) {
            // Fallback на альтернативный API
            $url = $this->open5e_api_url . '/classes/' . strtolower($className) . '/';
            $response = $this->makeRequest($url);
        }
        
        return $response;
    }
    
    /**
     * Получение списка классов
     */
    public function getClasses() {
        $url = $this->dnd5e_api_url . '/classes';
        $response = $this->makeRequest($url);
        
        if ($response && isset($response['results'])) {
            return array_map(function($class) {
                return [
                    'name' => $class['name'],
                    'index' => $class['index']
                ];
            }, $response['results']);
        }
        
        return [];
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
                    'index' => $race['index']
                ];
            }, $response['results']);
        }
        
        return [];
    }
    
    /**
     * Генерация характеристик по правилам D&D 5e
     */
    private function generateAbilitiesFromRules($raceInfo, $classInfo, $level) {
        // Базовые характеристики (4d6 drop lowest)
        $abilities = [
            'strength' => $this->roll4d6DropLowest(),
            'dexterity' => $this->roll4d6DropLowest(),
            'constitution' => $this->roll4d6DropLowest(),
            'intelligence' => $this->roll4d6DropLowest(),
            'wisdom' => $this->roll4d6DropLowest(),
            'charisma' => $this->roll4d6DropLowest()
        ];
        
        // Применяем бонусы расы
        if (isset($raceInfo['ability_bonuses'])) {
            foreach ($raceInfo['ability_bonuses'] as $bonus) {
                $ability = strtolower($bonus['ability_score']['name']);
                $abilities[$ability] += $bonus['bonus'];
            }
        }
        
        // Ограничиваем значения
        foreach ($abilities as $ability => $score) {
            $abilities[$ability] = max(3, min(20, $score));
        }
        
        return $abilities;
    }
    
    /**
     * Бросок 4d6 с отбрасыванием наименьшего
     */
    private function roll4d6DropLowest() {
        $rolls = [];
        for ($i = 0; $i < 4; $i++) {
            $rolls[] = rand(1, 6);
        }
        sort($rolls);
        return array_sum(array_slice($rolls, 1)); // Отбрасываем наименьший
    }
    
    /**
     * Генерация имени
     */
    private function generateName($race) {
        $names = [
            'human' => ['Алексей', 'Мария', 'Дмитрий', 'Анна', 'Сергей', 'Елена', 'Иван', 'Ольга'],
            'elf' => ['Леголас', 'Галадриэль', 'Элронд', 'Арвен', 'Трандуил', 'Келеборн', 'Элранир', 'Сильвана'],
            'dwarf' => ['Гимли', 'Торин', 'Балин', 'Двалин', 'Бомбур', 'Глоин', 'Дорин', 'Тора'],
            'halfling' => ['Бильбо', 'Фродо', 'Сэм', 'Пиппин', 'Мерри', 'Розмари', 'Перси', 'Дейзи'],
            'orc' => ['Гром', 'Железный Кулак', 'Кровавый Топор', 'Темный Дух', 'Волчья Грива', 'Грашнак', 'Ургот'],
            'tiefling' => ['Азраэль', 'Малик', 'Зара', 'Кайн', 'Люцифер', 'Асмодей', 'Бельфегор'],
            'dragonborn' => ['Дракс', 'Тиамат', 'Бахамут', 'Алдуин', 'Смауг', 'Фафнир', 'Нидхёгг'],
            'gnome' => ['Гномлик', 'Пип', 'Нимбл', 'Спаркл', 'Тинкер', 'Гизмо', 'Гаджет'],
            'half-elf' => ['Элрион', 'Арагорн', 'Элвира', 'Халфдан', 'Элронд', 'Арвен', 'Леголас'],
            'half-orc' => ['Гром', 'Железный Кулак', 'Кровавый Топор', 'Темный Дух', 'Волчья Грива', 'Грашнак']
        ];
        
        $raceNames = $names[$race] ?? $names['human'];
        return $raceNames[array_rand($raceNames)];
    }
    
    /**
     * Генерация описания
     */
    private function generateDescription($params) {
        $descriptions = [
            'fighter' => 'Опытный воин, закаленный в боях и сражениях. Мастер владения оружием и тактики.',
            'wizard' => 'Мудрый маг, изучающий древние тайны магии. Хранитель знаний и заклинаний.',
            'rogue' => 'Ловкий плут, мастер скрытности и точных ударов. Тень в ночи.',
            'cleric' => 'Благочестивый жрец, служащий своему божеству. Исцелитель и защитник веры.',
            'ranger' => 'Следопыт, знающий дикие земли как свои пять пальцев. Охотник и выживальщик.',
            'barbarian' => 'Дикий варвар, чья ярость в бою не знает границ. Неукротимый воин.',
            'bard' => 'Обаятельный бард, чьи песни и истории покоряют сердца. Вдохновитель и обманщик.',
            'druid' => 'Друид, связанный с природой и её силами. Хранитель равновесия мира.',
            'monk' => 'Дисциплинированный монах, владеющий боевыми искусствами. Мастер тела и духа.',
            'paladin' => 'Благородный паладин, защитник справедливости и добра. Святой воин.',
            'sorcerer' => 'Сорсерер, чья магия рождается из внутренней силы. Владелец врожденной магии.',
            'warlock' => 'Колдун, заключивший договор с могущественным покровителем. Тёмный маг.'
        ];
        
        return $descriptions[$params['class']] ?? 'Загадочный персонаж с интересным прошлым.';
    }
    
    /**
     * Генерация технических параметров
     */
    private function generateTechnicalParams($params, $abilities, $classInfo) {
        $level = $params['level'];
        $class = $params['class'];
        
        // Базовые хиты
        $hitDie = $classInfo['hit_die'] ?? 8;
        $baseHP = $hitDie;
        $conMod = floor(($abilities['constitution'] - 10) / 2);
        $hp = $baseHP + ($conMod * $level);
        
        // Класс доспеха (базовый)
        $ac = 10 + floor(($abilities['dexterity'] - 10) / 2);
        
        // Оружие
        $weapon = $this->getClassWeapon($class);
        
        // Урон
        $damage = $this->getWeaponDamage($weapon);
        
        // Модификаторы характеристик
        $abilityScores = [];
        foreach ($abilities as $ability => $score) {
            $modifier = floor(($score - 10) / 2);
            $modStr = $modifier >= 0 ? "+$modifier" : "$modifier";
            $abilityScores[] = ucfirst($ability) . ": $score ($modStr)";
        }
        
        return [
            'Хиты: ' . $hp,
            'Класс доспеха: ' . $ac,
            'Оружие: ' . $weapon,
            'Урон: ' . $damage,
            'Характеристики: ' . implode(', ', $abilityScores),
            'Уровень: ' . $level,
            'Кость хитов: d' . $hitDie
        ];
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
     * Выполнение HTTP запроса
     */
    private function makeRequest($url, $method = 'GET', $data = null) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
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
            error_log("DnD API HTTP Error: $httpCode for URL: $url");
            return null;
        }
        
        return json_decode($response, true);
    }
}
?>
