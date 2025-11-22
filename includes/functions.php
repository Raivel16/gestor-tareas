<?php
/**
 * Helper Functions
 * Funciones auxiliares para el sistema
 */

// Iniciar sesión si no está iniciada
function iniciarSesion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Verificar si el usuario está autenticado
function estaAutenticado() {
    iniciarSesion();
    return isset($_SESSION['usuario_id']);
}

// Obtener ID del usuario actual
function obtenerUsuarioId() {
    iniciarSesion();
    return $_SESSION['usuario_id'] ?? null;
}

// Validar email
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Sanitizar entrada
function sanitizar($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Respuesta JSON
function respuestaJSON($success, $message = '', $data = null) {
    header('Content-Type: application/json');
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}

// Validar contraseña (mínimo 6 caracteres)
function validarPassword($password) {
    return strlen($password) >= 6;
}

// Algoritmo de ordenamiento por IA (simple)
// Ordena por fecha límite ASC, luego por prioridad DESC
function sugerirOrdenTareas($tareas) {
    if (empty($tareas)) {
        return [];
    }
    
    // Mapear prioridades a valores numéricos
    $prioridades = [
        'high' => 3,
        'medium' => 2,
        'low' => 1
    ];
    
    usort($tareas, function($a, $b) use ($prioridades) {
        // Primero ordenar por fecha límite (más próximo primero)
        if ($a['fecha_limite'] && $b['fecha_limite']) {
            $fechaComparacion = strtotime($a['fecha_limite']) - strtotime($b['fecha_limite']);
            if ($fechaComparacion !== 0) {
                return $fechaComparacion;
            }
        } elseif ($a['fecha_limite']) {
            return -1; // Tareas con fecha primero
        } elseif ($b['fecha_limite']) {
            return 1;
        }
        
        // Luego ordenar por prioridad (mayor prioridad primero)
        $prioridadA = $prioridades[$a['prioridad']] ?? 0;
        $prioridadB = $prioridades[$b['prioridad']] ?? 0;
        
        return $prioridadB - $prioridadA;
    });
    
    // Retornar solo los IDs en orden
    return array_column($tareas, 'id');
}

// Requerir autenticación
function requerirAutenticacion() {
    if (!estaAutenticado()) {
        respuestaJSON(false, 'No autenticado. Por favor inicia sesión.');
    }
}
