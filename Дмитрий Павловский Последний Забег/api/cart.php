<?php
require_once '../config/db.php';
require_once 'check_auth.php'; // Добавляем проверку авторизации

header('Content-Type: application/json');

// Получить корзину
function getCart($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.image, (p.price * c.quantity) as total
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Добавить в корзину
if (isset($_POST['action']) && $_POST['action'] == 'add') {
    $product_id = $_POST['product_id'];
    
    // Проверяем, есть ли уже товар в корзине
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE product_id = ? AND user_id = ?");
    $stmt->execute([$product_id, $_SESSION['user_id']]);
    $item = $stmt->fetch();
    
    if ($item) {
        // Если есть - увеличиваем количество
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$item['id'], $_SESSION['user_id']]);
    } else {
        // Если нет - добавляем новый
        $stmt = $pdo->prepare("INSERT INTO cart (product_id, quantity, user_id) VALUES (?, 1, ?)");
        $stmt->execute([$product_id, $_SESSION['user_id']]);
    }
    
    echo json_encode(['success' => true, 'cart' => getCart($pdo, $_SESSION['user_id'])]);
}

// Удалить из корзины
else if (isset($_POST['action']) && $_POST['action'] == 'remove') {
    try {
        $cart_id = $_POST['cart_id'];
        
        // Удаляем товар с проверкой принадлежности пользователю
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $_SESSION['user_id']]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'cart' => getCart($pdo, $_SESSION['user_id'])]);
        } else {
            throw new Exception('Товар не найден');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// Обновить количество
else if (isset($_POST['action']) && $_POST['action'] == 'update_quantity') {
    try {
        $cart_id = $_POST['cart_id'];
        $quantity = (int)$_POST['quantity'];
        
        if ($quantity < 1) {
            throw new Exception('Количество должно быть больше 0');
        }
        
        // Обновляем количество с проверкой принадлежности пользователю
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$quantity, $cart_id, $_SESSION['user_id']]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'cart' => getCart($pdo, $_SESSION['user_id'])]);
        } else {
            throw new Exception('Товар не найден');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// Получить корзину
else {
    echo json_encode(['success' => true, 'cart' => getCart($pdo, $_SESSION['user_id'])]);
} 