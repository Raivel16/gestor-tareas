<?php
require_once __DIR__ . '/includes/functions.php';

iniciarSesion();

// Redirigir a index si ya está autenticado
if (estaAutenticado()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskBoard - Iniciar Sesión</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/notifications.css">
</head>
<body class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="logo">
                        <svg width="40" height="40" viewBox="0 0 32 32" fill="none">
                            <rect width="32" height="32" rx="6" fill="#2563eb"/>
                            <path d="M8 10h8v8H8V10zm10 0h8v8h-8V10z" fill="white"/>
                        </svg>
                        <h1>Gestor de Tareas</h1>
                    </div>
                    <p class="subtitle">Organiza tus tareas de forma visual</p>
                </div>

                <form id="loginForm" class="auth-form">
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input 
                            type="email" 
                            id="email" 
                            required
                            placeholder="tu@email.com"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input 
                            type="password" 
                            id="password" 
                            required
                            placeholder="••••••••"
                        >
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">Iniciar Sesión</button>
                </form>

                <div class="form-divider">
                    <span>o</span>
                </div>

                <button type="button" class="btn btn-secondary btn-full" onclick="window.location.href='signup.php'">
                    Crear Nueva Cuenta
                </button>

            </div>

            <div class="auth-illustration">
                <div class="illustration-shape"></div>
            </div>
        </div>
    </div>

    <script src="js/notifications.js"></script>
    <script src="auth.js"></script>
</body>
</html>
