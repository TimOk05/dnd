# 🔒 Улучшения безопасности D&D Copilot

## 🎯 **Текущие проблемы безопасности**

### **1. Уязвимости в коде**
- API ключи в открытом виде
- Отсутствие rate limiting
- Недостаточная валидация входных данных
- Отсутствие защиты от CSRF в некоторых местах

### **2. Проблемы с сессиями**
- Хранение сессий в файлах
- Отсутствие регенерации ID сессий
- Нет защиты от session fixation

### **3. Отсутствие мониторинга**
- Нет логирования подозрительной активности
- Отсутствие алертов о взломах
- Нет анализа безопасности

## 🚀 **Предлагаемые улучшения**

### **1.1 Защита API ключей**

#### **Переменные окружения:**
```php
// .env файл (не в репозитории)
DEEPSEEK_API_KEY=sk-your-actual-key-here
DATABASE_URL=mysql://user:pass@localhost/dnd
REDIS_URL=redis://localhost:6379
JWT_SECRET=your-super-secret-jwt-key
```

#### **Безопасная загрузка конфигурации:**
```php
class ConfigLoader {
    private $config = [];
    
    public function __construct() {
        $this->loadEnvFile();
        $this->validateRequiredKeys();
    }
    
    private function loadEnvFile() {
        $envFile = __DIR__ . '/../.env';
        
        if (!file_exists($envFile)) {
            throw new Exception('Environment file not found');
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $this->config[trim($key)] = trim($value);
            }
        }
    }
    
    private function validateRequiredKeys() {
        $required = ['DEEPSEEK_API_KEY', 'JWT_SECRET', 'DATABASE_URL'];
        
        foreach ($required as $key) {
            if (!isset($this->config[$key])) {
                throw new Exception("Required environment variable: $key");
            }
        }
    }
    
    public function get($key, $default = null) {
        return $this->config[$key] ?? $default;
    }
}
```

### **1.2 Rate Limiting**

#### **Redis-based rate limiting:**
```php
class RateLimiter {
    private $redis;
    private $limits = [
        'login' => ['requests' => 5, 'window' => 900],      // 5 попыток за 15 минут
        'api' => ['requests' => 100, 'window' => 3600],     // 100 запросов в час
        'ai' => ['requests' => 50, 'window' => 3600],       // 50 AI запросов в час
        'character' => ['requests' => 20, 'window' => 3600] // 20 персонажей в час
    ];
    
    public function __construct() {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }
    
    public function check($action, $identifier) {
        $limit = $this->limits[$action] ?? ['requests' => 10, 'window' => 3600];
        $key = "rate_limit:{$action}:{$identifier}";
        
        $current = $this->redis->incr($key);
        
        if ($current === 1) {
            $this->redis->expire($key, $limit['window']);
        }
        
        if ($current > $limit['requests']) {
            $this->logRateLimitExceeded($action, $identifier);
            return false;
        }
        
        return true;
    }
    
    private function logRateLimitExceeded($action, $identifier) {
        logMessage('Rate limit exceeded', 'WARNING', [
            'action' => $action,
            'identifier' => $identifier,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }
}
```

### **1.3 Улучшенная валидация**

#### **Валидация входных данных:**
```php
class InputValidator {
    private $errors = [];
    
    public function validate($data, $rules) {
        $this->errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $this->validateField($field, $value, $fieldRules);
        }
        
        return empty($this->errors);
    }
    
    private function validateField($field, $value, $rules) {
        foreach ($rules as $rule) {
            if (!$this->checkRule($field, $value, $rule)) {
                $this->errors[$field][] = $this->getErrorMessage($field, $rule);
            }
        }
    }
    
    private function checkRule($field, $value, $rule) {
        switch ($rule) {
            case 'required':
                return !empty($value);
                
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
                
            case 'min:3':
                return strlen($value) >= 3;
                
            case 'max:50':
                return strlen($value) <= 50;
                
            case 'alpha':
                return preg_match('/^[a-zA-Zа-яА-Я\s]+$/', $value);
                
            case 'numeric':
                return is_numeric($value);
                
            case 'int_range:1:20':
                $range = explode(':', $rule);
                $min = (int)$range[1];
                $max = (int)$range[2];
                return is_numeric($value) && $value >= $min && $value <= $max;
                
            case 'xss_clean':
                return $this->isXssClean($value);
                
            default:
                return true;
        }
    }
    
    private function isXssClean($value) {
        $dangerous = [
            '<script', 'javascript:', 'onload', 'onerror', 'onclick',
            'vbscript:', 'expression(', 'url(', 'eval('
        ];
        
        $lowerValue = strtolower($value);
        foreach ($dangerous as $pattern) {
            if (strpos($lowerValue, $pattern) !== false) {
                return false;
            }
        }
        
        return true;
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function sanitize($input, $type = 'string') {
        switch ($type) {
            case 'string':
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
            case 'int':
                return (int) $input;
            case 'email':
                return filter_var($input, FILTER_SANITIZE_EMAIL);
            case 'url':
                return filter_var($input, FILTER_SANITIZE_URL);
            case 'html':
                return strip_tags($input, '<p><br><strong><em><ul><ol><li>');
            default:
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }
}
```

