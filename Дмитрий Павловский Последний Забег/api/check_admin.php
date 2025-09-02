<?php
require_once 'check_auth.php';
require_once '../config/db.php';

// Проверяем, является ли пользователь администратором
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ? AND role = 'admin'");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();

if (!$admin) {
    header('Location: /index.php');
    exit;
}
?> 