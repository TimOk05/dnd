<?php
/**
 * Fallback данные для работы без внешних API
 */

class FallbackData {
    
    /**
     * Fallback данные для рас
     */
    public static function getRaces() {
        return [
            'human' => [
                'name' => 'Человек',
                'ability_bonuses' => ['str' => 1, 'dex' => 1, 'con' => 1, 'int' => 1, 'wis' => 1, 'cha' => 1],
                'traits' => ['Универсальность', 'Дополнительное владение навыком'],
                'speed' => 30
            ],
            'elf' => [
                'name' => 'Эльф',
                'ability_bonuses' => ['dex' => 2],
                'traits' => ['Темное зрение', 'Келебрас', 'Иммунитет к усыплению', 'Транс'],
                'speed' => 30
            ],
            'dwarf' => [
                'name' => 'Дварф',
                'ability_bonuses' => ['con' => 2],
                'traits' => ['Темное зрение', 'Устойчивость к яду', 'Владение боевым топором'],
                'speed' => 25
            ],
            'halfling' => [
                'name' => 'Полурослик',
                'ability_bonuses' => ['dex' => 2],
                'traits' => ['Удача', 'Смелость', 'Ловкость полурослика'],
                'speed' => 25
            ],
            'orc' => [
                'name' => 'Орк',
                'ability_bonuses' => ['str' => 2, 'con' => 1],
                'traits' => ['Темное зрение', 'Агрессивность', 'Мощное телосложение'],
                'speed' => 30
            ],
            'tiefling' => [
                'name' => 'Тифлинг',
                'ability_bonuses' => ['int' => 1, 'cha' => 2],
                'traits' => ['Темное зрение', 'Устойчивость к огню', 'Адское наследие'],
                'speed' => 30
            ],
            'dragonborn' => [
                'name' => 'Драконорожденный',
                'ability_bonuses' => ['str' => 2, 'cha' => 1],
                'traits' => ['Дыхание дракона', 'Устойчивость к урону', 'Драконье наследие'],
                'speed' => 30
            ],
            'gnome' => [
                'name' => 'Гном',
                'ability_bonuses' => ['int' => 2],
                'traits' => ['Темное зрение', 'Гномья хитрость', 'Естественная иллюзия'],
                'speed' => 25
            ],
            'half-elf' => [
                'name' => 'Полуэльф',
                'ability_bonuses' => ['cha' => 2, 'dex' => 1, 'int' => 1],
                'traits' => ['Темное зрение', 'Универсальность', 'Эльфийское наследие'],
                'speed' => 30
            ],
            'half-orc' => [
                'name' => 'Полуорк',
                'ability_bonuses' => ['str' => 2, 'con' => 1],
                'traits' => ['Темное зрение', 'Угрожающий', 'Устойчивость к урону'],
                'speed' => 30
            ]
        ];
    }
    
    /**
     * Fallback данные для классов
     */
    public static function getClasses() {
        return [
            'fighter' => [
                'name' => 'Воин',
                'hit_die' => 10,
                'primary_ability' => 'str',
                'saving_throw_proficiencies' => ['str', 'con'],
                'armor_proficiencies' => ['light', 'medium', 'heavy', 'shields'],
                'weapon_proficiencies' => ['simple', 'martial']
            ],
            'wizard' => [
                'name' => 'Волшебник',
                'hit_die' => 6,
                'primary_ability' => 'int',
                'saving_throw_proficiencies' => ['int', 'wis'],
                'armor_proficiencies' => [],
                'weapon_proficiencies' => ['daggers', 'quarterstaffs', 'light_crossbows']
            ],
            'rogue' => [
                'name' => 'Плут',
                'hit_die' => 8,
                'primary_ability' => 'dex',
                'saving_throw_proficiencies' => ['dex', 'int'],
                'armor_proficiencies' => ['light'],
                'weapon_proficiencies' => ['simple', 'hand_crossbows', 'longswords', 'rapiers', 'shortswords']
            ],
            'cleric' => [
                'name' => 'Жрец',
                'hit_die' => 8,
                'primary_ability' => 'wis',
                'saving_throw_proficiencies' => ['wis', 'cha'],
                'armor_proficiencies' => ['light', 'medium', 'shields'],
                'weapon_proficiencies' => ['simple']
            ],
            'ranger' => [
                'name' => 'Следопыт',
                'hit_die' => 10,
                'primary_ability' => 'dex',
                'saving_throw_proficiencies' => ['str', 'dex'],
                'armor_proficiencies' => ['light', 'medium', 'shields'],
                'weapon_proficiencies' => ['simple', 'martial']
            ],
            'barbarian' => [
                'name' => 'Варвар',
                'hit_die' => 12,
                'primary_ability' => 'str',
                'saving_throw_proficiencies' => ['str', 'con'],
                'armor_proficiencies' => ['light', 'medium', 'shields'],
                'weapon_proficiencies' => ['simple', 'martial']
            ],
            'bard' => [
                'name' => 'Бард',
                'hit_die' => 8,
                'primary_ability' => 'cha',
                'saving_throw_proficiencies' => ['dex', 'cha'],
                'armor_proficiencies' => ['light'],
                'weapon_proficiencies' => ['simple', 'hand_crossbows', 'longswords', 'rapiers', 'shortswords']
            ],
            'druid' => [
                'name' => 'Друид',
                'hit_die' => 8,
                'primary_ability' => 'wis',
                'saving_throw_proficiencies' => ['int', 'wis'],
                'armor_proficiencies' => ['light', 'medium', 'shields'],
                'weapon_proficiencies' => ['clubs', 'daggers', 'javelins', 'maces', 'quarterstaffs', 'scimitars', 'sickles', 'slings', 'spears']
            ],
            'monk' => [
                'name' => 'Монах',
                'hit_die' => 8,
                'primary_ability' => 'dex',
                'saving_throw_proficiencies' => ['str', 'dex'],
                'armor_proficiencies' => [],
                'weapon_proficiencies' => ['simple', 'shortswords']
            ],
            'paladin' => [
                'name' => 'Паладин',
                'hit_die' => 10,
                'primary_ability' => 'str',
                'saving_throw_proficiencies' => ['wis', 'cha'],
                'armor_proficiencies' => ['light', 'medium', 'heavy', 'shields'],
                'weapon_proficiencies' => ['simple', 'martial']
            ],
            'sorcerer' => [
                'name' => 'Сорсерер',
                'hit_die' => 6,
                'primary_ability' => 'cha',
                'saving_throw_proficiencies' => ['con', 'cha'],
                'armor_proficiencies' => [],
                'weapon_proficiencies' => ['daggers', 'quarterstaffs', 'light_crossbows']
            ],
            'warlock' => [
                'name' => 'Колдун',
                'hit_die' => 8,
                'primary_ability' => 'cha',
                'saving_throw_proficiencies' => ['wis', 'cha'],
                'armor_proficiencies' => ['light'],
                'weapon_proficiencies' => ['simple']
            ]
        ];
    }
    
