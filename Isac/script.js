// Глобальная переменная для хранения товаров
let products = [];

async function fetchProducts() {
    try {
        const response = await fetch('api/get_products.php');
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        console.log('Received products:', data);
        
        // Сохраняем товары глобально
        products = data;
        return data;
    } catch (error) {
        console.error('Error fetching products:', error);
        return [];
    }
}

function createProductCard(product) {
    console.log('Creating card for product:', product);
    
    const card = document.createElement('div');
    card.className = 'catalog-item';
    
    // Создаем изображение отдельно для добавления обработчика ошибки
    const img = document.createElement('img');
    img.src = product.image || 'images/placeholder.jpg';
    img.alt = product.name;
    img.onerror = function() {
        this.src = 'images/placeholder.jpg';
        this.onerror = null; // Предотвращаем зацикливание
    };

    // Создаем остальной HTML
    const productContent = `
        <h3>${product.name}</h3>
        <p>${product.description}</p>
        <div class="price">${parseInt(product.price).toLocaleString('ru-RU')} ₽</div>
        <button class="add-to-cart" data-product-id="${product.id || product.product_id}">
            <i class="fas fa-shopping-cart"></i>
            Добавить
        </button>
    `;

    // Добавляем изображение и контент в карточку
    card.appendChild(img);
    card.insertAdjacentHTML('beforeend', productContent);

    // Добавляем обработчик на кнопку
    const addButton = card.querySelector('.add-to-cart');
    addButton.addEventListener('click', () => {
        // Используем функцию addToCart из cart.js
        const productId = addButton.dataset.productId;
        console.log('Adding product to cart:', productId);
        addToCart(productId, 1);
        
        // Анимация кнопки
        addButton.classList.add('added');
        setTimeout(() => addButton.classList.remove('added'), 1000);
    });

    return card;
}

async function renderCatalog() {
    const catalogList = document.getElementById('catalog-list');
    if (!catalogList) return;
    
    // Очищаем список перед добавлением
    catalogList.innerHTML = '<div class="loading">Загрузка товаров...</div>';

    // Получаем товары
    const products = await fetchProducts();
    
    // Снова очищаем для добавления товаров
    catalogList.innerHTML = '';
    
    if (products.length === 0) {
        catalogList.innerHTML = '<p class="no-products">Товары не найдены</p>';
        return;
    }

    // Создаем и добавляем карточки товаров
    products.forEach(product => {
        const card = createProductCard(product);
        catalogList.appendChild(card);
    });
}

// Основная инициализация
document.addEventListener('DOMContentLoaded', () => {
    // Загружаем каталог
    renderCatalog();

    // Загружаем корзину
    loadCart();

    // Обработчик кнопки выхода
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            fetch('api/logout.php')
                .then(() => window.location.reload())
                .catch(error => console.error('Error:', error));
        });
    }
});
