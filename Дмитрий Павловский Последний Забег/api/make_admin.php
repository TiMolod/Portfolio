<?php
require_once 'check_admin.php';
require_once '../config/db.php';

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    try {
        $email = $_POST['email'];
        
        // Проверяем существование пользователя
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Назначаем права администратора
            $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE email = ?");
            $stmt->execute([$email]);
            $message = "Права администратора успешно назначены пользователю {$email}";
            $success = true;
        } else {
            $message = "Пользователь с email {$email} не найден";
            $success = false;
        }
    } catch (PDOException $e) {
        $message = "Ошибка: " . $e->getMessage();
        $success = false;
    }
}

// Получаем список всех пользователей
$users = $pdo->query("SELECT email, role FROM users ORDER BY email")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление правами администратора</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .admin-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .user-list {
            margin-top: 20px;
        }
        .user-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .user-item:last-child {
            border-bottom: none;
        }
        .admin-badge {
            background: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
        }
        .admin-form {
            margin-bottom: 20px;
        }
        .admin-form input[type="email"] {
            width: 300px;
            padding: 8px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1>Управление правами администратора</h1>
        
        <?php if (isset($message)): ?>
            <div class="message <?= $success ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="admin-form">
            <input type="email" name="email" placeholder="Email пользователя" required>
            <button type="submit" class="btn">Назначить администратором</button>
        </form>

        <div class="user-list">
            <h2>Список пользователей</h2>
            <?php foreach ($users as $user): ?>
                <div class="user-item">
                    <span><?= htmlspecialchars($user['email']) ?></span>
                    <?php if ($user['role'] === 'admin'): ?>
                        <span class="admin-badge">Администратор</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <p style="margin-top: 20px;">
            <a href="../admin/index.php">&larr; Вернуться в панель администратора</a>
        </p>
    </div>
</body>
</html> 