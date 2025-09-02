// Глобальная переменная для хранения корзины
let cart;

// Глобальная функция для показа уведомлений
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    // Добавляем класс для анимации появления
    setTimeout(() => notification.classList.add('show'), 100);

    // Удаляем уведомление через 3 секунды
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Класс для управления корзиной
class Cart {
    constructor() {
        this.items = [];
        this.initializeEventListeners();
        this.loadCart();
    }

    // Загрузка корзины с сервера
    async loadCart() {
        try {
            const response = await fetch('/api/cart_api.php?action=get');
            const data = await response.json();
            
            if (data.success) {
                this.items = data.items;
                this.render();
            } else {
                console.error('Error loading cart:', data.error);
            }
        } catch (error) {
            console.error('Error loading cart:', error);
        }
    }

    // Обновление счётчика товаров
    updateCartCount() {
        const count = this.items.reduce((sum, item) => sum + item.quantity, 0);
        const cartCount = document.getElementById('cart-count');
        if (cartCount) {
            cartCount.textContent = count > 0 ? count : '';
        }
    }

    // Добавление товара
    async addItem(productId, quantity = 1) {
        try {
            const response = await fetch('/api/cart_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'add',
                    product_id: productId,
                    quantity: quantity
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.items = data.items;
                this.render();
                showNotification('Товар добавлен в корзину');
                return true;
            } else {
                console.error('Error adding item:', data.error);
                showNotification('Ошибка при добавлении товара', 'error');
                return false;
            }
        } catch (error) {
            console.error('Error adding item:', error);
            showNotification('Ошибка при добавлении товара', 'error');
            return false;
        }
    }

    // Удаление товара
    async removeItem(productId) {
        try {
            const response = await fetch('/api/cart_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'remove',
                    product_id: productId
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.items = data.items;
                this.render();
                showNotification('Товар удален из корзины');
            } else {
                console.error('Error removing item:', data.error);
                showNotification('Ошибка при удалении товара', 'error');
            }
        } catch (error) {
            console.error('Error removing item:', error);
            showNotification('Ошибка при удалении товара', 'error');
        }
    }

    // Изменение количества товара
    async updateQuantity(productId, delta) {
        const item = this.items.find(item => item.product_id == productId);
        if (!item) return;

        const newQuantity = Math.max(1, item.quantity + delta);

        try {
            const response = await fetch('/api/cart_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'update',
                    product_id: productId,
                    quantity: newQuantity
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.items = data.items;
                this.render();
                showNotification('Количество обновлено');
            } else {
                console.error('Error updating quantity:', data.error);
                showNotification('Ошибка при обновлении количества', 'error');
            }
        } catch (error) {
            console.error('Error updating quantity:', error);
            showNotification('Ошибка при обновлении количества', 'error');
        }
    }

    // Подсчёт общей суммы
    calculateTotal() {
        return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    }

    // Отрисовка корзины
    render() {
        console.log('Rendering cart with items:', this.items);
        const cartItems = document.getElementById('cart-items');
        if (!cartItems) {
            console.log('Cart container not found, might be on a different page');
            // Обновляем только счетчик, если мы не на странице корзины
            this.updateCartCount();
            return;
        }

        if (this.items.length === 0) {
            cartItems.innerHTML = '<p class="empty-message">Корзина пуста</p>';
            const checkoutBtn = document.getElementById('checkoutBtn');
            if (checkoutBtn) {
                checkoutBtn.disabled = true;
            }
            const cartTotal = document.getElementById('cart-total');
            if (cartTotal) {
                cartTotal.textContent = '0 ₽';
            }
            this.updateCartCount();
            return;
        }

        cartItems.innerHTML = this.items.map(item => `
            <div class="cart-item" data-product-id="${item.product_id}">
                <img src="${item.image || '/images/placeholder.jpg'}" alt="${item.name}" onerror="this.src='/images/placeholder.jpg'">
                <div class="cart-item-details">
                    <h3>${item.name}</h3>
                    <div class="cart-item-price">${parseInt(item.price).toLocaleString('ru-RU')} ₽</div>
                </div>
                <div class="cart-item-quantity">
                    <button class="quantity-btn minus" data-action="decrease">-</button>
                    <span>${item.quantity}</span>
                    <button class="quantity-btn plus" data-action="increase">+</button>
                </div>
                <div class="cart-item-total">
                    ${(item.price * item.quantity).toLocaleString('ru-RU')} ₽
                </div>
                <button class="remove-item" data-action="remove">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `).join('');

        const total = this.calculateTotal();
        const cartTotal = document.getElementById('cart-total');
        if (cartTotal) {
            cartTotal.textContent = `${total.toLocaleString('ru-RU')} ₽`;
        }

        const checkoutBtn = document.getElementById('checkoutBtn');
        if (checkoutBtn) {
            checkoutBtn.disabled = false;
        }

        this.updateCartCount();
    }

    // Инициализация обработчиков событий
    initializeEventListeners() {
        console.log('Initializing event listeners');
        
        // Обработка кликов по кнопкам в корзине
        const cartItems = document.getElementById('cart-items');
        if (cartItems) {
            cartItems.addEventListener('click', (e) => {
                const button = e.target.closest('button');
                if (!button) return;

                const cartItem = button.closest('.cart-item');
                if (!cartItem) return;

                const productId = parseInt(cartItem.dataset.productId);
                const action = button.dataset.action;

                console.log('Button clicked:', action, 'for product:', productId);

                if (action === 'remove') {
                    this.removeItem(productId);
                } else if (action === 'decrease') {
                    this.updateQuantity(productId, -1);
                } else if (action === 'increase') {
                    this.updateQuantity(productId, 1);
                }
            });
        }

        // Обработка кнопки оформления заказа
        const checkoutBtn = document.getElementById('checkoutBtn');
        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', () => {
                if (this.items.length === 0) {
                    showNotification('Корзина пуста', 'error');
                    return;
                }
                showNotification('Функция оформления заказа будет доступна позже', 'info');
            });
        }
    }
}

// Глобальные функции для работы с корзиной
function addToCart(productId, quantity = 1) {
    if (!cart) {
        cart = new Cart();
    }
    return cart.addItem(productId, quantity);
}

function updateCartQuantity(productId, quantity) {
    if (!cart) {
        cart = new Cart();
    }
    return cart.updateQuantity(productId, quantity);
}

function removeFromCart(productId) {
    if (!cart) {
        cart = new Cart();
    }
    return cart.removeItem(productId);
}

function loadCart() {
    if (!cart) {
        cart = new Cart();
    }
    return cart.loadCart();
}

// Инициализация корзины при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    cart = new Cart();
}); 