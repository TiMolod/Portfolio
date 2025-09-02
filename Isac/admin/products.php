<?php
require_once '../api/check_admin.php';
require_once '../config/db.php';

// Получаем все товары
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DoDep - Управление товарами</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">DoDep Admin</div>
            <ul class="nav-links">
                <li><a href="index.php">Заказы</a></li>
                <li><a href="products.php" class="active">Товары</a></li>
                <li><a href="../index.php">На сайт</a></li>
                <li><a href="#" id="logoutBtn">Выйти</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="admin-container">
            <div class="products-header">
                <h1>Управление товарами</h1>
                <button class="btn-add-product" onclick="showProductModal()">
                    <i class="fas fa-plus"></i> Добавить товар
                </button>
            </div>
            
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card" data-id="<?= $product['id'] ?>">
                        <div class="product-image">
                            <img src="<?= htmlspecialchars($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                 onerror="this.onerror=null; this.src='../images/placeholder.jpg';">
                            <div class="product-actions">
                                <button class="edit-btn" onclick="editProduct(<?= $product['id'] ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="delete-btn" onclick="deleteProduct(<?= $product['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="product-price"><?= number_format($product['price'], 0, '', ' ') ?> ₽</p>
                            <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <!-- Модальное окно для добавления/редактирования товара -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeProductModal()">&times;</span>
            <h2 id="modalTitle">Добавить товар</h2>
            <form id="productForm" class="product-form">
                <input type="hidden" id="productId" name="id">
                <div class="form-group">
                    <label for="productName">Название товара</label>
                    <input type="text" id="productName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="productPrice">Цена (₽)</label>
                    <input type="number" id="productPrice" name="price" min="0" required>
                </div>
                <div class="form-group">
                    <label for="productDescription">Описание</label>
                    <textarea id="productDescription" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="productImage">URL изображения</label>
                    <input type="text" id="productImage" name="image" required>
                </div>
                <button type="submit" class="btn-submit">Сохранить</button>
            </form>
        </div>
    </div>

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
    let currentProductId = null;

    function showProductModal(productId = null) {
        const modal = document.getElementById('productModal');
        const form = document.getElementById('productForm');
        const title = document.getElementById('modalTitle');
        
        currentProductId = productId;
        
        if (productId) {
            title.textContent = 'Редактировать товар';
            // Загружаем данные товара
            fetch(`../api/admin.php?action=get_product&id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const product = data.product;
                        document.getElementById('productId').value = product.id;
                        document.getElementById('productName').value = product.name;
                        document.getElementById('productPrice').value = product.price;
                        document.getElementById('productDescription').value = product.description;
                        document.getElementById('productImage').value = product.image;
                    }
                });
        } else {
            title.textContent = 'Добавить товар';
            form.reset();
            document.getElementById('productId').value = '';
        }
        
        modal.style.display = 'block';
    }

    function closeProductModal() {
        document.getElementById('productModal').style.display = 'none';
    }

    function editProduct(id) {
        showProductModal(id);
    }

    function deleteProduct(id) {
        if (confirm('Вы уверены, что хотите удалить этот товар?')) {
            fetch('../api/admin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete_product&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const productCard = document.querySelector(`.product-card[data-id="${id}"]`);
                    if (productCard) {
                        productCard.remove();
                    }
                    showNotification('Товар успешно удален', 'success');
                } else {
                    showNotification(data.error || 'Ошибка при удалении товара', 'error');
                }
            });
        }
    }

    document.getElementById('productForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', currentProductId ? 'update_product' : 'add_product');
        
        fetch('../api/admin.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(
                    currentProductId ? 'Товар обновлен' : 'Товар добавлен', 
                    'success'
                );
                
                // Создаем новый товар для отображения
                const newProduct = {
                    id: currentProductId || data.product_id,
                    name: formData.get('name'),
                    price: formData.get('price'),
                    description: formData.get('description'),
                    image: formData.get('image')
                };

                if (currentProductId) {
                    // Обновляем существующую карточку товара
                    const productCard = document.querySelector(`.product-card[data-id="${currentProductId}"]`);
                    if (productCard) {
                        productCard.querySelector('h3').textContent = newProduct.name;
                        productCard.querySelector('.product-price').textContent = 
                            Number(newProduct.price).toLocaleString('ru-RU') + ' ₽';
                        productCard.querySelector('.product-description').textContent = newProduct.description;
                        const img = productCard.querySelector('img');
                        img.src = newProduct.image;
                        img.alt = newProduct.name;
                    }
                } else {
                    // Создаем новую карточку товара
                    const productsGrid = document.querySelector('.products-grid');
                    const newCard = document.createElement('div');
                    newCard.className = 'product-card';
                    newCard.setAttribute('data-id', newProduct.id);
                    newCard.innerHTML = `
                        <div class="product-image">
                            <img src="${newProduct.image}" 
                                 alt="${newProduct.name}"
                                 onerror="this.onerror=null; this.src='../images/placeholder.jpg';">
                            <div class="product-actions">
                                <button class="edit-btn" onclick="editProduct(${newProduct.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="delete-btn" onclick="deleteProduct(${newProduct.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3>${newProduct.name}</h3>
                            <p class="product-price">${Number(newProduct.price).toLocaleString('ru-RU')} ₽</p>
                            <p class="product-description">${newProduct.description}</p>
                        </div>
                    `;
                    productsGrid.insertBefore(newCard, productsGrid.firstChild);
                }
                
                // Закрываем модальное окно
                closeProductModal();
            } else {
                showNotification(data.error || 'Ошибка при сохранении товара', 'error');
            }
        });
    });

    // Закрытие модального окна при клике вне его
    window.onclick = function(event) {
        const modal = document.getElementById('productModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
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