<?php
require_once __DIR__ . '/includes/functions.php';

iniciarSesion();

// Redirigir a login si no está autenticado
if (!estaAutenticado()) {
    header('Location: login.php');
    exit;
}

$nombre_usuario = $_SESSION['nombre_completo'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Tareas</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/notifications.css">
</head>
<body class="dashboard-page">
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <div class="logo">
                <svg width="28" height="28" viewBox="0 0 32 32" fill="none">
                    <rect width="32" height="32" rx="6" fill="#2563eb"/>
                    <path d="M8 10h8v8H8V10zm10 0h8v8h-8V10z" fill="white"/>
                </svg>
                <span>Gestor de Tareas</span>
            </div>
            <div class="search-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <input type="text" id="searchInput" placeholder="Buscar tareas...">
            </div>
        </div>
        <div class="header-right">
            <button id="logoutBtn" class="header-icon avatar" title="Cerrar sesión">
                <span>👤</span>
            </button>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Board Header -->
        <div class="board-header">
            <div class="header-info">
                <h1>Mi Tablero</h1>
                <div class="tabs">
                    <button class="tab active">Board</button>
                </div>
            </div>
            <div class="board-actions">
                <button id="suggestOrderBtn" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2v20M2 12h20"/>
                    </svg>
                    Sugerir Orden
                </button>
            </div>
        </div>

        <!-- Kanban Board -->
        <div class="kanban-board">
            <!-- TO DO Column -->
            <div class="kanban-column">
                <div class="column-header">
                    <div class="column-title">
                        <h3>Hacer</h3>
                        <span class="task-count" data-column="todo">0</span>
                    </div>
                    <button class="column-menu">⋮</button>
                </div>
                <div class="tasks-container" data-column="todo"></div>
                <button class="btn-add-task" onclick="openTaskModal('todo')">+ Agregar Tarea</button>
            </div>

            <!-- IN PROGRESS Column -->
            <div class="kanban-column">
                <div class="column-header">
                    <div class="column-title">
                        <h3>En progreso</h3>
                        <span class="task-count" data-column="inprogress">0</span>
                    </div>
                    <button class="column-menu">⋮</button>
                </div>
                <div class="tasks-container" data-column="inprogress"></div>
                <button class="btn-add-task" onclick="openTaskModal('inprogress')">+ Agregar Tarea</button>
            </div>

            <!-- DONE Column -->
            <div class="kanban-column">
                <div class="column-header">
                    <div class="column-title">
                        <h3>Hecho</h3>
                        <span class="task-count" data-column="done">0</span>
                    </div>
                    <button class="column-menu">⋮</button>
                </div>
                <div class="tasks-container" data-column="done"></div>
                <button class="btn-add-task" onclick="openTaskModal('done')">+ Agregar Tarea</button>
            </div>
        </div>
    </main>

    <!-- Task Modal -->
    <div id="taskModal" class="modal hidden">
        <div class="modal-overlay" onclick="closeTaskModal()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Nueva Tarea</h2>
                <button class="modal-close" onclick="closeTaskModal()">✕</button>
            </div>
            <form id="taskForm" class="task-form">
                <div class="form-group">
                    <label for="taskTitle">Título *</label>
                    <input type="text" id="taskTitle" required placeholder="Ingresa el título de la tarea">
                </div>

                <div class="form-group">
                    <label for="taskDescription">Descripción</label>
                    <textarea id="taskDescription" rows="3" placeholder="Descripción de la tarea (opcional)"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="taskDate">Fecha Límite</label>
                        <input type="date" id="taskDate">
                    </div>
                    <div class="form-group">
                        <label for="taskPriority">Prioridad</label>
                        <select id="taskPriority">
                            <option value="low">Baja</option>
                            <option value="medium" selected>Media</option>
                            <option value="high">Alta</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="taskTag">Curso (Asignatura)</label>
                    <input type="text" id="taskTag" placeholder="ej: Matemáticas, Física, etc">
                </div>

                <div class="form-group">
                    <label for="taskImage">Imagen</label>
                    <div class="image-upload">
                        <input type="file" id="taskImage" accept="image/*" onchange="previewImage(event)">
                        <div class="upload-hint">Haz clic para seleccionar una imagen</div>
                        <div id="imagePreview" class="image-preview"></div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeTaskModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Tarea</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Task Detail Modal -->
    <div id="taskDetailModal" class="modal hidden">
        <div class="modal-overlay" onclick="closeTaskDetailModal()"></div>
        <div class="modal-content modal-detail">
            <div class="modal-header">
                <h2>Detalles de la Tarea</h2>
                <button class="modal-close" onclick="closeTaskDetailModal()">✕</button>
            </div>
            <div id="taskDetailContent" class="task-detail-content"></div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeTaskDetailModal()">Cerrar</button>
                <button type="button" class="btn btn-danger" onclick="deleteCurrentTask()">Eliminar</button>
            </div>
        </div>
    </div>

    <script src="js/notifications.js"></script>
    <script src="script.js"></script>
</body>
</html>
