<?php
require_once '../config/db.php';

try {
    // Добавляем колонку role в таблицу users
    $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user' AFTER email");
    
    // Создаем первого админа (замените email на нужный)
    $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE email = 'admin@dodep.ru' LIMIT 1");
    $stmt->execute();
    
    echo "Users table updated successfully";
} catch (PDOException $e) {
    echo "Error updating users table: " . $e->getMessage();
}
?> 