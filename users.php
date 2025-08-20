<?php
session_start();

// Файл для хранения пользователей
$users_file = 'users.json';

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

// Функция для проверки логина
function checkLogin($username, $password) {
    $users = loadUsers();
    foreach ($users as $user) {
        if ($user['username'] === $username && password_verify($password, $user['password'])) {
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
        if ($user['username'] === $username) {
            return ['success' => false, 'message' => 'Пользователь с таким именем уже существует'];
        }
    }
    
    // Добавляем нового пользователя
    $users[] = [
        'username' => $username,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    saveUsers($users);
    return ['success' => true, 'message' => 'Пользователь успешно зарегистрирован'];
}

// Обработка POST запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'register') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Валидация
        if (strlen($username) < 3 || strlen($username) > 20) {
            echo json_encode(['success' => false, 'message' => 'Имя пользователя должно быть от 3 до 20 символов']);
            exit;
        }
        
        if (strlen($password) < 4) {
            echo json_encode(['success' => false, 'message' => 'Пароль должен быть не менее 4 символов']);
            exit;
        }
        
        if (!preg_match('/^[а-яёa-z0-9_]+$/ui', $username)) {
            echo json_encode(['success' => false, 'message' => 'Имя пользователя может содержать только буквы, цифры и знак подчеркивания']);
            exit;
        }
        
        $result = registerUser($username, $password);
        echo json_encode($result);
        exit;
    }
    
    if ($action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (checkLogin($username, $password)) {
            $_SESSION['user'] = $username;
            $_SESSION['access_granted'] = true;
            echo json_encode(['success' => true, 'message' => 'Вход выполнен успешно']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Неверное имя пользователя или пароль']);
        }
        exit;
    }
    
    if ($action === 'logout') {
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Выход выполнен']);
        exit;
    }
}

// Проверяем, авторизован ли пользователь
function isLoggedIn() {
    return isset($_SESSION['user']) && isset($_SESSION['access_granted']) && $_SESSION['access_granted'] === true;
}

// Получаем имя текущего пользователя
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}
?>
