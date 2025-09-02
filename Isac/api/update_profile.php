<?php
session_start();
header('Content-Type: application/json');

require_once '../config/db.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

$userId = $_SESSION['user']['id'];
$name = $_POST['name'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($name)) {
    echo json_encode(['success' => false, 'error' => 'Имя не может быть пустым']);
    exit;
}

try {
    if (!empty($password)) {
        // Если указан новый пароль, обновляем имя и пароль
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET name = ?, password = ? WHERE id = ?');
        $stmt->execute([$name, $hashedPassword, $userId]);
    } else {
        // Если пароль не указан, обновляем только имя
        $stmt = $pdo->prepare('UPDATE users SET name = ? WHERE id = ?');
        $stmt->execute([$name, $userId]);
    }

    // Обновляем данные в сессии
    $_SESSION['user']['name'] = $name;

    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Ошибка сервера']);
} 