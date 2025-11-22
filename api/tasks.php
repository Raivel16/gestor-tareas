<?php
/**
 * Tasks API - Updated with File Upload Support
 * Manejo de operaciones CRUD de tareas con almacenamiento de archivos
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/AIService.php';

iniciarSesion();

// Desactivar salida de errores HTML para evitar romper el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Requerir autenticación para todas las operaciones
requerirAutenticacion();

// No establecer JSON header todavía para manejar multipart/form-data
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        header('Content-Type: application/json');
        getTasks();
        break;
    
    case 'create':
        createTask(); // Maneja tanto form-data como JSON
        break;
    
    case 'update':
        updateTask(); // Maneja tanto form-data como JSON
        break;
    
    case 'delete':
        header('Content-Type: application/json');
        deleteTask();
        break;
    
    case 'move':
        header('Content-Type: application/json');
        moveTask();
        break;
    
    case 'reorder':
        header('Content-Type: application/json');
        reorderTasks();
        break;
    
    case 'suggest_order':
        header('Content-Type: application/json');
        suggestOrder();
        break;
    
    default:
        header('Content-Type: application/json');
        respuestaJSON(false, 'Acción no válida');
}

function getTasks() {
    try {
        $db = Database::getInstance()->getConnection();
        $usuario_id = obtenerUsuarioId();
        
        $stmt = $db->prepare("
            SELECT id, titulo, descripcion, fecha_limite, prioridad, curso, imagen, columna, orden_posicion, fecha_creacion
            FROM tareas
            WHERE usuario_id = ?
            ORDER BY columna, orden_posicion ASC, fecha_creacion DESC
        ");
        $stmt->execute([$usuario_id]);
        $tareas = $stmt->fetchAll();
        
        // Convertir rutas de imagen a URLs completas
        foreach ($tareas as &$tarea) {
            if ($tarea['imagen']) {
                $tarea['imagen_url'] = APP_URL . '/' . $tarea['imagen'];
            } else {
                $tarea['imagen_url'] = null;
            }
        }
        
        respuestaJSON(true, 'Tareas obtenidas', $tareas);
        
    } catch(PDOException $e) {
        error_log("Error al obtener tareas: " . $e->getMessage());
        respuestaJSON(false, 'Error al obtener tareas');
    }
}

function createTask() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Content-Type: application/json');
        respuestaJSON(false, 'Método no permitido');
    }
    
    // Determinar si es multipart/form-data o JSON
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $isFormData = strpos($contentType, 'multipart/form-data') !== false;
    
    if ($isFormData) {
        $titulo = sanitizar($_POST['title'] ?? '');
        $descripcion = sanitizar($_POST['description'] ?? '');
        $fecha_limite = $_POST['date'] ?? null;
        $prioridad = $_POST['priority'] ?? 'medium';
        $curso = sanitizar($_POST['tag'] ?? '');
        $columna = $_POST['column'] ?? 'todo';
        $imageFile = $_FILES['image'] ?? null;
    } else {
        $data = json_decode(file_get_contents('php://input'), true);
        $titulo = sanitizar($data['title'] ?? '');
        $descripcion = sanitizar($data['description'] ?? '');
        $fecha_limite = $data['date'] ?? null;
        $prioridad = $data['priority'] ?? 'medium';
        $curso = sanitizar($data['tag'] ?? '');
        $columna = $data['column'] ?? 'todo';
        $imageFile = null;
    }
    
    // Validaciones
    if (empty($titulo)) {
        respuestaJSON(false, 'El título es requerido');
    }
    
    if (!in_array($prioridad, ['low', 'medium', 'high'])) {
        $prioridad = 'medium';
    }
    
    if (!in_array($columna, ['todo', 'inprogress', 'done'])) {
        $columna = 'todo';
    }
    
    try {
        header('Content-Type: application/json');
        $db = Database::getInstance()->getConnection();
        $usuario_id = obtenerUsuarioId();
        
        // Obtener el siguiente orden_posicion
        $stmt = $db->prepare("SELECT COALESCE(MAX(orden_posicion), 0) + 1 as next_pos FROM tareas WHERE usuario_id = ? AND columna = ?");
        $stmt->execute([$usuario_id, $columna]);
        $orden_posicion = $stmt->fetch()['next_pos'];
        
        // Insertar tarea primero sin imagen
        $stmt = $db->prepare("
            INSERT INTO tareas (usuario_id, titulo, descripcion, fecha_limite, prioridad, curso, columna, orden_posicion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $usuario_id,
            $titulo,
            $descripcion,
            $fecha_limite ?: null,
            $prioridad,
            $curso,
            $columna,
            $orden_posicion
        ]);
        
        $tarea_id = $db->lastInsertId();
        $imagen_path = null;
        
        // Procesar imagen si existe
        if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
            $uploadResult = handleImageUpload($imageFile, $usuario_id, $tarea_id);
            if ($uploadResult['success']) {
                $imagen_path = $uploadResult['path'];
                
                // Actualizar tarea con ruta de imagen
                $stmt = $db->prepare("UPDATE tareas SET imagen = ? WHERE id = ?");
                $stmt->execute([$imagen_path, $tarea_id]);
            } else {
                respuestaJSON(false, $uploadResult['error']);
            }
        }
        
        // Obtener la tarea completa
        $stmt = $db->prepare("SELECT * FROM tareas WHERE id = ?");
        $stmt->execute([$tarea_id]);
        $tarea = $stmt->fetch();
        
        if ($tarea['imagen']) {
            $tarea['imagen_url'] = APP_URL . '/' . $tarea['imagen'];
        } else {
            $tarea['imagen_url'] = null;
        }
        
        respuestaJSON(true, 'Tarea creada exitosamente', $tarea);
        
    } catch(PDOException $e) {
        error_log("Error al crear tarea: " . $e->getMessage());
        respuestaJSON(false, 'Error al crear la tarea');
    }
}

function updateTask() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Content-Type: application/json');
        respuestaJSON(false, 'Método no permitido');
    }
    
    // Determinar si es multipart/form-data o JSON
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $isFormData = strpos($contentType, 'multipart/form-data') !== false;
    
    if ($isFormData) {
        $id = intval($_POST['id'] ?? 0);
        $titulo = sanitizar($_POST['title'] ?? '');
        $descripcion = sanitizar($_POST['description'] ?? '');
        $fecha_limite = $_POST['date'] ?? null;
        $prioridad = $_POST['priority'] ?? 'medium';
        $curso = sanitizar($_POST['tag'] ?? '');
        $imageFile = $_FILES['image'] ?? null;
        $keepImage = $_POST['keep_image'] === 'true';
    } else {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        $titulo = sanitizar($data['title'] ?? '');
        $descripcion = sanitizar($data['description'] ?? '');
        $fecha_limite = $data['date'] ?? null;
        $prioridad = $data['priority'] ?? 'medium';
        $curso = sanitizar($data['tag'] ?? '');
        $imageFile = null;
        $keepImage = true;
    }
    
    // Validaciones
    if ($id <= 0) {
        respuestaJSON(false, 'ID de tarea inválido');
    }
    
    if (empty($titulo)) {
        header('Content-Type: application/json');
        respuestaJSON(false, 'El título es requerido');
    }
    
    if (!in_array($prioridad, ['low', 'medium', 'high'])) {
        $prioridad = 'medium';
    }
    
    try {
        header('Content-Type: application/json');
        $db = Database::getInstance()->getConnection();
        $usuario_id = obtenerUsuarioId();
        
        // Obtener tarea actual
        $stmt = $db->prepare("SELECT imagen FROM tareas WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$id, $usuario_id]);
        $tareaActual = $stmt->fetch();
        
        if (!$tareaActual) {
            respuestaJSON(false, 'Tarea no encontrada');
        }
        
        $imagen_path = $tareaActual['imagen'];
        
        // Procesar nueva imagen si existe
        if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
            // Eliminar imagen anterior si existe
            if ($imagen_path && file_exists(UPLOAD_DIR . '../' . $imagen_path)) {
                unlink(UPLOAD_DIR . '../' . $imagen_path);
            }
            
            $uploadResult = handleImageUpload($imageFile, $usuario_id, $id);
            if ($uploadResult['success']) {
                $imagen_path = $uploadResult['path'];
            } else {
                respuestaJSON(false, $uploadResult['error']);
            }
        } elseif (!$keepImage) {
            // Si no se mantiene la imagen, eliminarla
            if ($imagen_path && file_exists(UPLOAD_DIR . '../' . $imagen_path)) {
                unlink(UPLOAD_DIR . '../' . $imagen_path);
            }
            $imagen_path = null;
        }
        
        // Actualizar tarea
        $stmt = $db->prepare("
            UPDATE tareas
            SET titulo = ?, descripcion = ?, fecha_limite = ?, prioridad = ?, curso = ?, imagen = ?
            WHERE id = ? AND usuario_id = ?
        ");
        $stmt->execute([
            $titulo,
            $descripcion,
            $fecha_limite ?: null,
            $prioridad,
            $curso,
            $imagen_path,
            $id,
            $usuario_id
        ]);
        
        // Obtener la tarea actualizada
        $stmt = $db->prepare("SELECT * FROM tareas WHERE id = ?");
        $stmt->execute([$id]);
        $tarea = $stmt->fetch();
        
        if ($tarea['imagen']) {
            $tarea['imagen_url'] = APP_URL . '/' . $tarea['imagen'];
        } else {
            $tarea['imagen_url'] = null;
        }
        
        respuestaJSON(true, 'Tarea actualizada exitosamente', $tarea);
        
    } catch(PDOException $e) {
        error_log("Error al actualizar tarea: " . $e->getMessage());
        respuestaJSON(false, 'Error al actualizar la tarea');
    }
}

function deleteTask() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respuestaJSON(false, 'Método no permitido');
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);
    
    if ($id <= 0) {
        respuestaJSON(false, 'ID de tarea inválido');
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        $usuario_id = obtenerUsuarioId();
        
        // Obtener tarea para eliminar imagen
        $stmt = $db->prepare("SELECT imagen FROM tareas WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$id, $usuario_id]);
        $tarea = $stmt->fetch();
        
        if (!$tarea) {
            respuestaJSON(false, 'Tarea no encontrada');
        }
        
        // Eliminar imagen si existe
        if ($tarea['imagen'] && file_exists(UPLOAD_DIR . '../' . $tarea['imagen'])) {
            unlink(UPLOAD_DIR . '../' . $tarea['imagen']);
            
            // Intentar eliminar directorio de tarea si está vacío
            $taskDir = dirname(UPLOAD_DIR . '../' . $tarea['imagen']);
            if (is_dir($taskDir) && count(scandir($taskDir)) == 2) { // solo . y ..
                rmdir($taskDir);
            }
        }
        
        // Eliminar tarea
        $stmt = $db->prepare("DELETE FROM tareas WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$id, $usuario_id]);
        
        respuestaJSON(true, 'Tarea eliminada exitosamente');
        
    } catch(PDOException $e) {
        error_log("Error al eliminar tarea: " . $e->getMessage());
        respuestaJSON(false, 'Error al eliminar la tarea');
    }
}

function moveTask() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respuestaJSON(false, 'Método no permitido');
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = intval($data['id'] ?? 0);
    $columna = $data['column'] ?? '';
    
    if ($id <= 0) {
        respuestaJSON(false, 'ID de tarea inválido');
    }
    
    if (!in_array($columna, ['todo', 'inprogress', 'done'])) {
        respuestaJSON(false, 'Columna inválida');
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        $usuario_id = obtenerUsuarioId();
        
        // Verificar que la tarea pertenece al usuario
        $stmt = $db->prepare("SELECT id FROM tareas WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$id, $usuario_id]);
        
        if (!$stmt->fetch()) {
            respuestaJSON(false, 'Tarea no encontrada');
        }
        
        // Obtener el siguiente orden_posicion en la nueva columna
        $stmt = $db->prepare("SELECT COALESCE(MAX(orden_posicion), 0) + 1 as next_pos FROM tareas WHERE usuario_id = ? AND columna = ?");
        $stmt->execute([$usuario_id, $columna]);
        $orden_posicion = $stmt->fetch()['next_pos'];
        
        // Mover tarea
        $stmt = $db->prepare("UPDATE tareas SET columna = ?, orden_posicion = ? WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$columna, $orden_posicion, $id, $usuario_id]);
        
        respuestaJSON(true, 'Tarea movida exitosamente');
        
    } catch(PDOException $e) {
        error_log("Error al mover tarea: " . $e->getMessage());
        respuestaJSON(false, 'Error al mover la tarea');
    }
}

function reorderTasks() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respuestaJSON(false, 'Método no permitido');
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $columna = $data['column'] ?? '';
    $order = $data['order'] ?? []; // Array de IDs en el orden deseado
    
    if (!in_array($columna, ['todo', 'inprogress', 'done'])) {
        respuestaJSON(false, 'Columna inválida');
    }
    
    if (!is_array($order) || empty($order)) {
        respuestaJSON(false, 'Orden inválido');
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        $usuario_id = obtenerUsuarioId();
        
        $db->beginTransaction();
        
        // Actualizar orden_posicion para cada tarea
        $stmt = $db->prepare("UPDATE tareas SET orden_posicion = ? WHERE id = ? AND usuario_id = ? AND columna = ?");
        
        foreach ($order as $index => $task_id) {
            $posicion = $index + 1;
            $stmt->execute([$posicion, intval($task_id), $usuario_id, $columna]);
        }
        
        $db->commit();
        
        respuestaJSON(true, 'Orden actualizado exitosamente');
        
    } catch(PDOException $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log("Error al reordenar tareas: " . $e->getMessage());
        respuestaJSON(false, 'Error al reordenar las tareas');
    }
}

function suggestOrder() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respuestaJSON(false, 'Método no permitido');
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        $usuario_id = obtenerUsuarioId();
        
        // Obtener todas las tareas de la columna "todo"
        $stmt = $db->prepare("
            SELECT id, titulo, descripcion, fecha_limite, prioridad, curso
            FROM tareas
            WHERE usuario_id = ? AND columna = 'todo'
        ");
        $stmt->execute([$usuario_id]);
        $tareas = $stmt->fetchAll();
        
        if (empty($tareas)) {
            respuestaJSON(false, 'No hay tareas en la columna "Hacer" para ordenar');
        }
        
        // Obtener orden sugerido por la IA
        $aiResult = AIService::suggestTaskOrder($tareas);
        
        if (!$aiResult['success']) {
            respuestaJSON(false, $aiResult['error'] ?? 'Error al procesar orden');
        }
        
        $ordenSugerido = $aiResult['order'];
        $explicacion = $aiResult['explanation'];
        
        // NO aplicar cambios, solo devolver sugerencia
        respuestaJSON(true, 'Sugerencia generada exitosamente', [
            'order' => $ordenSugerido,
            'explanation' => $explicacion,
            'count' => count($ordenSugerido)
        ]);
        
    } catch(PDOException $e) {
        error_log("Error al sugerir orden: " . $e->getMessage());
        respuestaJSON(false, 'Error al generar sugerencia');
    }
}

/**
 * Maneja la subida de archivos de imagen
 */
