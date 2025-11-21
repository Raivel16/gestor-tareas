// Auth.js - Manejo de Login y Signup

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');

    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }

    if (signupForm) {
        signupForm.addEventListener('submit', handleSignup);
    }
});

function handleLogin(e) {
    e.preventDefault();

    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();

    // Validación básica
    if (!email || !password) {
        alert('Por favor completa todos los campos');
        return;
    }

    if (password.length < 6) {
        alert('La contraseña debe tener al menos 6 caracteres');
        return;
    }

    // Guardar usuario en localStorage (simulado)
    const user = {
        email,
        loginTime: new Date().toISOString()
    };

    localStorage.setItem('currentUser', JSON.stringify(user));
    localStorage.setItem(`user_${email}`, JSON.stringify({
        email,
        password,
        createdAt: new Date().toISOString()
    }));

    // Redirigir al tablero
    window.location.href = 'index.html';
}

function handleSignup(e) {
    e.preventDefault();

    const fullname = document.getElementById('fullname').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const confirmPassword = document.getElementById('confirmPassword').value.trim();

    // Validación básica
    if (!fullname || !email || !password || !confirmPassword) {
        alert('Por favor completa todos los campos');
        return;
    }

    if (password.length < 6) {
        alert('La contraseña debe tener al menos 6 caracteres');
        return;
    }

    if (password !== confirmPassword) {
        alert('Las contraseñas no coinciden');
        return;
    }

    // Verificar si el email ya existe
    if (localStorage.getItem(`user_${email}`)) {
        alert('Este email ya está registrado');
        return;
    }

    // Guardar nuevo usuario
    const userData = {
        fullname,
        email,
        password,
        createdAt: new Date().toISOString()
    };

    localStorage.setItem(`user_${email}`, JSON.stringify(userData));
    localStorage.setItem('currentUser', JSON.stringify({ email, fullname }));

    alert('Cuenta creada exitosamente. ¡Bienvenido!');
    window.location.href = 'index.html';
}
