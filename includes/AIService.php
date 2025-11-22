<?php
/**
 * AI Service - Groq Integration
 * Servicio para ordenamiento inteligente de tareas usando IA
 */

require_once __DIR__ . '/../config/config.php';

class AIService {
    
    /**
     * Sugiere el orden de tareas usando IA de Groq
     * @param array $tareas Array de tareas con campos: id, titulo, descripcion, fecha_limite, prioridad
     * @return array ['success' => bool, 'order' => array, 'explanation' => string, 'error' => string]
     */
    public static function suggestTaskOrder($tareas) {
        if (empty($tareas)) {
            return [
                'success' => false,
                'error' => 'No hay tareas para ordenar'
            ];
        }
        
        // Verificar si hay API key configurada
        if (!defined('GROQ_API_KEY') || GROQ_API_KEY === 'gsk_COLOCA_TU_API_KEY_AQUI') {
            // Fallback al algoritmo simple
            return self::fallbackSimpleOrder($tareas);
        }
        
        try {
            // Preparar datos para la IA
            $taskData = self::formatTasksForAI($tareas);
            
            // Crear el prompt
            $prompt = self::createPrompt($taskData);
            
            // Llamar a la API de Groq
            $response = self::callGroqAPI($prompt);
            
            if ($response['success']) {
                return $response;
            } else {
                // Si la IA falla, devolver el error para que el usuario lo vea
                return $response;
            }
            
        } catch (Exception $e) {
            error_log("Error en AIService: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Formatea las tareas para el prompt de IA
     */
    private static function formatTasksForAI($tareas) {
        $formatted = [];
        
        foreach ($tareas as $tarea) {
            $formatted[] = [
                'id' => $tarea['id'],
                'titulo' => $tarea['titulo'],
                'descripcion' => $tarea['descripcion'] ?? '',
                'fecha_limite' => $tarea['fecha_limite'] ?? null,
                'prioridad' => $tarea['prioridad'],
                'curso' => $tarea['curso'] ?? ''
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Crea el prompt para la IA
     */
    private static function createPrompt($taskData) {
        $tasksJson = json_encode($taskData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        $prompt = <<<PROMPT
Eres un asistente experto en gestión de tareas. Analiza las siguientes tareas y sugiere un orden óptimo.

TAREAS:
{$tasksJson}

CRITERIOS:
1. Urgencia (fecha límite)
2. Prioridad (high > medium > low)
3. Complejidad (título/descripción)

INSTRUCCIONES DE RESPUESTA:
- Devuelve SOLAMENTE un objeto JSON válido.
- NO incluyas bloques de código markdown (```json ... ```).
- NO incluyas texto antes ni después del JSON.
- El JSON debe tener esta estructura exacta:
{
  "order": [1, 3, 2],
  "explanation": "Primero la tarea 'Arreglar login' (ID 1) por urgencia. Luego 'Diseñar base de datos' (ID 3) por complejidad y prioridad alta. Finalmente 'Actualizar docs' (ID 2)."
}

IMPORTANTE:
- En "explanation", usa los TÍTULOS de las tareas para que el usuario entienda el orden.
- Sé conciso en la explicación.
PROMPT;
        
        return $prompt;
    }
    
    /**
     * Llama a la API de Groq
     */
    private static function callGroqAPI($prompt) {
        $ch = curl_init(GROQ_API_URL);
        
        $payload = [
            'model' => GROQ_MODEL,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.3,
            'max_tokens' => 1000
        ];
        
        $headers = [
            'Authorization: Bearer ' . GROQ_API_KEY,
            'Content-Type: application/json'
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false // Para desarrollo local
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("Error de cURL en Groq API: " . $error);
            return ['success' => false, 'error' => 'Error de conexión con IA'];
        }
        
        if ($httpCode !== 200) {
            error_log("Groq API HTTP {$httpCode}: " . $response);
            return ['success' => false, 'error' => 'Error en respuesta de IA'];
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['choices'][0]['message']['content'])) {
            error_log("Respuesta inesperada de Groq: " . $response);
            return ['success' => false, 'error' => 'Respuesta inválida de IA'];
        }
        
        $content = $data['choices'][0]['message']['content'];
        
        // Limpiar respuesta para extraer JSON
        $content = trim($content);
        
        // Intentar encontrar el JSON entre llaves si hay texto extra
        if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
            $content = $matches[0];
        }
        
        // Eliminar posibles bloques de markdown que hayan quedado
        $content = preg_replace('/^```json\s*/i', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);
        
        $result = json_decode($content, true);
        
        if (!isset($result['order']) || !isset($result['explanation'])) {
            error_log("JSON inválido de IA: " . $content);
            return ['success' => false, 'error' => 'Formato de respuesta inválido'];
        }
        
        return [
            'success' => true,
            'order' => $result['order'],
            'explanation' => $result['explanation']
        ];
    }
    
    /**
     * Algoritmo de ordenamiento simple (fallback)
     */
    private static function fallbackSimpleOrder($tareas) {
        $prioridades = ['high' => 3, 'medium' => 2, 'low' => 1];
        
        usort($tareas, function($a, $b) use ($prioridades) {
            // Primero por fecha límite
            if ($a['fecha_limite'] && $b['fecha_limite']) {
                $fechaComp = strtotime($a['fecha_limite']) - strtotime($b['fecha_limite']);
                if ($fechaComp !== 0) return $fechaComp;
            } elseif ($a['fecha_limite']) {
                return -1;
            } elseif ($b['fecha_limite']) {
                return 1;
            }
            
            // Luego por prioridad
            $prioA = $prioridades[$a['prioridad']] ?? 0;
            $prioB = $prioridades[$b['prioridad']] ?? 0;
            
            return $prioB - $prioA;
        });
        
        return [
            'success' => true,
            'order' => array_column($tareas, 'id'),
            'explanation' => 'Orden basado en fecha límite (más próxima primero) y prioridad (alta a baja). Configure GROQ_API_KEY para análisis de dificultad con IA.'
        ];
    }
}
