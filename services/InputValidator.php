<?php

/**
 * Улучшенная система валидации входных данных
 * Защищает от XSS, SQL Injection и других атак
 */
class InputValidator {
    private $errors = [];
    private $sanitized = [];
    
    /**
     * Валидирует данные по правилам
     */
    public function validate($data, $rules) {
        $this->errors = [];
        $this->sanitized = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $this->validateField($field, $value, $fieldRules);
        }
        
        return empty($this->errors);
    }
    
    /**
     * Валидирует отдельное поле
     */
    private function validateField($field, $value, $rules) {
        foreach ($rules as $rule) {
            if (!$this->checkRule($field, $value, $rule)) {
                $this->errors[$field][] = $this->getErrorMessage($field, $rule);
            }
        }
        
        // Если нет ошибок, сохраняем очищенное значение
        if (!isset($this->errors[$field])) {
            $this->sanitized[$field] = $this->sanitize($value, $rules);
        }
    }
    
    /**
     * Проверяет правило валидации
     */
    private function checkRule($field, $value, $rule) {
        switch ($rule) {
            case 'required':
                return !empty($value) || $value === '0';
                
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
                
            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL) !== false;
                
            case 'alpha':
                return preg_match('/^[a-zA-Zа-яА-Я\s]+$/', $value);
                
            case 'alphanumeric':
                return preg_match('/^[a-zA-Z0-9а-яА-Я\s]+$/', $value);
                
            case 'numeric':
                return is_numeric($value);
                
            case 'integer':
                return filter_var($value, FILTER_VALIDATE_INT) !== false;
                
            case 'xss_clean':
                return $this->isXssClean($value);
                
            case 'sql_injection_clean':
                return $this->isSqlInjectionClean($value);
                
            default:
                // Проверяем правила с параметрами (например, min:3, max:50)
                if (preg_match('/^(\w+):(.+)$/', $rule, $matches)) {
                    $ruleName = $matches[1];
                    $ruleValue = $matches[2];
                    
                    return $this->checkParameterizedRule($field, $value, $ruleName, $ruleValue);
                }
                
                return true;
        }
    }
    
    /**
     * Проверяет правила с параметрами
     */
    private function checkParameterizedRule($field, $value, $ruleName, $ruleValue) {
        switch ($ruleName) {
            case 'min':
                return strlen($value) >= (int) $ruleValue;
                
            case 'max':
                return strlen($value) <= (int) $ruleValue;
                
            case 'min_value':
                return (float) $value >= (float) $ruleValue;
                
            case 'max_value':
                return (float) $value <= (float) $ruleValue;
                
            case 'range':
                $range = explode('-', $ruleValue);
                if (count($range) === 2) {
                    $min = (int) $range[0];
                    $max = (int) $range[1];
                    return (int) $value >= $min && (int) $value <= $max;
                }
                return false;
                
            case 'regex':
                return preg_match($ruleValue, $value);
                
            case 'in':
                $allowedValues = explode(',', $ruleValue);
                return in_array($value, $allowedValues);
                
            default:
                return true;
        }
    }
    
    /**
     * Проверяет на XSS атаки
     */
    private function isXssClean($value) {
        if (empty($value)) {
            return true;
        }
        
        $dangerous = [
            '<script', 'javascript:', 'onload', 'onerror', 'onclick', 'onmouseover',
            'vbscript:', 'expression(', 'url(', 'eval(', 'document.cookie',
            'window.location', 'alert(', 'confirm(', 'prompt('
        ];
        
        $lowerValue = strtolower($value);
        foreach ($dangerous as $pattern) {
            if (strpos($lowerValue, $pattern) !== false) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Проверяет на SQL Injection атаки
     */
    private function isSqlInjectionClean($value) {
        if (empty($value)) {
            return true;
        }
        
        $dangerous = [
            'union', 'select', 'insert', 'update', 'delete', 'drop', 'create',
            'alter', 'exec', 'execute', 'script', '--', '/*', '*/', 'xp_',
            'sp_', 'waitfor', 'delay', 'benchmark', 'sleep'
        ];
        
        $lowerValue = strtolower($value);
        foreach ($dangerous as $pattern) {
            if (strpos($lowerValue, $pattern) !== false) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Очищает значение
     */
    private function sanitize($value, $rules) {
        if (empty($value)) {
            return $value;
        }
        
        // Определяем тип очистки на основе правил
        if (in_array('html', $rules)) {
            return $this->sanitizeHtml($value);
        } elseif (in_array('email', $rules)) {
            return filter_var($value, FILTER_SANITIZE_EMAIL);
        } elseif (in_array('url', $rules)) {
            return filter_var($value, FILTER_SANITIZE_URL);
        } elseif (in_array('integer', $rules)) {
            return (int) $value;
        } elseif (in_array('numeric', $rules)) {
            return (float) $value;
        } else {
            return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Очищает HTML, оставляя только безопасные теги
     */
    private function sanitizeHtml($value) {
        $allowedTags = '<p><br><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6>';
        return strip_tags($value, $allowedTags);
    }
    
    /**
     * Получает сообщение об ошибке
     */
    private function getErrorMessage($field, $rule) {
        $messages = [
            'required' => 'Поле обязательно для заполнения',
            'email' => 'Некорректный email адрес',
            'url' => 'Некорректный URL',
            'alpha' => 'Разрешены только буквы',
            'alphanumeric' => 'Разрешены только буквы и цифры',
            'numeric' => 'Разрешены только числа',
            'integer' => 'Разрешены только целые числа',
            'xss_clean' => 'Обнаружен потенциально опасный код',
            'sql_injection_clean' => 'Обнаружен потенциально опасный код'
        ];
        
        // Обработка правил с параметрами
        if (preg_match('/^(\w+):(.+)$/', $rule, $matches)) {
            $ruleName = $matches[1];
            $ruleValue = $matches[2];
            
            switch ($ruleName) {
                case 'min':
                    return "Минимальная длина: $ruleValue символов";
                case 'max':
                    return "Максимальная длина: $ruleValue символов";
                case 'min_value':
                    return "Минимальное значение: $ruleValue";
                case 'max_value':
                    return "Максимальное значение: $ruleValue";
                case 'range':
                    return "Значение должно быть в диапазоне: $ruleValue";
                case 'in':
                    return "Разрешены только значения: $ruleValue";
                default:
                    return "Некорректное значение";
            }
        }
        
        return $messages[$rule] ?? 'Некорректное значение';
    }
    
    /**
     * Получает ошибки валидации
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Получает очищенные данные
     */
    public function getSanitized() {
        return $this->sanitized;
    }
    
    /**
     * Получает первое сообщение об ошибке
     */
    public function getFirstError() {
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return reset($fieldErrors);
            }
        }
        return null;
    }
    
    /**
     * Проверяет, есть ли ошибки для поля
     */
    public function hasError($field) {
        return isset($this->errors[$field]) && !empty($this->errors[$field]);
    }
    
    /**
     * Получает ошибки для конкретного поля
     */
    public function getFieldErrors($field) {
        return $this->errors[$field] ?? [];
    }
}