function handleImageUpload($file, $usuario_id, $tarea_id) {
    // Validar tamaño
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        return ['success' => false, 'error' => 'La imagen no debe superar 5MB'];
    }
    
    // Validar tipo MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, UPLOAD_ALLOWED_TYPES)) {
        return ['success' => false, 'error' => 'Tipo de archivo no permitido. Solo JPG, PNG, GIF, WEBP'];
    }
    
    // Validar extensión
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, UPLOAD_ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'error' => 'Extensión de archivo no permitida'];
    }
    
    // Crear estructura de directorios
    $userDir = UPLOAD_DIR . $usuario_id . '/';
    $taskDir = $userDir . $tarea_id . '/';
    
    if (!is_dir($userDir)) {
        mkdir($userDir, 0755, true);
    }
    
    if (!is_dir($taskDir)) {
        mkdir($taskDir, 0755, true);
    }
    
    // Nombre del archivo
    $filename = 'imagen.' . $extension;
    $filepath = $taskDir . $filename;
    
    // Mover archivo
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Ruta relativa para la base de datos
        $relativePath = 'uploads/' . $usuario_id . '/' . $tarea_id . '/' . $filename;
        return ['success' => true, 'path' => $relativePath];
    } else {
        return ['success' => false, 'error' => 'Error al guardar el archivo'];
    }
}
