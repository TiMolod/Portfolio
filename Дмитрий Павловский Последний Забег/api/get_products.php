<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/db.php';

try {
    $stmt = $pdo->query('SELECT id, name, description, price, CONCAT("/images/", image) as image FROM products');
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Добавляем отладочную информацию
    error_log('Products fetched: ' . print_r($products, true));
    
    echo json_encode($products);
} catch(PDOException $e) {
    error_log('Error fetching products: ' . $e->getMessage());
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?> 