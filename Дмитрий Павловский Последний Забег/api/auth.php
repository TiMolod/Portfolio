<?php
require_once '../config/db.php';

header('Content-Type: application/json');

// Обработка запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'login':
            // Логика входа
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Проверяем пользователя в базе
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                echo json_encode(['success' => true]);
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Неверный email или пароль']);
            }
            break;

        case 'register':
            // Логика регистрации
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Проверяем, не занят ли email
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Email уже занят']);
                exit;
            }

            // Хешируем пароль и создаем пользователя
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            
            try {
                $stmt->execute([$name, $email, $hashedPassword]);
                session_start();
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_email'] = $email;
                echo json_encode(['success' => true]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Ошибка при регистрации']);
            }
            break;

        case 'logout':
            // Логика выхода
            session_start();
            session_destroy();
            echo json_encode(['success' => true]);
            break;

        case 'check':
            // Проверка авторизации
            session_start();
            echo json_encode([
                'success' => true,
                'isAuthenticated' => isset($_SESSION['user_id']),
                'user' => isset($_SESSION['user_id']) ? [
                    'id' => $_SESSION['user_id'],
                    'email' => $_SESSION['user_email']
                ] : null
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Неизвестное действие']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Метод не поддерживается']);
} 