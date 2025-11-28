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



-- Insertar un usuario de prueba (password: 123456)
-- La contraseña está hasheada con password_hash() de PHP
INSERT INTO usuarios (nombre_completo, email, password) VALUES 
('Usuario Demo', 'demo@gestor.com', '$2y$12$pFemtZJ7cSxx8pFaI6N7UeLtZvPTf6WkvYEfM.BUoKjKkmMP7S/CG');

-- Insertar tareas de ejemplo
INSERT INTO tareas (usuario_id, titulo, descripcion, fecha_limite, prioridad, curso, columna, orden_posicion) VALUES
(1, 'Redactar el marco teórico del artículo de investigación',
    'Elaborar el apartado del marco teórico para el artículo, incluyendo variables, definiciones clave, estudios previos y enfoques metodológicos relacionados. Requiere búsqueda bibliográfica, análisis y síntesis.',
    '2025-12-01', 'high', 'Investigación / Artículo Académico', 'todo', 1),

(1, 'Modelar problema de asignación con Método Húngaro',
    'Formular un problema de asignación y aplicar el método húngaro para obtener la solución óptima. Incluye preparar la matriz de costos y analizar resultados.',
    '2025-11-28', 'medium', 'Investigación Operativa / Optimización', 'todo', 2),

(1, 'Desarrollar módulo para actualizar roles en el sistema',
    'Crear la función backend para modificar un rol usando el procedimiento almacenado correspondiente. Requiere validar parámetros, gestionar errores y asegurar respuesta consistente.',
    '2025-11-25', 'high', 'Programación / Backend', 'todo', 3),

(1, 'Implementar control de permisos para páginas protegidas',
    'Ajustar el middleware y rutas de Express para evitar acceso directo a HTMLs internos sin validar permisos. Implica revisar la secuencia de middlewares y probar diferentes casos.',
    '2025-11-30', 'high', 'Programación / Seguridad', 'todo', 4),

(1, 'Resolver un modelo de Programación Lineal para distribución óptima',
    'Definir un modelo de PL para un caso de distribución y resolverlo con simplex o software especializado. Incluye interpretación detallada de resultados.',
    '2025-12-04', 'medium', 'Investigación Operativa / Modelos Matemáticos', 'todo', 5);



SELECT 'Base de datos creada/reseteada exitosamente' AS mensaje;
SELECT 'Usuario demo: demo@gestor.com / 123456' AS info;
