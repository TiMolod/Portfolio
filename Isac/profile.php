<?php
require_once 'api/check_auth.php';
require_once 'config/db.php';

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Получаем историю заказов
$stmt = $pdo->prepare("
    SELECT o.*, 
           COUNT(oi.id) as items_count,
           GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, ' шт.)') SEPARATOR ', ') as items_list
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DoDep - Личный кабинет</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">DoDep</div>
            <ul class="nav-links">
                <li><a href="index.php">Главная</a></li>
                <li><a href="cart.php">Корзина</a></li>
                <li><a href="profile.php" class="active">Профиль</a></li>
                <li><a href="#" id="logoutBtn">Выйти</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="profile-container">
            <div class="profile-header">
                <h1>Личный кабинет</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <h2><?= htmlspecialchars($user['name']) ?></h2>
                        <p><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                </div>
            </div>
                <div class="profile-section">
                    <h3>Настройки профиля</h3>
                    <form id="profileForm" class="profile-form">
                        <div class="form-group">
                            <label for="updateName">Имя</label>
                            <input type="text" id="updateName" name="name" value="<?= htmlspecialchars($user['name']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="updatePassword">Новый пароль</label>
                            <input type="password" id="updatePassword" name="password">
                        </div>
                        <div class="form-group">
                            <label for="updatePasswordConfirm">Подтвердите пароль</label>
                            <input type="password" id="updatePasswordConfirm" name="password_confirm">
                        </div>
                        <button type="submit" class="btn-profile">Сохранить изменения</button>
                    </form>
                </div>
            </div>
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

    // Обработчик формы профиля
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const password = document.getElementById('updatePassword').value;
        const passwordConfirm = document.getElementById('updatePasswordConfirm').value;

        if (password && password !== passwordConfirm) {
            showNotification('Пароли не совпадают', 'error');
            return;
        }

        const formData = new FormData(this);
        formData.append('action', 'update_profile');

        fetch('api/profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Профиль обновлен', 'success');
                if (data.reload) {
                    location.reload();
                }
            } else {
                showNotification(data.error || 'Ошибка при обновлении профиля', 'error');
            }
        })
        .catch(() => {
            showNotification('Ошибка при обновлении профиля', 'error');
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
    </script>
</body>
</html> 