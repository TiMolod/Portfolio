document.addEventListener('DOMContentLoaded', function() {
    // Переключение между формами входа и регистрации
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const authTabs = document.querySelectorAll('.auth-tab');

    authTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabType = this.dataset.tab;
            
            // Активируем нужную вкладку
            authTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Показываем нужную форму
            if (tabType === 'login') {
                loginForm.classList.remove('hidden');
                registerForm.classList.add('hidden');
            } else {
                loginForm.classList.add('hidden');
                registerForm.classList.remove('hidden');
            }
        });
    });

    // Обработка входа
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'login');

        fetch('api/auth.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'catalog.php';
            } else {
                showNotification(data.error || 'Ошибка при входе', 'error');
            }
        })
        .catch(error => {
            showNotification('Ошибка при входе', 'error');
        });
    });

    // Обработка регистрации
    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const password = this.querySelector('#registerPassword').value;
        const passwordConfirm = this.querySelector('#registerPasswordConfirm').value;

        if (password !== passwordConfirm) {
            showNotification('Пароли не совпадают', 'error');
            return;
        }

        const formData = new FormData(this);
        formData.append('action', 'register');

        fetch('api/auth.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'catalog.php';
            } else {
                showNotification(data.error || 'Ошибка при регистрации', 'error');
            }
        })
        .catch(error => {
            showNotification('Ошибка при регистрации', 'error');
        });
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