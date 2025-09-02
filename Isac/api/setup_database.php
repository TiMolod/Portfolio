<?php
require_once '../config/db.php';

try {
    // Читаем SQL-файл
    $sql = file_get_contents('create_tables.sql');
    
    // Выполняем SQL-запросы
    $pdo->exec($sql);
    
    echo "База данных успешно настроена!\n";
} catch (PDOException $e) {
    die("Ошибка при настройке базы данных: " . $e->getMessage() . "\n");
} 