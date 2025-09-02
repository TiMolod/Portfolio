<?php
require_once '../api/check_admin.php';
require_once '../config/db.php';

// Получаем все заказы с информацией о пользователях
$stmt = $pdo->query("
    SELECT 
        o.*,
        u.email as user_email,
        COUNT(oi.id) as items_count,
        GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, ' шт.)') SEPARATOR ', ') as items_list
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DoDep - Админ-панель</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">DoDep Admin</div>
            <ul class="nav-links">
                <li><a href="index.php" class="active">Заказы</a></li>
                <li><a href="products.php">Товары</a></li>
                <li><a href="../index.php">На сайт</a></li>
                <li><a href="#" id="logoutBtn">Выйти</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="admin-container">
            <h1>Управление заказами</h1>
            
            <div class="orders-list">
                <?php if (empty($orders)): ?>
                    <p class="empty-message">Заказов пока нет</p>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card admin-order" data-id="<?= $order['id'] ?>">
                            <div class="order-header">
                                <div class="order-info">
                                    <h4>Заказ №<?= $order['id'] ?></h4>
                                    <span class="order-date">
                                        <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?>
                                    </span>
                                    <span class="order-email"><?= htmlspecialchars($order['user_email']) ?></span>
                                </div>
                                <div class="order-status-control">
                                    <select class="status-select" onchange="updateOrderStatus(<?= $order['id'] ?>, this.value)">
                                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>
                                            Ожидает обработки
                                        </option>
                                        <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>
                                            В обработке
                                        </option>
                                        <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>
                                            Выполнен
                                        </option>
                                        <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>
                                            Отменён
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="order-details">
                                <div class="order-items">
                                    <strong>Состав заказа:</strong>
                                    <p><?= htmlspecialchars($order['items_list']) ?></p>
                                </div>
                                <div class="order-contact">
                                    <p><strong>Получатель:</strong> <?= htmlspecialchars($order['full_name']) ?></p>
                                    <p><strong>Телефон:</strong> <?= htmlspecialchars($order['phone']) ?></p>
                                    <p><strong>Адрес:</strong> <?= htmlspecialchars($order['address']) ?></p>
                                </div>
                                <div class="order-total">
                                    <strong>Сумма заказа:</strong>
                                    <span><?= number_format($order['total_amount'], 0, '', ' ') ?> ₽</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-left">
                <div class="footer-logo">DoDep</div>
                <p>Панель администратора</p>
            </div>
            <div class="footer-right">
                <a href="../api/make_admin.php" class="admin-link" target="_blank">Назначить права администратора</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 DoDep. Все права защищены</p>
        </div>
    </footer>

    <style>
    .footer-right {
        text-align: right;
    }
    .admin-link {
        color: #666;
        text-decoration: none;
        font-size: 0.9em;
        transition: color 0.3s;
    }
    .admin-link:hover {
        color: #007bff;
    }
    </style>

    <script>
    function updateOrderStatus(orderId, newStatus) {
        fetch('../api/admin.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update_status&order_id=${orderId}&status=${newStatus}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Статус заказа обновлен', 'success');
            } else {
                showNotification(data.error || 'Ошибка при обновлении статуса', 'error');
            }
        })
        .catch(() => {
            showNotification('Ошибка при обновлении статуса', 'error');
        });
    }

    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => notification.classList.add('show'), 100);
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Обработчик выхода
    document.getElementById('logoutBtn').addEventListener('click', function(e) {
        e.preventDefault();
        fetch('../api/auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=logout'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '../auth.html';
            }
        });
    });
    </script>
</body>
</html> 