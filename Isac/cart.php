<?php
require_once 'api/check_auth.php';
require_once 'config/db.php';

// Получаем товары в корзине
$stmt = $pdo->prepare("
    SELECT c.id, c.quantity, p.name, p.price, p.image, (p.price * c.quantity) as total
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

// Считаем общую сумму
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['total'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>DoDep - Корзина</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">DoDep</div>
            <ul class="nav-links">
                <li><a href="index.php">Главная</a></li>
                <li><a href="cart.php" class="active">Корзина</a></li>
                <li><a href="profile.php">Профиль</a></li>
                <li><a href="#" id="logoutBtn">Выйти</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="cart-container">
            <h1>Корзина</h1>
            
            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Ваша корзина пуста</p>
                    <a href="index.php" class="btn-shop">Перейти к покупкам</a>
                </div>
            <?php else: ?>
                <div class="cart-layout">
                    <div class="cart-items">
                        <div class="cart-header">
                            <span class="header-product">Товар</span>
                            <span class="header-price">Цена</span>
                            <span class="header-quantity">Количество</span>
                            <span class="header-total">Сумма</span>
                            <span class="header-actions"></span>
                        </div>
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item" data-id="<?= $item['id'] ?>">
                                <div class="item-product">
                                    <div class="cart-item-image">
                                        <img src="<?= htmlspecialchars($item['image']) ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>" 
                                             onerror="this.src='images/placeholder.jpg'; this.onerror=null;">
                                    </div>
                                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                                </div>
                                <div class="item-price"><?= number_format($item['price'], 0, '', ' ') ?> ₽</div>
                                <div class="quantity-controls">
                                    <button class="quantity-btn minus" onclick="updateQuantity(<?= $item['id'] ?>, <?= $item['quantity'] ?> - 1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span class="quantity"><?= $item['quantity'] ?></span>
                                    <button class="quantity-btn plus" onclick="updateQuantity(<?= $item['id'] ?>, <?= $item['quantity'] ?> + 1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div class="item-total"><?= number_format($item['total'], 0, '', ' ') ?> ₽</div>
                                <div class="item-actions">
                                    <button class="remove-btn" onclick="removeFromCart(<?= $item['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="cart-summary">
                        <h3>Сводка заказа</h3>
                        <div class="summary-row">
                            <span>Товары (<?= count($cart_items) ?>):</span>
                            <span><?= number_format($total, 0, '', ' ') ?> ₽</span>
                        </div>
                        <div class="summary-total">
                            <span>Итого:</span>
                            <span><?= number_format($total, 0, '', ' ') ?> ₽</span>
                        </div>
                        <button class="btn-checkout" onclick="showCheckoutModal()">Оформить заказ</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Модальное окно оформления заказа -->
    <div id="checkoutModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeCheckoutModal()">&times;</span>
            <h2>Оформление заказа</h2>
            <form id="orderForm" class="checkout-form">
                <div class="form-group">
                    <label for="fullName">ФИО</label>
                    <input type="text" id="fullName" name="full_name" required>
                </div>
                <div class="form-group">
                    <label for="phone">Телефон</label>
                    <input type="tel" id="phone" name="phone" required pattern="[0-9+\s-()]{10,}" title="Введите корректный номер телефона">
                </div>
                <div class="form-group">
                    <label for="address">Адрес доставки</label>
                    <textarea id="address" name="address" required></textarea>
                </div>
                <button type="submit" class="btn-checkout">Подтвердить заказ</button>
            </form>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-left">
                <div class="footer-logo">DoDep</div>
                <p>Качественные товары для вашего комфорта</p>
            </div>
            <div class="footer-right">
                <div class="footer-social">
                    <a href="#" title="VK"><i class="fab fa-vk"></i></a>
                    <a href="#" title="Telegram"><i class="fab fa-telegram"></i></a>
                    <a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
                <div class="footer-contact">
                    <p>info@dodep.ru</p>
                    <p>+7 (900) 123-45-67</p>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 DoDep. Все права защищены</p>
        </div>
    </footer>

    <script>
    function removeFromCart(cartId) {
        fetch('api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=remove&cart_id=' + cartId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = document.querySelector(`.cart-item[data-id="${cartId}"]`);
                if (item) {
                    item.remove();
                }
                
                // Проверяем, остались ли товары
                const cartItems = document.querySelectorAll('.cart-item');
                if (cartItems.length === 0) {
                    location.reload(); // Перезагружаем только если корзина пуста
                }
                
                showNotification('Товар удален из корзины', 'success');
            } else {
                showNotification('Ошибка при удалении товара', 'error');
            }
        });
    }

    function updateQuantity(cartId, newQuantity) {
        if (newQuantity < 1) return;
        
        fetch('api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=update_quantity&cart_id=' + cartId + '&quantity=' + newQuantity
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Перезагружаем страницу для обновления всех сумм
            } else {
                showNotification('Ошибка при обновлении количества', 'error');
            }
        });
    }

    // Модальное окно
    function showCheckoutModal() {
        document.getElementById('checkoutModal').style.display = 'block';
    }

    function closeCheckoutModal() {
        document.getElementById('checkoutModal').style.display = 'none';
    }

    // Закрытие модального окна при клике вне его
    window.onclick = function(event) {
        const modal = document.getElementById('checkoutModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    // Обработка формы заказа
    document.getElementById('orderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'create_order');

        fetch('api/order.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => {
                    window.location.href = 'profile.php'; // Перенаправляем в профиль
                }, 2000);
            } else {
                showNotification(data.error, 'error');
            }
        })
        .catch(error => {
            showNotification('Произошла ошибка при оформлении заказа', 'error');
        });
    });

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
        fetch('api/auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=logout'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'auth.html';
            }
        });
    });
    </script>
</body>
</html> 