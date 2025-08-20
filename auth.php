<?php
require_once 'config.php';

class Auth {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDbConnection();
        if (!$this->pdo) {
            throw new Exception('Ошибка подключения к базе данных. Проверьте настройки в config.php');
        }
    }
    
    // Регистрация нового пользователя
    public function register($username, $email, $password, $confirmPassword) {
        $errors = [];
        
        // Валидация данных
        if (!validateUsername($username)) {
            $errors[] = 'Имя пользователя должно содержать 3-20 символов (буквы, цифры, подчеркивания)';
        }
        
        if (!validateEmail($email)) {
            $errors[] = 'Некорректный email адрес';
        }
        
        $passwordErrors = validatePassword($password);
        if (!empty($passwordErrors)) {
            $errors = array_merge($errors, $passwordErrors);
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = 'Пароли не совпадают';
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Проверка существования пользователя
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'errors' => ['Пользователь с таким именем или email уже существует']];
            }
            
            // Создание пользователя
            $passwordHash = password_hash($password, PASSWORD_DEFAULT, ['cost' => SALT_ROUNDS]);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO users (username, email, password_hash) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$username, $email, $passwordHash]);
            
            return ['success' => true, 'message' => 'Регистрация успешна! Теперь вы можете войти в систему.'];
            
        } catch (PDOException $e) {
            logMessage("Registration error: " . $e->getMessage(), 'ERROR');
            return ['success' => false, 'errors' => ['Ошибка при регистрации. Попробуйте позже.']];
        }
    }
    
    // Авторизация пользователя
    public function login($username, $password) {
        $errors = [];
        
        // Проверка блокировки
        if ($this->isAccountLocked($username)) {
            return ['success' => false, 'errors' => ['Аккаунт временно заблокирован. Попробуйте позже.']];
        }
        
        try {
            $stmt = $this->pdo->prepare("SELECT id, username, password_hash, is_active FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $this->logLoginAttempt($username, false);
                return ['success' => false, 'errors' => ['Неверное имя пользователя или пароль']];
            }
            
            if (!$user['is_active']) {
                return ['success' => false, 'errors' => ['Аккаунт деактивирован']];
            }
            
            if (!password_verify($password, $user['password_hash'])) {
                $this->logLoginAttempt($username, false);
                return ['success' => false, 'errors' => ['Неверное имя пользователя или пароль']];
            }
            
            // Успешный вход
            $this->logLoginAttempt($username, true);
            $this->updateLastLogin($user['id']);
            
            // Создание сессии
            $sessionToken = $this->createSession($user['id']);
            
            return [
                'success' => true, 
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username']
                ],
                'session_token' => $sessionToken
            ];
            
        } catch (PDOException $e) {
            logMessage("Login error: " . $e->getMessage(), 'ERROR');
            return ['success' => false, 'errors' => ['Ошибка при входе. Попробуйте позже.']];
        }
    }
    
    // Проверка авторизации
    public function isAuthenticated() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token'])) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT us.id, u.username, u.is_active 
                FROM user_sessions us 
                JOIN users u ON us.user_id = u.id 
                WHERE us.session_token = ? AND us.expires_at > NOW()
            ");
            $stmt->execute([$_SESSION['session_token']]);
            $session = $stmt->fetch();
            
            if (!$session || $session['id'] != $_SESSION['user_id'] || !$session['is_active']) {
                $this->logout();
                return false;
            }
            
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // Получение данных текущего пользователя
    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        try {
            $stmt = $this->pdo->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }
    
    // Выход из системы
    public function logout() {
        if (isset($_SESSION['session_token'])) {
            try {
                $stmt = $this->pdo->prepare("DELETE FROM user_sessions WHERE session_token = ?");
                $stmt->execute([$_SESSION['session_token']]);
            } catch (PDOException $e) {
                logMessage("Logout error: " . $e->getMessage(), 'ERROR');
            }
        }
        
        session_destroy();
        return true;
    }
    
    // Смена пароля
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            $stmt = $this->pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
                return ['success' => false, 'errors' => ['Неверный текущий пароль']];
            }
            
            $passwordErrors = validatePassword($newPassword);
            if (!empty($passwordErrors)) {
                return ['success' => false, 'errors' => $passwordErrors];
            }
            
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT, ['cost' => SALT_ROUNDS]);
            
            $stmt = $this->pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$newPasswordHash, $userId]);
            
            return ['success' => true, 'message' => 'Пароль успешно изменен'];
            
        } catch (PDOException $e) {
            logMessage("Password change error: " . $e->getMessage(), 'ERROR');
            return ['success' => false, 'errors' => ['Ошибка при смене пароля']];
        }
    }
    
    // Приватные методы
    private function createSession($userId) {
        $token = generateToken();
        $expiresAt = date('Y-m-d H:i:s', time() + SESSION_TIMEOUT);
        
        $stmt = $this->pdo->prepare("
            INSERT INTO user_sessions (user_id, session_token, expires_at, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId, 
            $token, 
            $expiresAt, 
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        $_SESSION['user_id'] = $userId;
        $_SESSION['session_token'] = $token;
        
        return $token;
    }
    
    private function updateLastLogin($userId) {
        $stmt = $this->pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
    }
    
    private function logLoginAttempt($username, $success) {
        $stmt = $this->pdo->prepare("
            INSERT INTO login_attempts (username, ip_address, success) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$username, $_SERVER['REMOTE_ADDR'] ?? 'unknown', $success]);
    }
    
    private function isAccountLocked($username) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as attempts 
            FROM login_attempts 
            WHERE username = ? AND ip_address = ? AND success = 0 
            AND attempted_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$username, $_SERVER['REMOTE_ADDR'] ?? 'unknown', LOCKOUT_TIME]);
        $result = $stmt->fetch();
        
        return $result['attempts'] >= MAX_LOGIN_ATTEMPTS;
    }
}

// Обработка AJAX запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    try {
        $auth = new Auth();
        
        switch ($_POST['action']) {
            case 'register':
                $result = $auth->register(
                    $_POST['username'] ?? '',
                    $_POST['email'] ?? '',
                    $_POST['password'] ?? '',
                    $_POST['confirm_password'] ?? ''
                );
                break;
                
            case 'login':
                $result = $auth->login(
                    $_POST['username'] ?? '',
                    $_POST['password'] ?? ''
                );
                break;
                
            case 'logout':
                $result = ['success' => $auth->logout()];
                break;
                
            case 'change_password':
                if (!$auth->isAuthenticated()) {
                    $result = ['success' => false, 'errors' => ['Не авторизован']];
                    break;
                }
                $user = $auth->getCurrentUser();
                $result = $auth->changePassword(
                    $user['id'],
                    $_POST['current_password'] ?? '',
                    $_POST['new_password'] ?? ''
                );
                break;
                
            default:
                $result = ['success' => false, 'errors' => ['Неизвестное действие']];
        }
        
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'errors' => [DEBUG_MODE ? $e->getMessage() : 'Произошла ошибка']
        ], JSON_UNESCAPED_UNICODE);
    }
    
    exit;
}
?>
