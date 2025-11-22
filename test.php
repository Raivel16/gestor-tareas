<?php
/**
 * Test file for debugging API responses
 * Open this file in browser to test PHP configuration
 */

// Test 1: Simple JSON output
header('Content-Type: application/json');
require_once __DIR__ . '/config/config.php';

echo json_encode([
    'test' => 'success',
    'php_version' => PHP_VERSION,
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'file_uploads' => ini_get('file_uploads') ? 'enabled' : 'disabled',
    'app_url' => APP_URL
]);
