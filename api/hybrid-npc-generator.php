<?php
/**
 * Гибридная система генерации NPC
 * Комбинирует D&D API для технических параметров и DeepSeek для творческих аспектов
 */

require_once 'dnd-api-working.php';

class HybridNpcGenerator {
    private $dndApi;
    private $deepseekApi;
    
    public function __construct() {
        $this->dndApi = new DndApiWorking();
        $this->deepseekApi = new DeepSeekAPI();
    }
    
    /**
     * Генерация NPC с использованием гибридного подхода
     */
    public function generateHybridNPC($params = []) {
        $defaultParams = [
            'race' => 'human',
            'class' => 'fighter',
            'level' => 1,
            'alignment' => 'neutral',
            'background' => 'soldier',
            'use_ai_enhancement' => true
        ];
        
        $params = array_merge($defaultParams, $params);
        
        try {
            // 1. Получаем технические параметры от D&D API
            $technicalData = $this->dndApi->generateNPC($params);
            
            if (!$technicalData) {
                throw new Exception('Не удалось получить технические данные от D&D API');
            }
            
            // 2. Если включено AI-улучшение, отправляем данные в DeepSeek
            if ($params['use_ai_enhancement']) {
                $enhancedData = $this->enhanceWithAI($technicalData, $params);
                return $enhancedData;
            }
            
            return $technicalData;
            
        } catch (Exception $e) {
            error_log("Hybrid NPC Generation Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Улучшение NPC с помощью DeepSeek AI
     */
    private function enhanceWithAI($technicalData, $params) {
        // Создаем промпт для AI
        $prompt = $this->createAIPrompt($technicalData, $params);
        
        // Отправляем запрос к DeepSeek
        $aiResponse = $this->deepseekApi->generateContent($prompt);
        
        if ($aiResponse) {
            // Парсим ответ AI и объединяем с техническими данными
            return $this->mergeAIData($technicalData, $aiResponse);
        }
        
        // Если AI недоступен, возвращаем только технические данные
        return $technicalData;
    }
    
    /**
     * Создание промпта для AI
     */
    private function createAIPrompt($technicalData, $params) {
        $race = $technicalData['race'];
        $class = $technicalData['class'];
        $level = $technicalData['level'];
        $alignment = $technicalData['alignment'];
        $name = $technicalData['name'];
        
        $prompt = "Создай краткое описание NPC для D&D 5e на русском языке.

Персонаж: $name - $race $class $level уровня, мировоззрение: $alignment

Технические параметры:
" . implode("\n", $technicalData['technical_params']) . "

Пожалуйста, создай:
1. **Краткое описание** (2-3 предложения) - характер и стиль персонажа
2. **Внешность** (2-3 предложения) - детальное описание внешнего вида
3. **Краткую историю** (3-4 предложения) - ключевые моменты прошлого
4. **Особенности характера** (2-3 предложения) - уникальные черты

Учитывай:
- Мировоззрение персонажа должно отражаться в описании
- Раса и класс должны влиять на внешность и характер
- Уровень должен отражаться в опыте и уверенности
- Описание должно быть живым и интересным для игрового мастера

Формат ответа:
**Описание:** [текст]
**Внешность:** [текст]
**История:** [текст]
**Особенности:** [текст]";

        return $prompt;
    }
    
    /**
     * Объединение данных AI с техническими данными
     */
    private function mergeAIData($technicalData, $aiResponse) {
        // Парсим ответ AI
        $aiData = $this->parseAIResponse($aiResponse);
        
        // Объединяем данные
        $enhancedData = $technicalData;
        
        if (isset($aiData['description'])) {
            $enhancedData['description'] = $aiData['description'];
        }
        
        if (isset($aiData['appearance'])) {
            $enhancedData['appearance'] = $aiData['appearance'];
        }
        
        if (isset($aiData['history'])) {
            $enhancedData['history'] = $aiData['history'];
        }
        
        if (isset($aiData['personality'])) {
            $enhancedData['personality'] = $aiData['personality'];
        }
        
        $enhancedData['ai_enhanced'] = true;
        $enhancedData['api_source'] = 'D&D 5e API + DeepSeek AI';
        
        return $enhancedData;
    }
    
    /**
     * Парсинг ответа AI
     */
    private function parseAIResponse($response) {
        $data = [];
        
        // Ищем блоки по заголовкам
        $patterns = [
            'description' => '/\*\*Описание:\*\*\s*(.+?)(?=\*\*|$)/s',
            'appearance' => '/\*\*Внешность:\*\*\s*(.+?)(?=\*\*|$)/s',
            'history' => '/\*\*История:\*\*\s*(.+?)(?=\*\*|$)/s',
            'personality' => '/\*\*Особенности:\*\*\s*(.+?)(?=\*\*|$)/s'
        ];
        
        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $response, $matches)) {
                $data[$key] = trim($matches[1]);
            }
        }
        
        return $data;
    }
}

/**
 * Класс для работы с DeepSeek API
 */
class DeepSeekAPI {
    private $apiKey;
    private $apiUrl = 'https://api.deepseek.com/v1/chat/completions';
    
    public function __construct() {
        // Получаем API ключ из конфигурации
        $this->apiKey = $this->getApiKey();
    }
    
    /**
     * Получение API ключа
     */
    private function getApiKey() {
        // Проверяем наличие ключа в конфигурации
        if (file_exists('../config.php')) {
            include '../config.php';
            return defined('DEEPSEEK_API_KEY') ? DEEPSEEK_API_KEY : null;
        }
        return null;
    }
    
    /**
     * Генерация контента через DeepSeek API
     */
    public function generateContent($prompt) {
        if (!$this->apiKey) {
            error_log("DeepSeek API key not configured");
            return null;
        }
        
        $data = [
            'model' => 'deepseek-chat',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 1000,
            'temperature' => 0.7
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("DeepSeek API Error: $error");
            return null;
        }
        
        if ($httpCode !== 200) {
            error_log("DeepSeek API HTTP Error: $httpCode");
            return null;
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['choices'][0]['message']['content'])) {
            return $result['choices'][0]['message']['content'];
        }
        
        return null;
    }
}
?>
