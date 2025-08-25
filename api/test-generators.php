<?php
/**
 * Тестирование генераторов персонажей и противников
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/error-handler.php';
require_once __DIR__ . '/dnd-api-working.php';
require_once __DIR__ . '/generate-enemies.php';
require_once __DIR__ . '/generate-characters.php';

class GeneratorTester {
    
    /**
     * Тестирование генерации NPC
     */
    public function testNPCGeneration() {
        echo "<h2>Тестирование генерации NPC</h2>\n";
        
        $testCases = [
            ['race' => 'human', 'class' => 'fighter', 'level' => 1, 'alignment' => 'neutral'],
            ['race' => 'elf', 'class' => 'wizard', 'level' => 5, 'alignment' => 'lawful good'],
            ['race' => 'dwarf', 'class' => 'cleric', 'level' => 10, 'alignment' => 'neutral good']
        ];
        
        foreach ($testCases as $i => $params) {
            echo "<h3>Тест " . ($i + 1) . ": " . $params['race'] . " " . $params['class'] . " уровня " . $params['level'] . "</h3>\n";
            
            try {
                $dndApi = new DndApiWorking();
                $result = $dndApi->generateNPC($params);
                
                if ($result) {
                    echo "<div style='color: green;'>✓ Успешно сгенерирован NPC</div>\n";
                    echo "<pre>" . json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "</pre>\n";
                } else {
                    echo "<div style='color: red;'>✗ Ошибка: пустой результат</div>\n";
                }
            } catch (Exception $e) {
                echo "<div style='color: red;'>✗ Ошибка: " . $e->getMessage() . "</div>\n";
            }
        }
    }
    
    /**
     * Тестирование генерации противников
     */
    public function testEnemyGeneration() {
        echo "<h2>Тестирование генерации противников</h2>\n";
        
        $testCases = [
            ['threat_level' => 'easy', 'count' => 1, 'enemy_type' => 'beast'],
            ['threat_level' => 'medium', 'count' => 2, 'enemy_type' => 'humanoid'],
            ['threat_level' => 'hard', 'count' => 1, 'enemy_type' => 'dragon']
        ];
        
        foreach ($testCases as $i => $params) {
            echo "<h3>Тест " . ($i + 1) . ": " . $params['threat_level'] . " угроза, " . $params['count'] . " противник(ов)</h3>\n";
            
            try {
                $generator = new EnemyGenerator();
                $result = $generator->generateEnemies($params);
                
                if ($result['success']) {
                    echo "<div style='color: green;'>✓ Успешно сгенерированы противники</div>\n";
                    echo "<pre>" . json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "</pre>\n";
                } else {
                    echo "<div style='color: red;'>✗ Ошибка: " . $result['error'] . "</div>\n";
                }
            } catch (Exception $e) {
                echo "<div style='color: red;'>✗ Ошибка: " . $e->getMessage() . "</div>\n";
            }
        }
    }
    
    /**
     * Тестирование генерации персонажей
     */
    public function testCharacterGeneration() {
        echo "<h2>Тестирование генерации персонажей</h2>\n";
        
        $testCases = [
            ['race' => 'human', 'class' => 'fighter', 'level' => 1, 'alignment' => 'neutral'],
            ['race' => 'elf', 'class' => 'wizard', 'level' => 5, 'alignment' => 'lawful good'],
            ['race' => 'dwarf', 'class' => 'cleric', 'level' => 10, 'alignment' => 'neutral good']
        ];
        
        foreach ($testCases as $i => $params) {
            echo "<h3>Тест " . ($i + 1) . ": " . $params['race'] . " " . $params['class'] . " уровня " . $params['level'] . "</h3>\n";
            
            try {
                $generator = new CharacterGenerator();
                $result = $generator->generateCharacter($params);
                
                if ($result['success']) {
                    echo "<div style='color: green;'>✓ Успешно сгенерирован персонаж</div>\n";
                    echo "<pre>" . json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "</pre>\n";
                } else {
                    echo "<div style='color: red;'>✗ Ошибка: " . $result['error'] . "</div>\n";
                }
            } catch (Exception $e) {
                echo "<div style='color: red;'>✗ Ошибка: " . $e->getMessage() . "</div>\n";
            }
        }
    }
    
    /**
     * Тестирование доступности API
     */
    public function testApiAvailability() {
        echo "<h2>Тестирование доступности API</h2>\n";
        
        $apis = [
            'D&D 5e API' => 'https://www.dnd5eapi.co/api/classes',
            'Open5e API' => 'https://open5e.com/api/classes/',
        ];
        
        foreach ($apis as $name => $url) {
            $available = ErrorHandler::checkApiAvailability($url);
            if ($available) {
                echo "<div style='color: green;'>✓ $name доступен</div>\n";
            } else {
                echo "<div style='color: red;'>✗ $name недоступен</div>\n";
            }
        }
    }
    
    /**
     * Запуск всех тестов
     */
    public function runAllTests() {
        echo "<html><head><title>Тестирование генераторов D&D</title></head><body>\n";
        echo "<h1>Тестирование генераторов D&D</h1>\n";
        
        $this->testApiAvailability();
        $this->testNPCGeneration();
        $this->testEnemyGeneration();
        $this->testCharacterGeneration();
        
        echo "</body></html>\n";
    }
}

// Запускаем тесты если файл вызван напрямую
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $tester = new GeneratorTester();
    $tester->runAllTests();
}
?>
