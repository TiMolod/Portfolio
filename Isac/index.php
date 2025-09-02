<?php
session_start();

// Если пользователь авторизован, перенаправляем на catalog.php
if (isset($_SESSION['user_id'])) {
    header('Location: catalog.php');
    exit;
}

require_once 'config/db.php';

// Получаем все товары
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DoDep - Магазин</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">DoDep</div>
            <ul class="nav-links">
                <li><a href="index.php" class="active">Главная</a></li>
                <li><a href="auth.html">Войти</a></li>
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
                    <button onclick="requireAuth()">В корзину</button>
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
    function requireAuth() {
        showNotification('Пожалуйста, авторизуйтесь для добавления товаров в корзину', 'info');
        setTimeout(() => {
            window.location.href = 'auth.html';
        }, 2000);
    }

    function showNotification(message, type = 'info') {
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
    </script>
</body>
</html> 