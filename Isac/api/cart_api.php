<?php
session_start();
require_once '../config/db.php';

// Настройка CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Обработка OPTIONS запроса
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');

// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Функция для логирования
function logDebug($message, $data = null) {
    $log = date('Y-m-d H:i:s') . ' - ' . $message;
    if ($data !== null) {
        $log .= ' - Data: ' . print_r($data, true);
    }
    error_log($log);
}

// Функция для получения или создания ID сессии
function getSessionId() {
    if (!isset($_SESSION['cart_id'])) {
        $_SESSION['cart_id'] = session_id();
    }
    return $_SESSION['cart_id'];
}

// Получение товаров в корзине
function getCartItems($pdo, $sessionId) {
    $stmt = $pdo->prepare("
        SELECT ci.*, p.name, p.price, p.image, p.description 
        FROM cart_items ci 
        JOIN products p ON ci.product_id = p.id 
        WHERE ci.session_id = ?
    ");
    $stmt->execute([$sessionId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

try {
    $sessionId = getSessionId();
    
    // Получаем данные из тела запроса для POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = file_get_contents('php://input');
        if (!empty($input)) {
            $_POST = json_decode($input, true) ?? [];
        }
    }
    
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    logDebug('Request received', [
        'method' => $_SERVER['REQUEST_METHOD'],
        'action' => $action,
        'session_id' => $sessionId,
        'post_data' => $_POST,
        'get_data' => $_GET,
        'raw_input' => $input ?? null
    ]);

    switch ($action) {
        case 'add':
            $productId = $_POST['product_id'] ?? null;
            $quantity = $_POST['quantity'] ?? 1;

            logDebug('Adding product to cart', [
                'product_id' => $productId,
                'quantity' => $quantity
            ]);

            if (!$productId) {
                throw new Exception('Product ID is required');
            }

            // Проверяем существование товара
            $checkStmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
            $checkStmt->execute([$productId]);
            if (!$checkStmt->fetch()) {
                throw new Exception('Product not found');
            }

            // Проверяем, есть ли уже такой товар в корзине
            $stmt = $pdo->prepare("
                SELECT quantity FROM cart_items 
                WHERE session_id = ? AND product_id = ?
            ");
            $stmt->execute([$sessionId, $productId]);
            $existingItem = $stmt->fetch();

            if ($existingItem) {
                // Обновляем количество
                $stmt = $pdo->prepare("
                    UPDATE cart_items 
                    SET quantity = quantity + ? 
                    WHERE session_id = ? AND product_id = ?
                ");
                $stmt->execute([$quantity, $sessionId, $productId]);
                logDebug('Updated existing cart item');
            } else {
                // Добавляем новый товар
                $stmt = $pdo->prepare("
                    INSERT INTO cart_items (session_id, product_id, quantity) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$sessionId, $productId, $quantity]);
                logDebug('Added new cart item');
            }

            $items = getCartItems($pdo, $sessionId);
            logDebug('Cart items after add', $items);

            echo json_encode([
                'success' => true,
                'message' => 'Product added to cart',
                'items' => $items
            ]);
            break;

        case 'update':
            $productId = $_POST['product_id'] ?? null;
            $quantity = $_POST['quantity'] ?? null;

            logDebug('Updating cart quantity', [
                'product_id' => $productId,
                'quantity' => $quantity
            ]);

            if (!$productId || $quantity === null) {
                throw new Exception('Product ID and quantity are required');
            }

            if ($quantity <= 0) {
                // Удаляем товар
                $stmt = $pdo->prepare("
                    DELETE FROM cart_items 
                    WHERE session_id = ? AND product_id = ?
                ");
                $stmt->execute([$sessionId, $productId]);
                logDebug('Removed item from cart');
            } else {
                // Обновляем количество
                $stmt = $pdo->prepare("
                    UPDATE cart_items 
                    SET quantity = ? 
                    WHERE session_id = ? AND product_id = ?
                ");
                $stmt->execute([$quantity, $sessionId, $productId]);
                logDebug('Updated item quantity');
            }

            echo json_encode([
                'success' => true,
                'message' => 'Cart updated',
                'items' => getCartItems($pdo, $sessionId)
            ]);
            break;

        case 'remove':
            $productId = $_POST['product_id'] ?? null;

            logDebug('Removing product from cart', [
                'product_id' => $productId
            ]);

            if (!$productId) {
                throw new Exception('Product ID is required');
            }

            $stmt = $pdo->prepare("
                DELETE FROM cart_items 
                WHERE session_id = ? AND product_id = ?
            ");
            $stmt->execute([$sessionId, $productId]);

            echo json_encode([
                'success' => true,
                'message' => 'Product removed from cart',
                'items' => getCartItems($pdo, $sessionId)
            ]);
            break;

        case 'get':
            $items = getCartItems($pdo, $sessionId);
            logDebug('Getting cart items', $items);

            echo json_encode([
                'success' => true,
                'items' => $items
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    logDebug('Error occurred', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 