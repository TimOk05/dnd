<?php

/**
 * Улучшенная система кэширования для API запросов
 * Ускоряет работу приложения и снижает нагрузку на внешние API
 */
class APICache {
    private $cachePath;
    private $defaultTTL;
    
    public function __construct($cachePath = null, $defaultTTL = 3600) {
        $this->cachePath = $cachePath ?: __DIR__ . '/../cache/api/';
        $this->defaultTTL = $defaultTTL;
        
        // Создаем директорию если не существует
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }
    
    /**
     * Получает данные из кэша
     */
    public function get($key) {
        $file = $this->getCacheFile($key);
        
        if (!file_exists($file)) {
            return null;
        }
        
        $data = json_decode(file_get_contents($file), true);
        
        if (!$data || !isset($data['expires']) || $data['expires'] < time()) {
            // Кэш истек, удаляем файл
            unlink($file);
            return null;
        }
        
        return $data['value'];
    }
    
    /**
     * Сохраняет данные в кэш
     */
    public function set($key, $value, $ttl = null) {
        $ttl = $ttl ?: $this->defaultTTL;
        $file = $this->getCacheFile($key);
        
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        return file_put_contents($file, json_encode($data), LOCK_EX) !== false;
    }
    
    /**
     * Удаляет данные из кэша
     */
    public function delete($key) {
        $file = $this->getCacheFile($key);
        
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return true;
    }
    
    /**
     * Проверяет существование кэша
     */
    public function exists($key) {
        $file = $this->getCacheFile($key);
        
        if (!file_exists($file)) {
            return false;
        }
        
        $data = json_decode(file_get_contents($file), true);
        return $data && isset($data['expires']) && $data['expires'] > time();
    }
    
    /**
     * Получает время истечения кэша
     */
    public function getExpiration($key) {
        $file = $this->getCacheFile($key);
        
        if (!file_exists($file)) {
            return null;
        }
        
        $data = json_decode(file_get_contents($file), true);
        return $data['expires'] ?? null;
    }
    
    /**
     * Продлевает время жизни кэша
     */
    public function extend($key, $ttl = null) {
        $ttl = $ttl ?: $this->defaultTTL;
        $file = $this->getCacheFile($key);
        
        if (!file_exists($file)) {
            return false;
        }
        
        $data = json_decode(file_get_contents($file), true);
        
        if (!$data) {
            return false;
        }
        
        $data['expires'] = time() + $ttl;
        
        return file_put_contents($file, json_encode($data), LOCK_EX) !== false;
    }
    
    /**
     * Очищает весь кэш
     */
    public function clear() {
        $files = glob($this->cachePath . '*.cache');
        
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }
    
    /**
     * Очищает устаревшие записи
     */
    public function cleanup() {
        $files = glob($this->cachePath . '*.cache');
        $deleted = 0;
        
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            
            if (!$data || !isset($data['expires']) || $data['expires'] < time()) {
                unlink($file);
                $deleted++;
            }
        }
        
        return $deleted;
    }
    
    /**
     * Получает статистику кэша
     */
    public function getStats() {
        $files = glob($this->cachePath . '*.cache');
        $total = count($files);
        $expired = 0;
        $size = 0;
        
        foreach ($files as $file) {
            $size += filesize($file);
            $data = json_decode(file_get_contents($file), true);
            
            if (!$data || !isset($data['expires']) || $data['expires'] < time()) {
                $expired++;
            }
        }
        
        return [
            'total_files' => $total,
            'expired_files' => $expired,
            'valid_files' => $total - $expired,
            'total_size' => $size,
            'cache_path' => $this->cachePath
        ];
    }
    
    /**
     * Генерирует ключ кэша для URL
     */
    public function generateKey($url, $params = []) {
        $key = $url;
        
        if (!empty($params)) {
            ksort($params); // Сортируем параметры для консистентности
            $key .= '?' . http_build_query($params);
        }
        
        return md5($key);
    }
    
    /**
     * Получает путь к файлу кэша
     */
    private function getCacheFile($key) {
        return $this->cachePath . $key . '.cache';
    }
    
    /**
     * Кэширует результат функции
     */
    public function remember($key, $ttl, $callback) {
        $cached = $this->get($key);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $value = $callback();
        $this->set($key, $value, $ttl);
        
        return $value;
    }
    
    /**
     * Кэширует результат функции с тегами
     */
    public function rememberWithTags($key, $ttl, $tags, $callback) {
        $cached = $this->get($key);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $value = $callback();
        
        // Сохраняем значение
        $this->set($key, $value, $ttl);
        
        // Сохраняем теги
        $this->saveTags($key, $tags);
        
        return $value;
    }
    
    /**
     * Очищает кэш по тегам
     */
    public function clearByTags($tags) {
        $tagFile = $this->cachePath . 'tags.json';
        
        if (!file_exists($tagFile)) {
            return 0;
        }
        
        $tagData = json_decode(file_get_contents($tagFile), true) ?: [];
        $deleted = 0;
        
        foreach ($tags as $tag) {
            if (isset($tagData[$tag])) {
                foreach ($tagData[$tag] as $key) {
                    if ($this->delete($key)) {
                        $deleted++;
                    }
                }
                unset($tagData[$tag]);
            }
        }
        
        // Обновляем файл тегов
        file_put_contents($tagFile, json_encode($tagData), LOCK_EX);
        
        return $deleted;
    }
    
    /**
     * Сохраняет теги для ключа
     */
    private function saveTags($key, $tags) {
        $tagFile = $this->cachePath . 'tags.json';
        $tagData = [];
        
        if (file_exists($tagFile)) {
            $tagData = json_decode(file_get_contents($tagFile), true) ?: [];
        }
        
        foreach ($tags as $tag) {
            if (!isset($tagData[$tag])) {
                $tagData[$tag] = [];
            }
            $tagData[$tag][] = $key;
        }
        
        file_put_contents($tagFile, json_encode($tagData), LOCK_EX);
    }
}
