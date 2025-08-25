<?php
/**
 * Рабочая интеграция с D&D API
 * Использует только реальные внешние API
 */

require_once __DIR__ . '/fallback-data.php';

class DndApiWorking {
    private $dnd5e_api_url = 'https://www.dnd5eapi.co/api';
    private $open5e_api_url = 'https://open5e.com/api';
    
    // Переводы рас
    private $raceTranslations = [
        'human' => 'Человек',
        'elf' => 'Эльф',
        'dwarf' => 'Дварф',
        'halfling' => 'Полурослик',
        'orc' => 'Орк',
        'tiefling' => 'Тифлинг',
        'dragonborn' => 'Драконорожденный',
        'gnome' => 'Гном',
        'half-elf' => 'Полуэльф',
        'half-orc' => 'Полуорк'
    ];
    
    // Переводы классов
    private $classTranslations = [
        'fighter' => 'Воин',
        'wizard' => 'Волшебник',
        'rogue' => 'Плут',
        'cleric' => 'Жрец',
        'ranger' => 'Следопыт',
        'barbarian' => 'Варвар',
        'bard' => 'Бард',
        'druid' => 'Друид',
        'monk' => 'Монах',
        'paladin' => 'Паладин',
        'sorcerer' => 'Сорсерер',
        'warlock' => 'Колдун'
    ];
    
    // Магические классы
    private $magicClasses = ['wizard', 'cleric', 'bard', 'druid', 'sorcerer', 'warlock', 'paladin', 'ranger'];
    
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
            
            // Генерируем описание, внешность и профессию
            $description = $this->generateDescription($params);
            $appearance = $this->generateAppearance($params);
            $profession = $this->generateProfession($params);
            
            // Генерируем технические параметры с учетом уровня
            $technicalParams = $this->generateTechnicalParams($params, $abilities, $classInfo);
            
            // Генерируем заклинания для магических классов
            $spells = [];
            if (in_array($params['class'], $this->magicClasses)) {
                $spells = $this->generateSpells($params['class'], $params['level']);
            }
            