    /**
     * Fallback данные для монстров
     */
    public static function getMonsters() {
        return [
            [
                'index' => 'goblin',
                'name' => 'Гоблин',
                'type' => 'humanoid',
                'size' => 'small',
                'alignment' => 'neutral evil',
                'challenge_rating' => '1/4',
                'hit_points' => 7,
                'armor_class' => 15,
                'speed' => '30 ft',
                'abilities' => [
                    'str' => 8, 'dex' => 14, 'con' => 10, 'int' => 10, 'wis' => 8, 'cha' => 8
                ],
                'actions' => [
                    'scimitar' => 'Рубящая атака: +4 к попаданию, 5 (1d6+2) рубящего урона',
                    'shortbow' => 'Дальняя атака: +4 к попаданию, 5 (1d6+2) колющего урона'
                ]
            ],
            [
                'index' => 'orc',
                'name' => 'Орк',
                'type' => 'humanoid',
                'size' => 'medium',
                'alignment' => 'chaotic evil',
                'challenge_rating' => '1/2',
                'hit_points' => 15,
                'armor_class' => 13,
                'speed' => '30 ft',
                'abilities' => [
                    'str' => 16, 'dex' => 12, 'con' => 16, 'int' => 7, 'wis' => 11, 'cha' => 10
                ],
                'actions' => [
                    'greataxe' => 'Рубящая атака: +5 к попаданию, 9 (1d12+3) рубящего урона',
                    'javelin' => 'Дальняя атака: +5 к попаданию, 6 (1d6+3) колющего урона'
                ]
            ],
            [
                'index' => 'troll',
                'name' => 'Тролль',
                'type' => 'giant',
                'size' => 'large',
                'alignment' => 'chaotic evil',
                'challenge_rating' => '5',
                'hit_points' => 84,
                'armor_class' => 15,
                'speed' => '30 ft',
                'abilities' => [
                    'str' => 18, 'dex' => 13, 'con' => 20, 'int' => 7, 'wis' => 9, 'cha' => 7
                ],
                'actions' => [
                    'multiattack' => 'Тролль совершает три атаки: одну когтями и две кулаками',
                    'claws' => 'Рубящая атака: +7 к попаданию, 11 (2d6+4) рубящего урона',
                    'bite' => 'Колющая атака: +7 к попаданию, 11 (2d6+4) колющего урона'
                ],
                'special_abilities' => [
                    'regeneration' => 'Тролль восстанавливает 10 хитов в начале своего хода'
                ]
            ]
        ];
    }
    
    /**
     * Fallback данные для оружия
     */
    public static function getWeapons() {
        return [
            'sword' => ['name' => 'Меч', 'damage' => '1d8', 'type' => 'slashing'],
            'axe' => ['name' => 'Топор', 'damage' => '1d8', 'type' => 'slashing'],
            'bow' => ['name' => 'Лук', 'damage' => '1d8', 'type' => 'piercing'],
            'dagger' => ['name' => 'Кинжал', 'damage' => '1d4', 'type' => 'piercing'],
            'staff' => ['name' => 'Посох', 'damage' => '1d6', 'type' => 'bludgeoning'],
            'mace' => ['name' => 'Булава', 'damage' => '1d6', 'type' => 'bludgeoning']
        ];
    }
    
    /**
     * Fallback данные для заклинаний
     */
    public static function getSpells() {
        return [
            'fireball' => [
                'name' => 'Огненный шар',
                'level' => 3,
                'school' => 'evocation',
                'casting_time' => '1 действие',
                'range' => '150 футов',
                'components' => ['V', 'S', 'M'],
                'duration' => 'Мгновенно',
                'description' => 'Яркий светящийся шар огня летит к выбранной точке в пределах дистанции и взрывается'
            ],
            'magic_missile' => [
                'name' => 'Волшебная стрела',
                'level' => 1,
                'school' => 'evocation',
                'casting_time' => '1 действие',
                'range' => '120 футов',
                'components' => ['V', 'S'],
                'duration' => 'Мгновенно',
                'description' => 'Вы создаете три светящихся дротика магической силы'
            ],
            'cure_wounds' => [
                'name' => 'Лечение ран',
                'level' => 1,
                'school' => 'evocation',
                'casting_time' => '1 действие',
                'range' => 'Касание',
                'components' => ['V', 'S'],
                'duration' => 'Мгновенно',
                'description' => 'Существо, которого вы касаетесь, восстанавливает количество хитов'
            ]
        ];
    }
}
?>
