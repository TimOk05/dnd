<?php
session_start();

// Файл для хранения пользователей
$users_file = 'users.json';
$login_attempts_file = 'login_attempts.json';
$max_attempts = 5; // Максимум попыток входа
$lockout_time = 900; // 15 минут блокировки

// Функция для генерации CSRF токена
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Функция для проверки CSRF токена
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Функция для логирования
function logActivity($action, $username, $ip, $success = true) {
    $log_file = 'security.log';
    $timestamp = date('Y-m-d H:i:s');
    $status = $success ? 'SUCCESS' : 'FAILED';
    $log_entry = "[$timestamp] $status - $action - User: $username - IP: $ip\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Функция для проверки блокировки IP
function isIPBlocked($ip) {
    global $login_attempts_file, $lockout_time;
    
    if (!file_exists($login_attempts_file)) {
        return false;
    }
    
    $attempts = json_decode(file_get_contents($login_attempts_file), true) ?: [];
    
    if (isset($attempts[$ip])) {
        $last_attempt = $attempts[$ip]['last_attempt'];
        $count = $attempts[$ip]['count'];
        
        // Если прошло время блокировки, сбрасываем счетчик
        if (time() - $last_attempt > $lockout_time) {
            unset($attempts[$ip]);
            file_put_contents($login_attempts_file, json_encode($attempts));
            return false;
        }
        
        // Если превышен лимит попыток
        if ($count >= 5) {
            return true;
        }
    }
    
    return false;
}

// Функция для записи попытки входа
function recordLoginAttempt($ip, $success) {
    global $login_attempts_file, $max_attempts;
    
    $attempts = json_decode(file_get_contents($login_attempts_file), true) ?: [];
    
    if (!isset($attempts[$ip])) {
        $attempts[$ip] = ['count' => 0, 'last_attempt' => time()];
    }
    
    if ($success) {
        // Успешный вход - сбрасываем счетчик
        unset($attempts[$ip]);
    } else {
        // Неудачная попытка
        $attempts[$ip]['count']++;
        $attempts[$ip]['last_attempt'] = time();
    }
    
    file_put_contents($login_attempts_file, json_encode($attempts));
}

// Функция для загрузки пользователей
function loadUsers() {
    global $users_file;
    if (file_exists($users_file)) {
        $data = file_get_contents($users_file);
        return json_decode($data, true) ?: [];
    }
    return [];
}

// Функция для сохранения пользователей
function saveUsers($users) {
    global $users_file;
    file_put_contents($users_file, json_encode($users, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

// Функция для проверки сложности пароля
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Пароль должен быть не менее 8 символов';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Пароль должен содержать хотя бы одну заглавную букву';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Пароль должен содержать хотя бы одну строчную букву';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Пароль должен содержать хотя бы одну цифру';
    }
    
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Пароль должен содержать хотя бы один специальный символ';
    }
    
    return $errors;
}

// Функция для проверки логина
function checkLogin($username, $password) {
    $users = loadUsers();
    foreach ($users as $user) {
        if (hash_equals($user['username'], $username) && password_verify($password, $user['password'])) {
            return true;
        }
    }
    return false;
}

// Функция для регистрации
function registerUser($username, $password) {
    $users = loadUsers();
    
    // Проверяем, не занято ли имя пользователя
    foreach ($users as $user) {
        if (hash_equals($user['username'], $username)) {
            return ['success' => false, 'message' => 'Пользователь с таким именем уже существует'];
        }
    }
    
    // Добавляем нового пользователя
    $users[] = [
        'username' => $username,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'created_at' => date('Y-m-d H:i:s'),
        'last_login' => null,
        'login_count' => 0
    ];
    
    saveUsers($users);
    return ['success' => true, 'message' => 'Пользователь успешно зарегистрирован'];
}

// Обработка POST запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    if ($action === 'register') {
        // Проверяем CSRF токен
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Ошибка безопасности. Обновите страницу.']);
            exit;
        }
        
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Валидация имени пользователя
        if (strlen($username) < 3 || strlen($username) > 20) {
            echo json_encode(['success' => false, 'message' => 'Имя пользователя должно быть от 3 до 20 символов']);
            exit;
        }
        
        if (!preg_match('/^[а-яёa-z0-9_]+$/ui', $username)) {
            echo json_encode(['success' => false, 'message' => 'Имя пользователя может содержать только буквы, цифры и знак подчеркивания']);
            exit;
        }
        
        // Валидация пароля
        $password_errors = validatePassword($password);
        if (!empty($password_errors)) {
            echo json_encode(['success' => false, 'message' => implode('. ', $password_errors)]);
            exit;
        }
        
        $result = registerUser($username, $password);
        if ($result['success']) {
            logActivity('REGISTER', $username, $ip, true);
        }
        echo json_encode($result);
        exit;
    }
    
    if ($action === 'login') {
        // Проверяем блокировку IP
        if (isIPBlocked($ip)) {
            echo json_encode(['success' => false, 'message' => 'Слишком много неудачных попыток. Попробуйте через 15 минут.']);
            exit;
        }
        
        // Проверяем CSRF токен
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Ошибка безопасности. Обновите страницу.']);
            exit;
        }
        
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (checkLogin($username, $password)) {
            $_SESSION['user'] = $username;
            $_SESSION['access_granted'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['ip'] = $ip;
            
            // Обновляем статистику пользователя
            $users = loadUsers();
            foreach ($users as &$user) {
                if (hash_equals($user['username'], $username)) {
                    $user['last_login'] = date('Y-m-d H:i:s');
                    $user['login_count'] = ($user['login_count'] ?? 0) + 1;
                    break;
                }
            }
            saveUsers($users);
            
            recordLoginAttempt($ip, true);
            logActivity('LOGIN', $username, $ip, true);
            
            echo json_encode(['success' => true, 'message' => 'Вход выполнен успешно']);
        } else {
            recordLoginAttempt($ip, false);
            logActivity('LOGIN_FAILED', $username, $ip, false);
            echo json_encode(['success' => false, 'message' => 'Неверное имя пользователя или пароль']);
        }
        exit;
    }
    
    if ($action === 'logout') {
        $username = $_SESSION['user'] ?? 'unknown';
        logActivity('LOGOUT', $username, $ip, true);
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Выход выполнен']);
        exit;
    }
    
    if ($action === 'admin_login') {
        $password = $_POST['password'] ?? '';
        
        if (checkAdminPassword($password)) {
            $_SESSION['is_admin'] = true;
            logActivity('ADMIN_LOGIN', 'admin', $ip, true);
            echo json_encode(['success' => true, 'message' => 'Доступ администратора предоставлен']);
        } else {
            logActivity('ADMIN_LOGIN_FAILED', 'admin', $ip, false);
            echo json_encode(['success' => false, 'message' => 'Неверный пароль администратора']);
        }
        exit;
    }
    
    if ($action === 'admin_logout') {
        unset($_SESSION['is_admin']);
        logActivity('ADMIN_LOGOUT', 'admin', $ip, true);
        echo json_encode(['success' => true, 'message' => 'Выход из режима администратора выполнен']);
        exit;
    }
}

// Проверяем, авторизован ли пользователь
function isLoggedIn() {
    if (!isset($_SESSION['user']) || !isset($_SESSION['access_granted']) || $_SESSION['access_granted'] !== true) {
        return false;
    }
    
    // Проверяем время сессии (8 часов)
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 28800) {
        session_destroy();
        return false;
    }
    
    // Проверяем IP адрес
    if (isset($_SESSION['ip']) && $_SESSION['ip'] !== ($_SERVER['REMOTE_ADDR'] ?? 'unknown')) {
        session_destroy();
        return false;
    }
    
    return true;
}

// Получаем имя текущего пользователя
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

// Функция для проверки административного пароля
function checkAdminPassword($password) {
    // Хеш пароля Timdndadmin
    $adminHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    return password_verify($password, $adminHash);
}

// Функция для проверки прав администратора
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}
?>
