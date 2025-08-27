<?php
require_once 'config.php';

// Файл для хранения пользователей
$users_file = 'users.json';
$login_attempts_file = 'login_attempts.json';

// Функция для логирования активности
function logActivity($action, $username, $ip, $success = true, $details = []) {
    $context = array_merge([
        'action' => $action,
        'username' => $username,
        'ip' => $ip,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ], $details);
    
    $level = $success ? 'INFO' : 'WARNING';
    $message = "User activity: $action - $username - $ip";
    
    logMessage($message, $level, $context);
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
        if ($count >= MAX_LOGIN_ATTEMPTS) {
            return true;
        }
    }
    
    return false;
}

// Функция для записи попытки входа
function recordLoginAttempt($ip, $success) {
    global $login_attempts_file;
    
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
    
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = "Пароль должен содержать минимум " . PASSWORD_MIN_LENGTH . " символов";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Пароль должен содержать хотя бы одну заглавную букву";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Пароль должен содержать хотя бы одну строчную букву";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Пароль должен содержать хотя бы одну цифру";
    }
    
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "Пароль должен содержать хотя бы один специальный символ";
    }
    
    return $errors;
}

// Функция для регистрации пользователя
function registerUser($username, $password, $email = null) {
    $username = sanitizeInput($username, 'string');
    $email = $email ? sanitizeInput($email, 'email') : null;
    
    // Проверяем сложность пароля
    $passwordErrors = validatePassword($password);
    if (!empty($passwordErrors)) {
        return ['success' => false, 'errors' => $passwordErrors];
    }
    
    $users = loadUsers();
    
    // Проверяем, не существует ли уже пользователь
    foreach ($users as $user) {
        if (strtolower($user['username']) === strtolower($username)) {
            return ['success' => false, 'errors' => ['Пользователь с таким именем уже существует']];
        }
    }
    
    // Создаем нового пользователя
    $newUser = [
        'id' => uniqid('user_', true),
        'username' => $username,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'email' => $email,
        'created_at' => date('Y-m-d H:i:s'),
        'last_login' => null,
        'is_active' => true,
        'role' => 'user'
    ];
    
    $users[] = $newUser;
    saveUsers($users);
    
    logActivity('user_registered', $username, $_SERVER['REMOTE_ADDR'] ?? 'unknown', true);
    
    return ['success' => true, 'user' => $newUser];
}

// Функция для аутентификации пользователя
function authenticateUser($username, $password) {
    $username = sanitizeInput($username, 'string');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Проверяем блокировку IP
    if (isIPBlocked($ip)) {
        logActivity('login_blocked', $username, $ip, false, ['reason' => 'ip_blocked']);
        return ['success' => false, 'error' => 'Слишком много неудачных попыток входа. Попробуйте позже.'];
    }
    
    $users = loadUsers();
    
    foreach ($users as $user) {
        if (strtolower($user['username']) === strtolower($username)) {
            if (password_verify($password, $user['password_hash'])) {
                // Успешный вход
                recordLoginAttempt($ip, true);
                
                // Обновляем время последнего входа
                $user['last_login'] = date('Y-m-d H:i:s');
                $user['login_count'] = ($user['login_count'] ?? 0) + 1;
                
                // Обновляем пользователя в списке
                foreach ($users as &$u) {
                    if ($u['id'] === $user['id']) {
                        $u = $user;
                        break;
                    }
                }
                saveUsers($users);
                
                // Устанавливаем сессию
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['login_time'] = time();
                
                logActivity('login_success', $username, $ip, true);
                
                return ['success' => true, 'user' => $user];
            } else {
                // Неверный пароль
                recordLoginAttempt($ip, false);
                logActivity('login_failed', $username, $ip, false, ['reason' => 'invalid_password']);
                return ['success' => false, 'error' => 'Неверное имя пользователя или пароль'];
            }
        }
    }
    
    // Пользователь не найден
    recordLoginAttempt($ip, false);
    logActivity('login_failed', $username, $ip, false, ['reason' => 'user_not_found']);
    return ['success' => false, 'error' => 'Неверное имя пользователя или пароль'];
}

// Функция для проверки авторизации
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Функция для получения текущего пользователя
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $users = loadUsers();
    foreach ($users as $user) {
        if ($user['id'] === $_SESSION['user_id']) {
            return $user;
        }
    }
    
    return null;
}

// Функция для выхода из системы
function logout() {
    if (isLoggedIn()) {
        logActivity('logout', $_SESSION['username'], $_SERVER['REMOTE_ADDR'] ?? 'unknown', true);
    }
    
    session_destroy();
    return ['success' => true];
}

// Функция для проверки роли пользователя
function hasRole($role) {
    $user = getCurrentUser();
    return $user && $user['role'] === $role;
}

// Функция для изменения пароля
function changePassword($userId, $currentPassword, $newPassword) {
    $users = loadUsers();
    
    foreach ($users as &$user) {
        if ($user['id'] === $userId) {
            if (password_verify($currentPassword, $user['password_hash'])) {
                // Проверяем сложность нового пароля
                $passwordErrors = validatePassword($newPassword);
                if (!empty($passwordErrors)) {
                    return ['success' => false, 'errors' => $passwordErrors];
                }
                
                $user['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
                $user['password_changed_at'] = date('Y-m-d H:i:s');
                
                saveUsers($users);
                
                logActivity('password_changed', $user['username'], $_SERVER['REMOTE_ADDR'] ?? 'unknown', true);
                
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => 'Неверный текущий пароль'];
            }
        }
    }
    
    return ['success' => false, 'error' => 'Пользователь не найден'];
}

// Функция для удаления пользователя (только для администратора)
function deleteUser($userId) {
    if (!hasRole('admin')) {
        return ['success' => false, 'error' => 'Недостаточно прав'];
    }
    
    $users = loadUsers();
    $deletedUser = null;
    
    foreach ($users as $key => $user) {
        if ($user['id'] === $userId) {
            $deletedUser = $user;
            unset($users[$key]);
            break;
        }
    }
    
    if ($deletedUser) {
        $users = array_values($users); // Переиндексируем массив
        saveUsers($users);
        
        logActivity('user_deleted', $deletedUser['username'], $_SERVER['REMOTE_ADDR'] ?? 'unknown', true, ['deleted_by' => $_SESSION['username']]);
        
        return ['success' => true, 'user' => $deletedUser];
    }
    
    return ['success' => false, 'error' => 'Пользователь не найден'];
}

// Функция для получения статистики пользователей
function getUserStats() {
    $users = loadUsers();
    $stats = [
        'total_users' => count($users),
        'active_users' => 0,
        'recent_logins' => 0,
        'admin_users' => 0
    ];
    
    $recentTime = time() - (7 * 24 * 60 * 60); // 7 дней
    
    foreach ($users as $user) {
        if ($user['is_active'] ?? true) {
            $stats['active_users']++;
        }
        
        if ($user['role'] === 'admin') {
            $stats['admin_users']++;
        }
        
        if (isset($user['last_login'])) {
            $lastLogin = strtotime($user['last_login']);
            if ($lastLogin > $recentTime) {
                $stats['recent_logins']++;
            }
        }
    }
    
    return $stats;
}
?>