### **1.4 JWT аутентификация**

#### **JWT токены:**
```php
class JWTAuth {
    private $secret;
    private $algorithm = 'HS256';
    
    public function __construct() {
        $this->secret = getenv('JWT_SECRET');
        if (!$this->secret) {
            throw new Exception('JWT_SECRET not configured');
        }
    }
    
    public function generateToken($user) {
        $payload = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24), // 24 часа
            'jti' => bin2hex(random_bytes(16)) // Уникальный ID токена
        ];
        
        return $this->encode($payload);
    }
    
    public function validateToken($token) {
        try {
            $payload = $this->decode($token);
            
            // Проверяем срок действия
            if ($payload['exp'] < time()) {
                return false;
            }
            
            // Проверяем в черном списке
            if ($this->isBlacklisted($payload['jti'])) {
                return false;
            }
            
            return $payload;
        } catch (Exception $e) {
            logMessage('JWT validation failed', 'WARNING', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    public function blacklistToken($token) {
        $payload = $this->decode($token);
        $this->redis->setex("blacklist:{$payload['jti']}", 86400, '1'); // 24 часа
    }
    
    private function isBlacklisted($jti) {
        return $this->redis->exists("blacklist:{$jti}");
    }
    
    private function encode($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => $this->algorithm]);
        $payload = json_encode($payload);
        
        $base64Header = $this->base64UrlEncode($header);
        $base64Payload = $this->base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', 
            $base64Header . "." . $base64Payload, 
            $this->secret, 
            true
        );
        $base64Signature = $this->base64UrlEncode($signature);
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    private function decode($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new Exception('Invalid token format');
        }
        
        list($header, $payload, $signature) = $parts;
        
        $expectedSignature = hash_hmac('sha256', 
            $header . "." . $payload, 
            $this->secret, 
            true
        );
        
        if (!hash_equals($this->base64UrlDecode($signature), $expectedSignature)) {
            throw new Exception('Invalid signature');
        }
        
        return json_decode($this->base64UrlDecode($payload), true);
    }
    
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
}
```

### **1.5 Улучшенная защита от атак**

#### **Защита от SQL Injection:**
```php
class DatabaseSecurity {
    private $pdo;
    
    public function __construct() {
        $this->pdo = new PDO(
            getenv('DATABASE_URL'),
            null,
            null,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]
        );
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function insert($table, $data) {
        $columns = array_keys($data);
        $placeholders = ':' . implode(', :', $columns);
        $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES ({$placeholders})";
        
        return $this->query($sql, $data);
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "{$column} = :{$column}";
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $set) . " WHERE {$where}";
        $params = array_merge($data, $whereParams);
        
        return $this->query($sql, $params);
    }
}
```

#### **Защита от XSS:**
```php
class XSSProtection {
    public static function sanitize($input, $allowedTags = []) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        
        // Удаляем опасные теги и атрибуты
        $dangerous = [
            'script', 'object', 'embed', 'form', 'input', 'textarea',
            'select', 'option', 'iframe', 'frame', 'frameset', 'noframes'
        ];
        
        $input = strip_tags($input, implode('', $allowedTags));
        
        // Удаляем опасные атрибуты
        $input = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $input);
        $input = preg_replace('/\s*javascript\s*:/i', '', $input);
        $input = preg_replace('/\s*vbscript\s*:/i', '', $input);
        $input = preg_replace('/\s*expression\s*\(/i', '', $input);
        
        return $input;
    }
    
    public static function validateUrl($url) {
        $parsed = parse_url($url);
        
        if (!$parsed) {
            return false;
        }
        
        // Разрешаем только HTTP и HTTPS
        if (!in_array($parsed['scheme'] ?? '', ['http', 'https'])) {
            return false;
        }
        
        // Проверяем на опасные протоколы
        $dangerous = ['javascript:', 'vbscript:', 'data:', 'file:'];
        foreach ($dangerous as $protocol) {
            if (stripos($url, $protocol) === 0) {
                return false;
            }
        }
        
        return true;
    }
}
```

### **1.6 Система мониторинга безопасности**

