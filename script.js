// Elementos del DOM
const loginScreen = document.getElementById('loginScreen');
const welcomeScreen = document.getElementById('welcomeScreen');
const usernameInput = document.getElementById('username');
const passwordInput = document.getElementById('password');
const loginBtn = document.getElementById('loginBtn');
const logoutBtn = document.getElementById('logoutBtn');
const messageDiv = document.getElementById('message');
const welcomeMessage = document.getElementById('welcomeMessage');
const userName = document.getElementById('userName');
const userRole = document.getElementById('userRole');

// Estado de la aplicación
let isLoading = false;

// Función para mostrar mensajes
function showMessage(text, type) {
    messageDiv.textContent = text;
    messageDiv.className = `message ${type} show`;
}

// Función para ocultar mensajes
function hideMessage() {
    messageDiv.className = 'message';
}

// Función para mostrar spinner en el botón
function setLoading(loading) {
    isLoading = loading;
    if (loading) {
        loginBtn.disabled = true;
        loginBtn.innerHTML = '<div class="spinner"></div> Verificando...';
    } else {
        loginBtn.disabled = false;
        loginBtn.textContent = 'Iniciar Sesión';
    }
}

// Función principal de login
async function handleLogin() {
    const username = usernameInput.value.trim();
    const password = passwordInput.value.trim();

    if (!username || !password) {
        showMessage('Por favor completa todos los campos', 'error');
        return;
    }

    hideMessage();
    setLoading(true);

    try {
        // Llamada al PHP local
        const response = await fetch("http://localhost/PROYECTO_PRUEBA/login.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                username: username,
                password: password
            })
        });

        const result = await response.json();

        if (result.success) {
            // Login exitoso
            welcomeMessage.textContent = result.message;
            userName.textContent = result.user.name;
            userRole.textContent = result.user.role;
            
            // Cambiar a pantalla de bienvenida
            loginScreen.classList.remove('active');
            welcomeScreen.classList.add('active');
        } else {
            // Login fallido
            showMessage(result.message, 'error');
        }

    } catch (error) {
        console.error("Error:", error);
        showMessage("Error al conectar con el servidor. Verifica la consola.", 'error');
    } finally {
        setLoading(false);
    }
}

// Función de logout
function handleLogout() {
    // Limpiar campos
    usernameInput.value = '';
    passwordInput.value = '';
    hideMessage();
    
    // Volver a pantalla de login
    welcomeScreen.classList.remove('active');
    loginScreen.classList.add('active');
}

// Event Listeners
loginBtn.addEventListener('click', handleLogin);
logoutBtn.addEventListener('click', handleLogout);

// Permitir login con Enter
usernameInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter' && !isLoading) handleLogin();
});

passwordInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter' && !isLoading) handleLogin();
});
