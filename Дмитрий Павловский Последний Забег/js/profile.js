document.addEventListener('DOMContentLoaded', async () => {
    // Проверка авторизации
    try {
        const response = await fetch('api/check_auth.php');
        const data = await response.json();
        
        if (!data.authenticated) {
            window.location.href = 'auth.html';
            return;
        }
        
        // Заполняем информацию о пользователе
        document.getElementById('userName').textContent = data.user.name;
        document.getElementById('userEmail').textContent = data.user.email;
        document.getElementById('updateName').value = data.user.name;
    } catch (error) {
        console.error('Error:', error);
        window.location.href = 'auth.html';
    }

    // Обработка выхода
    document.getElementById('logoutBtn').addEventListener('click', async (e) => {
        e.preventDefault();
        try {
            await fetch('api/logout.php');
            window.location.href = 'auth.html';
        } catch (error) {
            console.error('Error:', error);
        }
    });

    // Обработка формы обновления профиля
    const profileForm = document.getElementById('profileForm');
    profileForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const password = profileForm.querySelector('#updatePassword').value;
        const passwordConfirm = profileForm.querySelector('#updatePasswordConfirm').value;
        
        if (password && password !== passwordConfirm) {
            alert('Пароли не совпадают');
            return;
        }
        
        const formData = new FormData(profileForm);
        try {
            const response = await fetch('api/update_profile.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                alert('Профиль успешно обновлен');
                // Обновляем отображаемое имя
                document.getElementById('userName').textContent = formData.get('name');
            } else {
                alert(data.error || 'Ошибка обновления профиля');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Произошла ошибка при обновлении профиля');
        }
    });
}); 