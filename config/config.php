<?php
/**
 * Configuration File
 * Configuración general de la aplicación
 */

// Groq AI Configuration
define('GROQ_API_KEY', 'YOUR API KEY');
define('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions');
define('GROQ_MODEL', 'llama-3.3-70b-versatile');

// Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('UPLOAD_ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Application Configuration
define('APP_URL', getAppUrl());

/**
 * Detecta la URL base de la aplicación automáticamente
 */
function getAppUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    
    // Limpiar la ruta
    $path = str_replace('\\', '/', $path);
    
    // Si estamos en un subdirectorio (api, config), subir un nivel
    $path = str_replace('/api', '', $path);
    $path = str_replace('/config', '', $path);
    
    $path = rtrim($path, '/');
    
    return $protocol . '://' . $host . $path;
}
