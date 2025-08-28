<?php

/**
 * Безопасный загрузчик переменных окружения
 * Загружает конфигурацию из .env файла
 */
class EnvironmentLoader {
    private $config = [];
    private $envFile;
    
    public function __construct($envFile = null) {
        $this->envFile = $envFile ?: __DIR__ . '/../.env';
        $this->loadEnvFile();
        $this->validateRequiredKeys();
    }
    
    /**
     * Загружает переменные из .env файла
     */
    private function loadEnvFile() {
        if (!file_exists($this->envFile)) {
            throw new Exception('Environment file not found: ' . $this->envFile);
        }
        
        $lines = file($this->envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Пропускаем комментарии
            if (strpos($line, '#') === 0) {
                continue;
            }
            
            // Парсим строки вида KEY=value
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Убираем кавычки если есть
                if (preg_match('/^["\'](.*)["\']$/', $value, $matches)) {
                    $value = $matches[1];
                }
                
                $this->config[$key] = $value;
            }
        }
    }
    
    /**
     * Проверяет наличие обязательных ключей
     */
    private function validateRequiredKeys() {
        $required = [
            'DEEPSEEK_API_KEY',
            'APP_NAME',
            'DEBUG_MODE'
        ];
        
        foreach ($required as $key) {
            if (!isset($this->config[$key])) {
                throw new Exception("Required environment variable missing: $key");
            }
        }
    }
    
    /**
     * Получает значение переменной окружения
     */
    public function get($key, $default = null) {
        return $this->config[$key] ?? $default;
    }
    
    /**
     * Получает все переменные
     */
    public function getAll() {
        return $this->config;
    }
    
    /**
     * Проверяет существование переменной
     */
    public function has($key) {
        return isset($this->config[$key]);
    }
    
    /**
     * Получает булево значение
     */
    public function getBool($key, $default = false) {
        $value = $this->get($key, $default);
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
    
    /**
     * Получает целое число
     */
    public function getInt($key, $default = 0) {
        $value = $this->get($key, $default);
        return (int) $value;
    }
}