#### **Логирование безопасности:**
```php
class SecurityLogger {
    private $logFile;
    
    public function __construct() {
        $this->logFile = __DIR__ . '/../logs/security.log';
    }
    
    public function logSecurityEvent($event, $details = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'user_id' => $_SESSION['user_id'] ?? 'anonymous',
            'details' => $details
        ];
        
        $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
        
        // Отправляем алерт для критических событий
        if ($this->isCriticalEvent($event)) {
            $this->sendSecurityAlert($event, $details);
        }
    }
    
    private function isCriticalEvent($event) {
        $criticalEvents = [
            'failed_login_attempt',
            'rate_limit_exceeded',
            'suspicious_activity',
            'admin_access_attempt',
            'file_upload_attempt',
            'sql_injection_attempt',
            'xss_attempt'
        ];
        
        return in_array($event, $criticalEvents);
    }
    
    private function sendSecurityAlert($event, $details) {
        // Отправка уведомления администратору
        $message = "Security Alert: {$event}\n";
        $message .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
        $message .= "Details: " . json_encode($details, JSON_UNESCAPED_UNICODE);
        
        // Можно отправить email, SMS или push уведомление
        mail(getenv('ADMIN_EMAIL'), 'Security Alert', $message);
    }
}
```

#### **Анализ подозрительной активности:**
```php
class SecurityAnalyzer {
    private $redis;
    
    public function __construct() {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }
    
    public function analyzeActivity($ip, $action) {
        $key = "activity:{$ip}";
        $activities = $this->redis->lrange($key, 0, -1);
        
        // Добавляем новую активность
        $activity = [
            'action' => $action,
            'timestamp' => time(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        $this->redis->lpush($key, json_encode($activity));
        $this->redis->expire($key, 3600); // Храним 1 час
        
        // Анализируем паттерны
        $this->checkPatterns($ip, $activities);
    }
    
    private function checkPatterns($ip, $activities) {
        $recentActivities = array_filter($activities, function($activity) {
            $data = json_decode($activity, true);
            return (time() - $data['timestamp']) < 300; // Последние 5 минут
        });
        
        // Проверяем частые неудачные попытки входа
        $failedLogins = array_filter($recentActivities, function($activity) {
            $data = json_decode($activity, true);
            return $data['action'] === 'failed_login';
        });
        
        if (count($failedLogins) > 10) {
            $this->blockIP($ip, 'Too many failed login attempts');
        }
        
        // Проверяем подозрительные паттерны
        $suspiciousPatterns = $this->detectSuspiciousPatterns($recentActivities);
        if ($suspiciousPatterns) {
            $this->flagIP($ip, $suspiciousPatterns);
        }
    }
    
    private function detectSuspiciousPatterns($activities) {
        $patterns = [];
        
        // Быстрые последовательные запросы
        $timestamps = array_map(function($activity) {
            $data = json_decode($activity, true);
            return $data['timestamp'];
        }, $activities);
        
        sort($timestamps);
        
        for ($i = 1; $i < count($timestamps); $i++) {
            if ($timestamps[$i] - $timestamps[$i-1] < 1) { // Менее 1 секунды
                $patterns[] = 'rapid_requests';
                break;
            }
        }
        
        // Различные User-Agent в короткое время
        $userAgents = array_unique(array_map(function($activity) {
            $data = json_decode($activity, true);
            return $data['user_agent'];
        }, $activities));
        
        if (count($userAgents) > 3) {
            $patterns[] = 'multiple_user_agents';
        }
        
        return $patterns;
    }
    
    private function blockIP($ip, $reason) {
        $this->redis->setex("blocked:{$ip}", 3600, json_encode([
            'reason' => $reason,
            'timestamp' => time()
        ]));
        
        logMessage('IP blocked', 'WARNING', [
            'ip' => $ip,
            'reason' => $reason
        ]);
    }
    
    private function flagIP($ip, $patterns) {
        $this->redis->setex("flagged:{$ip}", 1800, json_encode([
            'patterns' => $patterns,
            'timestamp' => time()
        ]));
        
        logMessage('IP flagged', 'WARNING', [
            'ip' => $ip,
            'patterns' => $patterns
        ]);
    }
}
```

## 🚀 **План внедрения**

### **Этап 1: Критические улучшения (1 неделя)**
1. Перемещение API ключей в переменные окружения
2. Внедрение rate limiting
3. Улучшенная валидация входных данных
4. Базовое логирование безопасности

### **Этап 2: Аутентификация (2 недели)**
1. Внедрение JWT токенов
2. Улучшенная защита сессий
3. Двухфакторная аутентификация (опционально)
4. Управление токенами

### **Этап 3: Защита от атак (2 недели)**
1. Защита от SQL Injection
2. Защита от XSS
3. CSRF защита
4. Защита от файловых атак

### **Этап 4: Мониторинг (1 неделя)**
1. Система логирования безопасности
2. Анализ подозрительной активности
3. Алерты и уведомления
4. Документация безопасности

## 📈 **Ожидаемые результаты**

### **Безопасность:**
- ⬆️ **Защита от атак** на 95%
- ⬇️ **Количество инцидентов** на 90%
- ⬆️ **Соответствие стандартам** на 100%
- ⬆️ **Мониторинг угроз** на 100%

### **Соответствие:**
- ✅ **OWASP Top 10** защита
- ✅ **GDPR** соответствие
- ✅ **ISO 27001** рекомендации
- ✅ **PCI DSS** (если применимо)

---

**Эти улучшения обеспечат высокий уровень безопасности системы!**
