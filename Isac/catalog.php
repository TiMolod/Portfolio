<?php
require_once 'api/check_auth.php';
require_once 'config/db.php';

// Получаем все товары
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>DoDep - Магазин</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">DoDep</div>
            <ul class="nav-links">
                <li><a href="catalog.php" class="active">Каталог</a></li>
                <li><a href="cart.php">Корзина</a></li>
                <li><a href="profile.php">Профиль</a></li>
                <li><a href="#" id="logoutBtn">Выйти</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="products">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?= htmlspecialchars($product['image']) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             onerror="this.src='images/placeholder.jpg'; this.onerror=null;">
                    </div>
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="description"><?= htmlspecialchars($product['description']) ?></p>
                    <p class="price"><?= number_format($product['price'], 0, '', ' ') ?> ₽</p>
                    <button onclick="addToCart(<?= $product['id'] ?>)">В корзину</button>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

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
    function addToCart(productId) {
        fetch('api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=add&product_id=' + productId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Товар добавлен в корзину', 'success');
            } else {
                showNotification('Ошибка при добавлении товара', 'error');
            }
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