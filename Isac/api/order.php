<?php
require_once 'check_auth.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_order') {
        try {
            // Получаем данные формы
            $fullName = $_POST['full_name'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $address = $_POST['address'] ?? '';

            if (empty($fullName) || empty($phone) || empty($address)) {
                throw new Exception('Все поля обязательны для заполнения');
            }

            // Получаем товары из корзины
            $stmt = $pdo->prepare("
                SELECT c.product_id, c.quantity, p.price, p.name
                FROM cart c
                JOIN products p ON c.product_id = p.id
                WHERE c.user_id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $cartItems = $stmt->fetchAll();

            if (empty($cartItems)) {
                throw new Exception('Корзина пуста');
            }

            // Считаем общую сумму
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
            }

            // Начинаем транзакцию
            $pdo->beginTransaction();

            // Создаем заказ
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, full_name, phone, address, total_amount)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $fullName, $phone, $address, $totalAmount]);
            $orderId = $pdo->lastInsertId();

            // Добавляем товары в заказ
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($cartItems as $item) {
                $stmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price']
                ]);
            }

            // Очищаем корзину
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);

            // Завершаем транзакцию
            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => "Заказ №{$orderId} успешно оформлен",
                'order_id' => $orderId
            ]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
?> 