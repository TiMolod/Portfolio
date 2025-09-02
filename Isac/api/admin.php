<?php
require_once 'check_admin.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Обновление статуса заказа
    if ($action === 'update_status') {
        try {
            $orderId = $_POST['order_id'] ?? '';
            $status = $_POST['status'] ?? '';

            if (empty($orderId) || empty($status)) {
                throw new Exception('Не указан ID заказа или статус');
            }

            // Проверяем допустимые значения статуса
            $allowedStatuses = ['pending', 'processing', 'completed', 'cancelled'];
            if (!in_array($status, $allowedStatuses)) {
                throw new Exception('Недопустимый статус заказа');
            }

            // Обновляем статус заказа
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $orderId]);

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Статус заказа обновлен'
                ]);
            } else {
                throw new Exception('Заказ не найден');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    // Добавление нового товара
    else if ($action === 'add_product') {
        try {
            $name = $_POST['name'] ?? '';
            $price = $_POST['price'] ?? '';
            $description = $_POST['description'] ?? '';
            $image = $_POST['image'] ?? '';

            if (empty($name) || empty($price) || empty($description)) {
                throw new Exception('Название, цена и описание обязательны для заполнения');
            }

            // Если URL изображения не указан, используем placeholder
            if (empty($image)) {
                $image = '../images/placeholder.jpg';
            }

            $stmt = $pdo->prepare("
                INSERT INTO products (name, price, description, image)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$name, $price, $description, $image]);

            echo json_encode([
                'success' => true,
                'message' => 'Товар успешно добавлен',
                'product_id' => $pdo->lastInsertId()
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    // Обновление товара
    else if ($action === 'update_product') {
        try {
            $id = $_POST['id'] ?? '';
            $name = $_POST['name'] ?? '';
            $price = $_POST['price'] ?? '';
            $description = $_POST['description'] ?? '';
            $image = $_POST['image'] ?? '';

            if (empty($id) || empty($name) || empty($price) || empty($description)) {
                throw new Exception('ID, название, цена и описание обязательны для заполнения');
            }

            // Если URL изображения не указан, используем placeholder
            if (empty($image)) {
                $image = '../images/placeholder.jpg';
            }

            $stmt = $pdo->prepare("
                UPDATE products 
                SET name = ?, price = ?, description = ?, image = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $price, $description, $image, $id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Товар успешно обновлен'
                ]);
            } else {
                throw new Exception('Товар не найден');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    // Удаление товара
    else if ($action === 'delete_product') {
        try {
            $id = $_POST['id'] ?? '';

            if (empty($id)) {
                throw new Exception('Не указан ID товара');
            }

            // Проверяем, есть ли товар в заказах
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM order_items WHERE product_id = ?
            ");
            $stmt->execute([$id]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                throw new Exception('Невозможно удалить товар, так как он есть в заказах');
            }

            // Удаляем товар из корзины
            $stmt = $pdo->prepare("DELETE FROM cart WHERE product_id = ?");
            $stmt->execute([$id]);

            // Удаляем сам товар
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Товар успешно удален'
                ]);
            } else {
                throw new Exception('Товар не найден');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}

// GET запросы
else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    // Получение данных товара для редактирования
    if ($action === 'get_product') {
        try {
            $id = $_GET['id'] ?? '';

            if (empty($id)) {
                throw new Exception('Не указан ID товара');
            }

            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch();

            if ($product) {
                echo json_encode([
                    'success' => true,
                    'product' => $product
                ]);
            } else {
                throw new Exception('Товар не найден');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
} 