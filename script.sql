-- Script para crear/resetear la base de datos del Gestor de Tareas
-- MariaDB / MySQL
-- ADVERTENCIA: Este script ELIMINARÁ la base de datos existente

-- Eliminar la base de datos si existe
DROP DATABASE IF EXISTS gestor_tareas;

-- Crear la base de datos
CREATE DATABASE gestor_tareas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE gestor_tareas;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de tareas (actualizada para usar rutas de archivos)
CREATE TABLE tareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    fecha_limite DATE,
    prioridad ENUM('low', 'medium', 'high') DEFAULT 'medium',
    curso VARCHAR(255),
    imagen VARCHAR(500) COMMENT 'Ruta relativa a la imagen',
    columna ENUM('todo', 'inprogress', 'done') DEFAULT 'todo',
    orden_posicion INT DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_columna (columna),
    INDEX idx_orden (usuario_id, columna, orden_posicion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para almacenar el orden sugerido por IA
CREATE TABLE orden_tareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    columna ENUM('todo', 'inprogress', 'done') NOT NULL,
    orden_tipo ENUM('ai_suggested', 'user_custom') DEFAULT 'user_custom',
    orden_ids TEXT NOT NULL COMMENT 'JSON array de IDs de tareas en orden',
    explicacion_ia TEXT COMMENT 'Explicación del orden sugerido por IA',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_column (usuario_id, columna),
    INDEX idx_usuario_columna (usuario_id, columna)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar un usuario de prueba (password: 123456)
-- La contraseña está hasheada con password_hash() de PHP
INSERT INTO usuarios (nombre_completo, email, password) VALUES 
('Usuario Demo', 'demo@gestor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insertar tareas de ejemplo
INSERT INTO tareas (usuario_id, titulo, descripcion, fecha_limite, prioridad, curso, columna, orden_posicion) VALUES
(1, 'Implementar sistema de autenticación', 'Crear módulo completo con login, registro y recuperación de contraseña usando JWT', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'high', 'Desarrollo Web', 'todo', 1),
(1, 'Actualizar README', 'Documentar las nuevas funcionalidades implementadas', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'low', 'Documentación', 'todo', 2),
(1, 'Diseñar base de datos', 'Crear diagrama ER y normalizar hasta 3FN para el nuevo módulo', DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'high', 'Base de Datos', 'todo', 3),
(1, 'Revisar código legacy', 'Hacer refactoring del módulo de reportes', DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'medium', 'Mantenimiento', 'todo', 4),
(1, 'Tarea en Progreso', 'Desarrollando API REST para usuarios', DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'medium', 'Backend', 'inprogress', 1),
(1, 'Tarea Completada', 'Configuración inicial del proyecto', DATE_ADD(CURDATE(), INTERVAL -2 DAY), 'low', 'DevOps', 'done', 1);

SELECT 'Base de datos creada/reseteada exitosamente' AS mensaje;
SELECT 'Usuario demo: demo@gestor.com / 123456' AS info;
