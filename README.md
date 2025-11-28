# Gestor de Tareas - Task Management System

Sistema profesional de gestión de tareas con tablero Kanban, soporte de imágenes, ordenamiento inteligente con IA y despliegue en Rocky Linux.

## 🚀 Características

### Gestión de Tareas

- ✅ **CRUD Completo**: Crear, leer, actualizar y eliminar tareas
- ✅ **Tablero Kanban**: Tres columnas (Hacer, En progreso, Hecho)
- ✅ **Drag & Drop**: Arrastra tareas entre columnas y reordena manualmente
- ✅ **Campos Completos**: Título, descripción, fecha límite, prioridad, curso e imagen
- ✅ **Subida de Imágenes**: Almacenamiento de archivos organizados por usuario/tarea
- ✅ **Búsqueda**: Filtra tareas por título y descripción en tiempo real

### Ordenamiento Inteligente con IA

- 🤖 **Groq AI Integration**: Análisis de dificultad de tareas usando LLaMA 3.3
- 🧠 **Criterios Inteligentes**:
  - Urgencia (fecha límite más próxima)
  - Prioridad (Alta > Media > Baja)
  - Dificultad/Complejidad (analizada del título y descripción)
- 💡 **Explicación**: La IA proporciona una justificación del orden sugerido
- 🔄 **Fallback**: Algoritmo simple si la IA no está configurada

### Autenticación y Seguridad

- 🔐 **Registro y Login**: Sistema de sesiones PHP
- 🔒 **Contraseñas Hasheadas**: bcrypt para máxima seguridad
- 👥 **Multi-usuario**: Aislamiento completo de datos por usuario
- 🛡️ **Validaciones**: Server-side y client-side

### Interfaz Profesional

- 🎨 **Notificaciones Modernas**: Modales, toasts y overlays de carga
- 📱 **Responsive**: Funciona en desktop, tablet y móvil
- ⚡ **Interactiva**: Actualizaciones en tiempo real sin recargar
- 🎯 **UX Mejorada**: Feedback visual para todas las acciones
---

## 📁 Estructura del Proyecto

```
gestor-tareas/
├── api/
│   ├── auth.php              # Endpoints de autenticación
│   └── tasks.php             # Endpoints de tareas (CRUD + AI)
├── config/
│   ├── config.php            # Configuración (Groq API, uploads)
│   ├── database.php          # Conexión a base de datos
│   └── .htaccess             # Protección de archivos de config
├── includes/
│   ├── functions.php         # Funciones auxiliares
│   └── AIService.php         # Servicio de IA (Groq)
├── css/
│   └── notifications.css     # Estilos de notificaciones
├── js/
│   └── notifications.js      # Sistema de notificaciones
├── uploads/                  # Almacenamiento de imágenes
│   ├── .htaccess             # Seguridad (no PHP execution)
│   └── {usuario_id}/         # Por usuario
│       └── {tarea_id}/       # Por tarea
├── index.php                 # Dashboard principal
├── login.php                 # Página de login
├── signup.php                # Página de registro
├── script.js                 # Lógica del Kanban
├── auth.js                   # Lógica de autenticación
├── styles.css                # Estilos principales
├── script.sql                # Schema de base de datos
└── README.md                 # Este archivo
```

---

## 📝 Notas de Seguridad

- ✅ Contraseñas hasheadas con bcrypt
- ✅ Validación server-side de todos los inputs
- ✅ Protección contra inyección SQL (prepared statements)
- ✅ Sesiones PHP con cookies seguras
- ✅ Sin ejecución de PHP en carpeta de uploads
- ✅ Validación de tipo MIME de archivos
- ✅ Protección de archivos de configuración vía .htaccess
- ✅ Aislamiento de datos entre usuarios

## 📄 Licencia

Este proyecto es de código abierto bajo la licencia MIT.

---

## 👨‍💻 Autores

- **Raivel Lorenzo Valiente**
- **Eddy Giovanny Roque Meza**
- **Grace Brittney Mejia Larrazábal**
- **Pavel Jordan Lapierre Castillo**
- **Jhira Lit Aliaga Ramos**

Desarrollado con ❤️ para gestión eficiente de tareas.

**Contacto:**

- GitHub: [Raivel16](https://github.com/Raivel16)
- Email: [raivellorenzovaliente@gmail.com]

---

## 🙏 Agradecimientos

- **Groq**: Por proporcionar API de IA gratuita y rápida
- **Rocky Linux**: Por la distribución empresarial estable
- **PHP Community**: Por las excelentes herramientas y documentación

---
**¡Disfruta gestionando tus tareas!** 🎉
