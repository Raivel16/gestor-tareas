<?php
/**
 * Authentication API
 * Manejo de registro, login y logout
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

iniciarSesion();
header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'register':
        handleRegister();
        break;
    
    case 'login':
        handleLogin();
        break;
    
    case 'logout':
        handleLogout();
        break;
    
    case 'check':
        handleCheck();
        break;
    
    default:
        respuestaJSON(false, 'Acción no válida');
}

function handleRegister() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respuestaJSON(false, 'Método no permitido');
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $nombre_completo = sanitizar($data['name'] ?? $data['fullname'] ?? '');
    $email = sanitizar($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $confirmPassword = $data['confirmPassword'] ?? '';
    
    // Validaciones
    if (empty($nombre_completo) || empty($email) || empty($password) || empty($confirmPassword)) {
        respuestaJSON(false, 'Todos los campos son requeridos');
    }
    
    if (!validarEmail($email)) {
        respuestaJSON(false, 'Email inválido');
    }
    
    if (!validarPassword($password)) {
        respuestaJSON(false, 'La contraseña debe tener al menos 6 caracteres');
    }
    
    if ($password !== $confirmPassword) {
        respuestaJSON(false, 'Las contraseñas no coinciden');
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Verificar si el email ya existe
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            respuestaJSON(false, 'Este email ya está registrado');
        }
        
        // Crear nuevo usuario
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO usuarios (nombre_completo, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$nombre_completo, $email, $passwordHash]);
        
        $usuario_id = $db->lastInsertId();
        
        // Iniciar sesión automáticamente
        $_SESSION['usuario_id'] = $usuario_id;
        $_SESSION['nombre_completo'] = $nombre_completo;
        $_SESSION['email'] = $email;
        
        respuestaJSON(true, 'Cuenta creada exitosamente', [
            'usuario_id' => $usuario_id,
            'nombre_completo' => $nombre_completo,
            'email' => $email
        ]);
        
    } catch(PDOException $e) {
        error_log("Error en registro: " . $e->getMessage());
        respuestaJSON(false, 'Error al crear la cuenta');
    }
}

function handleLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respuestaJSON(false, 'Método no permitido');
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $email = sanitizar($data['email'] ?? '');
    $password = $data['password'] ?? '';
    
    // Validaciones
    if (empty($email) || empty($password)) {
        respuestaJSON(false, 'Todos los campos son requeridos');
    }
    
    if (!validarEmail($email)) {
        respuestaJSON(false, 'Email inválido');
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Buscar usuario
        $stmt = $db->prepare("SELECT id, nombre_completo, email, password FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        
        if (!$usuario || !password_verify($password, $usuario['password'])) {
            respuestaJSON(false, 'Credenciales incorrectas');
        }
        
        // Iniciar sesión
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre_completo'] = $usuario['nombre_completo'];
        $_SESSION['email'] = $usuario['email'];
        
        respuestaJSON(true, 'Login exitoso', [
            'usuario_id' => $usuario['id'],
            'nombre_completo' => $usuario['nombre_completo'],
            'email' => $usuario['email']
        ]);
        
    } catch(PDOException $e) {
        error_log("Error en login: " . $e->getMessage());
        respuestaJSON(false, 'Error al iniciar sesión');
    }
}

function handleLogout() {
    session_destroy();
    respuestaJSON(true, 'Sesión cerrada');
}

function handleCheck() {
    if (estaAutenticado()) {
        respuestaJSON(true, 'Autenticado', [
            'usuario_id' => $_SESSION['usuario_id'],
            'nombre_completo' => $_SESSION['nombre_completo'] ?? '',
            'email' => $_SESSION['email'] ?? ''
        ]);
    } else {
        respuestaJSON(false, 'No autenticado');
    }
}
