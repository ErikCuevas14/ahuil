document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const errorMessageDiv = document.getElementById('errorMessage');
    const loginBtn = document.getElementById('loginBtn');
    const registerBtn = document.getElementById('registerBtn');
    const logoutBtn = document.getElementById('logoutBtn');
    const authButtonsDiv = document.getElementById('authButtons');
    const userInfoDiv = document.getElementById('userInfo');
    const usernameDisplay = document.getElementById('usernameDisplay');

    const showErrorMessage = (message) => {
        if (errorMessageDiv) {
            errorMessageDiv.textContent = message;
            errorMessageDiv.style.display = 'block';
        }
    };

    const hideErrorMessage = () => {
        if (errorMessageDiv) {
            errorMessageDiv.textContent = '';
            errorMessageDiv.style.display = 'none';
        }
    };

    const updateAuthUI = (isLoggedIn, username = '') => {
        if (authButtonsDiv && userInfoDiv) {
            if (isLoggedIn) {
                authButtonsDiv.style.display = 'none';
                userInfoDiv.style.display = 'flex';
                if (usernameDisplay) {
                    usernameDisplay.textContent = username;
                }
            } else {
                authButtonsDiv.style.display = 'flex';
                userInfoDiv.style.display = 'none';
            }
        }
    };

    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            hideErrorMessage();

            const formData = new FormData(registerForm);
            formData.append('action', 'register');

            try {
                const response = await fetch('auth_process.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    window.location.href = 'index.php';
                } else {
                    showErrorMessage(data.message);
                }
            } catch (error) {
                console.error('Error en la solicitud de registro:', error);
                showErrorMessage('Ocurrió un error al intentar registrarse. Inténtalo de nuevo.');
            }
        });
    }

    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            hideErrorMessage();

            const formData = new FormData(loginForm);
            formData.append('action', 'login');

            try {
                const response = await fetch('auth_process.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    window.location.href = 'index.php';
                } else {
                    showErrorMessage(data.message);
                }
            } catch (error) {
                console.error('Error en la solicitud de login:', error);
                showErrorMessage('Ocurrió un error al intentar iniciar sesión. Inténtalo de nuevo.');
            }
        });
    }

    if (logoutBtn) {
        logoutBtn.addEventListener('click', async () => {
            try {
                const response = await fetch('auth_process.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=logout'
                });
                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    updateAuthUI(false);
                    window.location.href = 'index.php';
                } else {
                    alert('Error al cerrar sesión: ' + data.message);
                }
            } catch (error) {
                console.error('Error en la solicitud de logout:', error);
                alert('Ocurrió un error al intentar cerrar sesión.');
            }
        });
    }

    if (loginBtn) {
        loginBtn.addEventListener('click', () => {
            window.location.href = 'login.php';
        });
    }

    if (registerBtn) {
        registerBtn.addEventListener('click', () => {
            window.location.href = 'register.php';
        });
    }
});