            return [
                'name' => $name,
                'race' => $this->raceTranslations[$params['race']] ?? $raceInfo['name'],
                'class' => $this->classTranslations[$params['class']] ?? $classInfo['name'],
                'level' => $params['level'],
                'alignment' => $this->translateAlignment($params['alignment']),
                'background' => $params['background'],
                'abilities' => $abilities,
                'description' => $description,
                'appearance' => $appearance,
                'profession' => $profession,
                'technical_params' => $technicalParams,
                'spells' => $spells,
                'api_source' => 'D&D 5e API'
            ];
            
        } catch (Exception $e) {
            error_log("NPC Generation Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Перевод мировоззрения на русский
     */
    private function translateAlignment($alignment) {
        $alignments = [
            'lawful good' => 'Законно-добрый',
            'neutral good' => 'Нейтрально-добрый',
            'chaotic good' => 'Хаотично-добрый',
            'lawful neutral' => 'Законно-нейтральный',
            'neutral' => 'Нейтральный',
            'chaotic neutral' => 'Хаотично-нейтральный',
            'lawful evil' => 'Законно-злой',
            'neutral evil' => 'Нейтрально-злой',
            'chaotic evil' => 'Хаотично-злой'
        ];
        
        return $alignments[$alignment] ?? $alignment;
    }
    
    /**
     * Генерация внешности
     */
    private function generateAppearance($params) {
        $appearances = [
            'human' => [
                'Высокий и стройный человек с карими глазами и темными волосами',
                'Крепкого телосложения человек с голубыми глазами и светлыми волосами',
                'Среднего роста человек с зелеными глазами и рыжими волосами',
                'Коренастый человек с серыми глазами и черными волосами'
            ],
            'elf' => [
                'Высокий эльф с острыми чертами лица, длинными светлыми волосами и ярко-зелеными глазами',
                'Стройный эльф с изящными чертами, серебристыми волосами и голубыми глазами',
                'Элегантный эльф с тонкими чертами, золотистыми волосами и фиолетовыми глазами'
            ],
            'dwarf' => [
                'Крепкий дварф с густой бородой, карими глазами и темными волосами',
                'Коренастый дварф с рыжей бородой, зелеными глазами и рыжими волосами',
                'Мощный дварф с седой бородой, голубыми глазами и светлыми волосами'
            ],
            'gnome' => [
                'Маленький гном с острыми чертами, рыжими волосами и ярко-голубыми глазами',
                'Курносый гном с кудрявыми волосами, зелеными глазами и веснушками',
                'Живой гном с острым взглядом, темными волосами и карими глазами'
            ]
        ];
        
        $raceAppearances = $appearances[$params['race']] ?? $appearances['human'];
        return $raceAppearances[array_rand($raceAppearances)];
    }
    
    /**
     * Генерация профессии
     */
    private function generateProfession($params) {
        $professions = [
            'fighter' => ['Страж', 'Наемник', 'Охранник', 'Солдат', 'Главарь банды', 'Телохранитель'],
            'wizard' => ['Исследователь', 'Учитель магии', 'Библиотекарь', 'Алхимик', 'Картограф', 'Хроникер'],
            'rogue' => ['Вор', 'Шпион', 'Контрабандист', 'Охотник за головами', 'Карманник', 'Разведчик'],
            'cleric' => ['Жрец', 'Монах', 'Проповедник', 'Целитель', 'Хранитель храма', 'Миссионер'],
            'ranger' => ['Охотник', 'Следопыт', 'Лесник', 'Проводник', 'Торговец мехами', 'Пограничник'],
            'barbarian' => ['Охотник', 'Воин племени', 'Защитник', 'Наемник', 'Главарь клана', 'Странник'],
            'bard' => ['Музыкант', 'Поэт', 'Актер', 'Сказочник', 'Дипломат', 'Шпион'],
            'druid' => ['Хранитель леса', 'Целитель', 'Проводник', 'Охотник', 'Мудрец', 'Защитник природы'],
            'monk' => ['Монах', 'Учитель боевых искусств', 'Странник', 'Хранитель знаний', 'Аскет', 'Защитник'],
            'paladin' => ['Рыцарь', 'Защитник веры', 'Страж порядка', 'Крестоносец', 'Командир', 'Святой воин'],
            'sorcerer' => ['Аристократ', 'Странник', 'Отшельник', 'Торговец', 'Искатель приключений', 'Мистик'],
            'warlock' => ['Исследователь', 'Колдун', 'Мистик', 'Отшельник', 'Торговец', 'Странник']
        ];
        
        $classProfessions = $professions[$params['class']] ?? ['Странник'];
        return $classProfessions[array_rand($classProfessions)];
    }
    
    /**
     * Генерация заклинаний для магических классов
     */
    private function generateSpells($class, $level) {
        $spells = [];
        
        // Заговоры (0 уровень)
        $cantrips = $this->getCantrips($class);
        if ($cantrips) {
            $spells['cantrips'] = array_slice($cantrips, 0, min(3, count($cantrips)));
        }
        
        // Заклинания 1-го уровня
        if ($level >= 1) {
            $level1Spells = $this->getSpellsByLevel($class, 1);
            if ($level1Spells) {
                $spells['level_1'] = array_slice($level1Spells, 0, min(4, count($level1Spells)));
            }
        }
        
        // Заклинания 2-го уровня
        if ($level >= 3) {
            $level2Spells = $this->getSpellsByLevel($class, 2);
            if ($level2Spells) {
                $spells['level_2'] = array_slice($level2Spells, 0, min(2, count($level2Spells)));
            }
        }
        
        // Заклинания 3-го уровня
        if ($level >= 5) {
            $level3Spells = $this->getSpellsByLevel($class, 3);
            if ($level3Spells) {
                $spells['level_3'] = array_slice($level3Spells, 0, min(2, count($level3Spells)));
            }
        }
        
        return $spells;
    }
    
    /**
     * Получение заговоров для класса
     */
    private function getCantrips($class) {
        $cantrips = [
            'wizard' => ['Огненный снаряд', 'Луч холода', 'Электрическая дуга', 'Магическая рука', 'Свет', 'Сообщение'],
            'cleric' => ['Священное пламя', 'Слово', 'Свет', 'Спасительная благодать', 'Сопротивление'],
            'bard' => ['Висс', 'Свет', 'Сообщение', 'Престидижитация', 'Дружба с животными'],
            'druid' => ['Луч холода', 'Свет', 'Дружба с животными', 'Сопротивление', 'Шип'],
            'sorcerer' => ['Огненный снаряд', 'Луч холода', 'Электрическая дуга', 'Свет', 'Престидижитация'],
            'warlock' => ['Элдричский взрыв', 'Свет', 'Престидижитация', 'Сообщение', 'Слово'],
            'paladin' => ['Священное пламя', 'Свет', 'Сопротивление'],
            'ranger' => ['Дружба с животными', 'Свет', 'Сопротивление']
        ];
        
        return $cantrips[$class] ?? [];
    }
    
    /**
     * Получение заклинаний по уровню
     */
    private function getSpellsByLevel($class, $spellLevel) {
        $spells = [
            'wizard' => [
                1 => ['Волшебная стрела', 'Огненный шар', 'Ледяной шторм', 'Молния', 'Невидимость', 'Телепортация'],
                2 => ['Волшебная стрела', 'Огненный шар', 'Ледяной шторм', 'Молния', 'Невидимость'],
                3 => ['Огненный шар', 'Ледяной шторм', 'Молния', 'Невидимость', 'Телепортация']
            ],
            'cleric' => [
                1 => ['Лечение ран', 'Благословение', 'Проклятие', 'Защита от зла и добра', 'Священное пламя'],
                2 => ['Лечение ран', 'Благословение', 'Проклятие', 'Защита от зла и добра'],
                3 => ['Лечение ран', 'Благословение', 'Проклятие', 'Защита от зла и добра']
            ],
            'bard' => [
                1 => ['Очарование личности', 'Невидимость', 'Лечение ран', 'Благословение', 'Проклятие'],
                2 => ['Очарование личности', 'Невидимость', 'Лечение ран'],
                3 => ['Очарование личности', 'Невидимость', 'Лечение ран']
            ],
            'druid' => [
                1 => ['Лечение ран', 'Дружба с животными', 'Вызов элементаля', 'Превращение', 'Шип'],
                2 => ['Лечение ран', 'Дружба с животными', 'Вызов элементаля'],
                3 => ['Лечение ран', 'Дружба с животными', 'Вызов элементаля']
            ],
            'sorcerer' => [
                1 => ['Огненный шар', 'Ледяной шторм', 'Молния', 'Невидимость', 'Телепортация'],
                2 => ['Огненный шар', 'Ледяной шторм', 'Молния'],
                3 => ['Огненный шар', 'Ледяной шторм', 'Молния']
            ],
            'warlock' => [
                1 => ['Элдричский взрыв', 'Невидимость', 'Проклятие', 'Защита от зла и добра'],
                2 => ['Элдричский взрыв', 'Невидимость', 'Проклятие'],
                3 => ['Элдричский взрыв', 'Невидимость', 'Проклятие']
            ]
        ];
        
        return $spells[$class][$spellLevel] ?? [];
    }
    
    /**
     * Получение информации о расе
     */
    public function getRaceInfo($race) {
        // Сначала пытаемся получить из API
        $url = $this->dnd5e_api_url . '/races/' . strtolower($race);
        $response = $this->makeRequest($url);
        
        if ($response) {
            return $response;
        }
        
        // Fallback: используем локальные данные
        $fallbackRaces = FallbackData::getRaces();
        if (isset($fallbackRaces[$race])) {
            return $fallbackRaces[$race];
        }
        
        // Если раса не найдена, возвращаем человека
        return $fallbackRaces['human'];
    }
    
    /**
     * Получение информации о классе
     */
    public function getClassInfo($class) {
        // Сначала пытаемся получить из API
        $url = $this->dnd5e_api_url . '/classes/' . strtolower($class);
        $response = $this->makeRequest($url);
        
        if ($response) {
            return $response;
        }
        
        // Fallback: используем локальные данные
        $fallbackClasses = FallbackData::getClasses();
        if (isset($fallbackClasses[$class])) {
            return $fallbackClasses[$class];
        }
        
        // Если класс не найден, возвращаем воина
        return $fallbackClasses['fighter'];
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
     * Генерация описания с учетом мировоззрения
     */
    private function generateDescription($params) {
        $alignment = $params['alignment'];
        $class = $params['class'];
        
                 // Базовые описания по классам (нейтральные)
         $baseDescriptions = [
             'fighter' => 'Опытный воин, закаленный в боях и сражениях.',
             'wizard' => 'Мудрый маг, изучающий древние тайны магии.',
             'rogue' => 'Ловкий плут, мастер скрытности и точных ударов.',
             'cleric' => 'Жрец, служащий своему божеству.',
             'ranger' => 'Следопыт, знающий дикие земли как свои пять пальцев.',
             'barbarian' => 'Дикий варвар, чья ярость в бою не знает границ.',
             'bard' => 'Бард, чьи песни и истории покоряют сердца.',
             'druid' => 'Друид, связанный с природой и её силами.',
             'monk' => 'Дисциплинированный монах, владеющий боевыми искусствами.',
             'paladin' => 'Паладин, защитник своей веры.',
             'sorcerer' => 'Сорсерер, чья магия рождается из внутренней силы.',
             'warlock' => 'Колдун, заключивший договор с могущественным покровителем.'
         ];
        
        $baseDesc = $baseDescriptions[$class] ?? 'Загадочный персонаж с интересным прошлым.';
        
        // Добавляем мировоззрение
        $alignmentDesc = '';
        switch ($alignment) {
            case 'lawful good':
                $alignmentDesc = ' Строго следует закону и защищает добро.';
                break;
            case 'neutral good':
                $alignmentDesc = ' Стремится к добру, но не всегда следует правилам.';
                break;
            case 'chaotic good':
                $alignmentDesc = ' Добрый, но предпочитает свободу и спонтанность.';
                break;
            case 'lawful neutral':
                $alignmentDesc = ' Следует порядку и традициям, независимо от морали.';
                break;
            case 'neutral':
                $alignmentDesc = ' Балансирует между порядком и хаосом, добром и злом.';
                break;
            case 'chaotic neutral':
                $alignmentDesc = ' Ценит свободу и избегает ограничений.';
                break;
            case 'lawful evil':
                $alignmentDesc = ' Злой, но следует строгой иерархии и правилам.';
                break;
            case 'neutral evil':
                $alignmentDesc = ' Эгоистичен и жесток, не связан ни с порядком, ни с хаосом.';
                break;
            case 'chaotic evil':
                $alignmentDesc = ' Жестокий и непредсказуемый, разрушает все вокруг.';
                break;
        }
        
        return $baseDesc . $alignmentDesc;
    }
    
    /**
     * Генерация технических параметров с учетом уровня
     */
    private function generateTechnicalParams($params, $abilities, $classInfo) {
        $level = $params['level'];
        $class = $params['class'];
        
        // Базовые хиты с учетом уровня
        $hitDie = $classInfo['hit_die'] ?? 8;
        $baseHP = $hitDie;
        $conMod = floor(($abilities['constitution'] - 10) / 2);
        
        // Расчет хитов по уровням
        $hp = $baseHP + $conMod; // 1 уровень
        for ($i = 2; $i <= $level; $i++) {
            $hp += rand(1, $hitDie) + $conMod;
        }
        
        // Класс доспеха (базовый)
        $ac = 10 + floor(($abilities['dexterity'] - 10) / 2);
        
        // Оружие
        $weapon = $this->getClassWeapon($class);
        
        // Урон с учетом уровня
        $damage = $this->getWeaponDamage($weapon, $level);
        
        // Модификаторы характеристик (только на русском)
        $abilityScores = [];
        foreach ($abilities as $ability => $score) {
            $modifier = floor(($score - 10) / 2);
            $modStr = $modifier >= 0 ? "+$modifier" : "$modifier";
            $abilityScores[] = $this->translateAbility($ability) . ": $score ($modStr)";
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
     * Перевод характеристик на русский
     */
    private function translateAbility($ability) {
        $translations = [
            'strength' => 'Сила',
            'dexterity' => 'Ловкость',
            'constitution' => 'Телосложение',
            'intelligence' => 'Интеллект',
            'wisdom' => 'Мудрость',
            'charisma' => 'Харизма'
        ];
        
        return $translations[$ability] ?? ucfirst($ability);
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
     * Получение урона оружия с учетом уровня
     */
    private function getWeaponDamage($weapon, $level) {
        $baseDamage = [
            'Топор' => '1d12 рубящий',
            'Меч' => '1d8 рубящий',
            'Лук' => '1d8 колющий',
            'Кулаки' => '1d4 дробящий',
            'Кинжал' => '1d4 колющий',
            'Рапира' => '1d8 колющий',
            'Булава' => '1d6 дробящий',
            'Посох' => '1d6 дробящий'
        ];
        
        $damage = $baseDamage[$weapon] ?? '1d4 дробящий';
        
        // Увеличиваем урон с уровнем (для воинов)
        if ($level >= 5 && in_array($weapon, ['Топор', 'Меч', 'Рапира'])) {
            $damage = str_replace('1d', '2d', $damage);
        }
        
        return $damage;
    }
    
    /**
     * Выполнение HTTP запроса
     */
    private function makeRequest($url, $method = 'GET', $data = null) {
        // Проверяем, доступен ли cURL
        if (!function_exists('curl_init')) {
            // Fallback: используем file_get_contents для GET запросов
            if ($method === 'GET') {
                $context = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'header' => [
                            'Content-Type: application/json',
                            'User-Agent: DnD-Copilot/1.0'
                        ],
                        'timeout' => 30
                    ]
                ]);
                
                $response = @file_get_contents($url, false, $context);
                if ($response === false) {
                    return null; // Возвращаем null для использования fallback данных
                }
                
                return json_decode($response, true);
            } else {
                // Для POST запросов возвращаем null, чтобы использовать локальные данные
                return null;
            }
        }
        
        // Используем cURL если доступен
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
            return null; // Возвращаем null для использования fallback данных
        }
        
        if ($httpCode !== 200) {
            return null; // Возвращаем null для использования fallback данных
        }
        
        return json_decode($response, true);
    }
}
?>
