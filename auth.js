// Auth.js - Authentication con Notificaciones Profesionales

// Login Handler
async function handleLogin(event) {
  event.preventDefault();

  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;

  // Validación
  if (!email || !password) {
    showToast("Por favor completa todos los campos", "warning");
    return;
  }

  if (password.length < 6) {
    showToast("La contraseña debe tener al menos 6 caracteres", "warning");
    return;
  }

  // Mostrar loading
  showLoading("Iniciando sesión...");

  try {
    const response = await fetch("api/auth.php?action=login", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ email, password }),
    });

    const result = await response.json();

    hideLoading();

    if (result.success) {
      showToast("¡Bienvenido!", "success", 1500);
      setTimeout(() => {
        window.location.href = "index.php";
      }, 1500);
    } else {
      showToast(result.message || "Error al iniciar sesión", "error");
    }
  } catch (error) {
    hideLoading();
    console.error("Error:", error);
    showModal(
      "Error de Conexión",
      "No se pudo conectar con el servidor. Por favor intenta nuevamente.",
      "error"
    );
  }
}

// Signup Handler
async function handleSignup(event) {
  event.preventDefault();

  const name = document.getElementById("fullname").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;
  const confirmPassword = document.getElementById("confirmPassword").value;

  // Validación
  if (!name || !email || !password || !confirmPassword) {
    showToast("Por favor completa todos los campos", "warning");
    return;
  }

  if (password.length < 6) {
    showToast("La contraseña debe tener al menos 6 caracteres", "warning");
    return;
  }

  if (password !== confirmPassword) {
    showToast("Las contraseñas no coinciden", "error");
    return;
  }

  // Mostrar loading
  showLoading("Creando tu cuenta...");

  try {
    const response = await fetch("api/auth.php?action=register", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        name,
        email,
        password,
        confirmPassword,
      }),
    });

    const result = await response.json();

    hideLoading();

    if (result.success) {
      showModal(
        "¡Cuenta Creada!",
        "Tu cuenta ha sido creada exitosamente. ¡Bienvenido!",
        "success",
        () => {
          window.location.href = "index.php";
        }
      );
    } else {
      showToast(result.message || "Error al crear la cuenta", "error");
    }
  } catch (error) {
    hideLoading();
    console.error("Error:", error);
    showModal(
      "Error de Conexión",
      "No se pudo conectar con el servidor. Por favor intenta nuevamente.",
      "error"
    );
  }
}

// Setup Form Listeners
document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", handleLogin);
  }

  const signupForm = document.getElementById("signupForm");
  if (signupForm) {
    signupForm.addEventListener("submit", handleSignup);
  }
});
