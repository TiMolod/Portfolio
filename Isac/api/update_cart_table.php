<?php
require_once '../config/db.php';

try {
    // Добавляем колонку user_id в таблицу cart
    $pdo->exec("ALTER TABLE cart ADD COLUMN user_id INT NOT NULL AFTER id");
    
    // Добавляем внешний ключ для связи с таблицей users
    $pdo->exec("ALTER TABLE cart ADD FOREIGN KEY (user_id) REFERENCES users(id)");
    
    echo "Cart table updated successfully";
} catch (PDOException $e) {
    echo "Error updating cart table: " . $e->getMessage();
}
?> 