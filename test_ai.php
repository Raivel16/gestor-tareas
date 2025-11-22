<?php
// test_ai.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'includes/AIService.php';

// Mock tasks
$tareas = [
    [
        'id' => 1,
        'titulo' => 'Fix login bug',
        'descripcion' => 'Users cannot log in with special characters',
        'fecha_limite' => date('Y-m-d', strtotime('+1 day')),
        'prioridad' => 'high',
        'curso' => 'Backend'
    ],
    [
        'id' => 2,
        'titulo' => 'Update documentation',
        'descripcion' => 'Add new features to README',
        'fecha_limite' => date('Y-m-d', strtotime('+5 days')),
        'prioridad' => 'low',
        'curso' => 'Docs'
    ]
];

echo "Testing AI Service...\n";
echo "API Key: " . (defined('GROQ_API_KEY') ? substr(GROQ_API_KEY, 0, 5) . '...' : 'NOT DEFINED') . "\n";
echo "Model: " . (defined('GROQ_MODEL') ? GROQ_MODEL : 'NOT DEFINED') . "\n";

// Force debug output in AIService by modifying it temporarily or just capturing output?
// Since I cannot easily modify the class to echo without breaking it, I will rely on the return value.
// But wait, the class catches exceptions and returns fallback.
// I need to see the error log.
// Let's define a custom error handler or check if we can modify AIService to return the error in the fallback message for debugging.

// Actually, let's try to call the private method callGroqAPI via reflection if needed, 
// or better, let's modify AIService.php to append the error to the explanation in the fallback.

// For now, let's just run it and see if it returns fallback.
$result = AIService::suggestTaskOrder($tareas);

echo "Result:\n";
print_r($result);

if (strpos($result['explanation'], 'Configure GROQ_API_KEY') !== false) {
    echo "\nFALLBACK DETECTED.\n";
} else {
    echo "\nSUCCESS (AI Response).\n";
}
