<?php

/**
 * Система Rate Limiting для защиты от злоупотреблений
 * Использует файловое хранилище (можно заменить на Redis)
 */
class RateLimiter {
    private $storagePath;
    private $limits;
    
    public function __construct($storagePath = null) {
        $this->storagePath = $storagePath ?: __DIR__ . '/../cache/rate_limits/';
        $this->limits = [
            'login' => ['requests' => 5, 'window' => 900],      // 5 попыток за 15 минут
            'api' => ['requests' => 100, 'window' => 3600],     // 100 запросов в час
            'ai' => ['requests' => 50, 'window' => 3600],       // 50 AI запросов в час
            'character' => ['requests' => 20, 'window' => 3600], // 20 персонажей в час
            'default' => ['requests' => 10, 'window' => 3600]   // 10 запросов в час по умолчанию
        ];
        
        // Создаем директорию если не существует
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }
    
    /**
     * Проверяет лимит для действия
     */
    public function check($action, $identifier) {
        $limit = $this->limits[$action] ?? $this->limits['default'];
        $key = $this->getKey($action, $identifier);
        
        $current = $this->increment($key);
        
        if ($current === 1) {
            $this->setExpiry($key, $limit['window']);
        }
        
        if ($current > $limit['requests']) {
            $this->logRateLimitExceeded($action, $identifier, $current, $limit);
            return false;
        }
        
        return true;
    }
    
    /**
     * Получает количество оставшихся запросов
     */
    public function getRemaining($action, $identifier) {
        $limit = $this->limits[$action] ?? $this->limits['default'];
        $key = $this->getKey($action, $identifier);
        
        $current = $this->getCurrent($key);
        $remaining = max(0, $limit['requests'] - $current);
        
        return $remaining;
    }
    
    /**
     * Получает время до сброса лимита
     */
    public function getResetTime($action, $identifier) {
        $key = $this->getKey($action, $identifier);
        $file = $this->storagePath . $key . '.expiry';
        
        if (file_exists($file)) {
            $expiry = (int) file_get_contents($file);
            return max(0, $expiry - time());
        }
        
        return 0;
    }
    
    /**
     * Сбрасывает лимит для идентификатора
     */
    public function reset($action, $identifier) {
        $key = $this->getKey($action, $identifier);
        $this->delete($key);
    }
    
    /**
     * Генерирует ключ для хранения
     */
    private function getKey($action, $identifier) {
        return md5($action . ':' . $identifier);
    }
    
    /**
     * Увеличивает счетчик
     */
    private function increment($key) {
        $file = $this->storagePath . $key . '.count';
        
        if (file_exists($file)) {
            $current = (int) file_get_contents($file);
            $current++;
        } else {
            $current = 1;
        }
        
        file_put_contents($file, $current, LOCK_EX);
        return $current;
    }
    
    /**
     * Получает текущее значение счетчика
     */
    private function getCurrent($key) {
        $file = $this->storagePath . $key . '.count';
        
        if (file_exists($file)) {
            return (int) file_get_contents($file);
        }
        
        return 0;
    }
    
    /**
     * Устанавливает время истечения
     */
    private function setExpiry($key, $window) {
        $file = $this->storagePath . $key . '.expiry';
        $expiry = time() + $window;
        file_put_contents($file, $expiry, LOCK_EX);
    }
    
    /**
     * Удаляет данные лимита
     */
    private function delete($key) {
        $countFile = $this->storagePath . $key . '.count';
        $expiryFile = $this->storagePath . $key . '.expiry';
        
        if (file_exists($countFile)) {
            unlink($countFile);
        }
        
        if (file_exists($expiryFile)) {
            unlink($expiryFile);
        }
    }
    
    /**
     * Логирует превышение лимита
     */
    private function logRateLimitExceeded($action, $identifier, $current, $limit) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => $action,
            'identifier' => $identifier,
            'current_requests' => $current,
            'limit' => $limit['requests'],
            'window' => $limit['window'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        $logFile = __DIR__ . '/../logs/rate_limit.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Очищает устаревшие записи
     */
    public function cleanup() {
        $files = glob($this->storagePath . '*.expiry');
        
        foreach ($files as $file) {
            $expiry = (int) file_get_contents($file);
            
            if ($expiry < time()) {
                $key = basename($file, '.expiry');
                $this->delete($key);
            }
        }
    }
}
