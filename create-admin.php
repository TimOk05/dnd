<?php
require_once 'config.php';
require_once 'users.php';

// Проверяем, есть ли уже администратор
$users = loadUsers();
$hasAdmin = false;

foreach ($users as $user) {
    if (isset($user['role']) && $user['role'] === 'admin') {
        $hasAdmin = true;
        break;
    }
}

if ($hasAdmin) {
    echo "Администратор уже существует!";
    exit;
}

// Создаем администратора
$adminData = [
    'id' => uniqid('admin_', true),
    'username' => 'admin',
    'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
    'role' => 'admin',
    'created_at' => date('Y-m-d H:i:s'),
    'last_login' => null,
    'login_count' => 0
];

$users[] = $adminData;
saveUsers($users);

echo "Администратор создан успешно!<br>";
echo "Логин: admin<br>";
echo "Пароль: admin123<br>";
echo "<br><a href='login.php'>Войти в систему</a>";
?>
