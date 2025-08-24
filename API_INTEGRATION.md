# Интеграция с внешними D&D API

## Обзор

Данная система решает проблемы с генерацией NPC путем интеграции с внешними D&D API вместо полной зависимости от AI-сервисов. Это обеспечивает более надежную, быструю и структурированную генерацию персонажей.

## Проблемы текущей системы

### 1. Нестабильность AI-генерации
- **Проблема**: AI может возвращать неструктурированные или неполные данные
- **Решение**: Использование структурированных API с гарантированным форматом ответа

### 2. Сложная обработка ответов
- **Проблема**: Функция `formatNpcBlocks` пытается "угадать" структуру ответа AI
- **Решение**: Работа с четко определенными JSON-структурами от API

### 3. Зависимость от внешних AI-сервисов
- **Проблема**: Медленная работа и возможные сбои AI-сервисов
- **Решение**: Кэширование данных и fallback на локальную генерацию

## Поддерживаемые API

### 1. D&D 5e API (dnd5eapi.co)
**URL**: `https://www.dnd5eapi.co/api`

**Возможности**:
- Список всех классов, рас, оружия, заклинаний
- Детальная информация о каждом элементе
- Бесплатный и открытый API
- Стабильная работа

**Примеры запросов**:
```php
// Получение списка классов
GET /api/classes

// Информация о конкретном классе
GET /api/classes/fighter

// Список рас
GET /api/races

// Информация о расе
GET /api/races/human
```

### 2. Open5e API
**URL**: `https://open5e.com/api`

**Возможности**:
- Альтернативный источник данных
- Дополнительные материалы
- Резервный API при недоступности основного

### 3. Custom NPC Generator API
**URL**: `https://api.example.com/npc` (замените на реальный)

**Возможности**:
- Специализированная генерация NPC
- Расширенные параметры
- Интеграция с AI для творческих элементов

## Архитектура системы

### Файловая структура
```
api/
├── dnd-api.php          # Основной класс для работы с API
├── generate-npc.php     # Endpoint для генерации NPC
└── format-npc.php       # Функции форматирования

test-npc-api.php         # Тестовая страница
```

### Класс DndApiManager

```php
class DndApiManager {
    // Основные методы
    public function getClasses()
    public function getRaces()
    public function getWeapons()
    public function getSpells()
    public function getMonsters()
    public function generateNPC($params)
    
    // Вспомогательные методы
    private function makeRequest($url, $method, $data)
    private function generateFromDnd5eAPI($params)
    private function generateFromExternalAPI($params)
}
```

## Использование

### 1. Базовая генерация NPC

```php
require_once 'api/dnd-api.php';

$dndApi = new DndApiManager();

$npc = $dndApi->generateNPC([
    'race' => 'human',
    'class' => 'fighter',
    'level' => 1,
    'alignment' => 'neutral',
    'background' => 'soldier'
]);
```

### 2. Получение данных из API

```php
// Получение списка классов
$classes = $dndApi->getClasses();

// Информация о классе
$fighterInfo = $dndApi->getClassInfo('fighter');

// Список рас
$races = $dndApi->getRaces();
```

### 3. Генерация через AJAX

```javascript
fetch('api/generate-npc.php', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        // Отображение NPC
        displayNPC(data.npc);
    }
});
```

## Преимущества новой системы

### 1. Надежность
- ✅ Гарантированная структура данных
- ✅ Отсутствие "угадывания" формата
- ✅ Стабильная работа API

### 2. Производительность
- ✅ Быстрые ответы от API
- ✅ Кэширование данных
- ✅ Отсутствие зависимости от AI

### 3. Точность
- ✅ Соответствие правилам D&D 5e
- ✅ Корректные характеристики
- ✅ Правильные технические параметры

### 4. Гибкость
- ✅ Поддержка всех рас и классов
- ✅ Настраиваемые параметры
- ✅ Возможность расширения

## Настройка и развертывание

### 1. Требования
- PHP 7.4+
- cURL extension
- JSON extension
- Доступ к внешним API

### 2. Конфигурация
```php
// В api/dnd-api.php
private $dnd5e_api_url = 'https://www.dnd5eapi.co/api';
private $open5e_api_url = 'https://open5e.com/api';
private $custom_npc_api_url = 'https://your-api.com/npc';
```

### 3. Тестирование
```bash
# Проверка доступности API
curl https://www.dnd5eapi.co/api/classes

# Тест генерации NPC
php -f test-npc-api.php
```

## Обработка ошибок

### 1. Недоступность API
```php
try {
    $npc = $dndApi->generateNPC($params);
} catch (Exception $e) {
    // Fallback на локальную генерацию
    $npc = generateLocalNPC($params);
}
```

### 2. Неверные параметры
```php
// Валидация входных данных
$validRaces = ['human', 'elf', 'dwarf', ...];
if (!in_array($race, $validRaces)) {
    throw new Exception('Invalid race');
}
```

### 3. Логирование ошибок
```php
error_log("DnD API Error: " . $e->getMessage());
```

## Расширение функциональности

### 1. Добавление новых API
```php
public function generateFromNewAPI($params) {
    $url = 'https://new-api.com/npc';
    return $this->makeRequest($url, 'POST', $params);
}
```

### 2. Кэширование данных
```php
private function getCachedData($key) {
    $cacheFile = "cache/{$key}.json";
    if (file_exists($cacheFile) && time() - filemtime($cacheFile) < 3600) {
        return json_decode(file_get_contents($cacheFile), true);
    }
    return null;
}
```

### 3. Интеграция с AI для творческих элементов
```php
public function generateCreativeDescription($npcData) {
    // Использование AI только для описаний
    $prompt = "Опиши NPC: {$npcData['race']} {$npcData['class']}";
    return $this->callAI($prompt);
}
```

## Мониторинг и аналитика

### 1. Логирование запросов
```php
private function logRequest($api, $params, $success) {
    $log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'api' => $api,
        'params' => $params,
        'success' => $success
    ];
    file_put_contents('logs/api_requests.log', json_encode($log) . "\n", FILE_APPEND);
}
```

### 2. Статистика использования
- Количество запросов к каждому API
- Время ответа
- Процент успешных запросов
- Популярные комбинации параметров

## Безопасность

### 1. Валидация входных данных
- Проверка типов данных
- Ограничение значений
- Санитизация строк

### 2. Ограничение запросов
```php
private function checkRateLimit($ip) {
    $requests = $this->getRequestsCount($ip);
    if ($requests > 100) { // 100 запросов в час
        throw new Exception('Rate limit exceeded');
    }
}
```

### 3. HTTPS для всех API
- Обязательное использование HTTPS
- Проверка SSL сертификатов
- Безопасная передача данных

## Заключение

Интеграция с внешними D&D API решает основные проблемы текущей системы генерации NPC:

1. **Надежность**: Структурированные данные вместо "угадывания"
2. **Скорость**: Быстрые ответы от специализированных API
3. **Точность**: Соответствие официальным правилам D&D 5e
4. **Масштабируемость**: Легкое добавление новых API и функций

Система готова к использованию и может быть легко расширена для поддержки дополнительных функций и API.
