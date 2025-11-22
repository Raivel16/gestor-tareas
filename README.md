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

### Despliegue en Producción

- 🐧 **Rocky Linux**: Scripts de instalación automatizada
- 🌐 **Apache/Nginx**: Configuración optimizada
- 🗄️ **MariaDB**: Base de datos robusta
- 📦 **Todo incluido**: Dependencias, firewall, SELinux

---

## 📋 Requisitos

### Desarrollo Local (Windows - Laragon)

- PHP 7.4+
- MariaDB 5.7+ / MySQL 5.7+
- Apache
- Extensiones PHP: pdo_mysql, json, mbstring, fileinfo

### Producción (Rocky Linux)

- Rocky Linux 8/9
- Apache 2.4+
- PHP 8.0+
- MariaDB 10.5+
- Acceso root/sudo

---

## 🛠️ Instalación

### Opción 1: Desarrollo Local (Laragon en Windows)

#### Paso 1: Iniciar Laragon

```bash
# Asegúrate de que Apache y MySQL estén corriendo
```

#### Paso 2: Ejecutar Script de Base de Datos

**Método HeidiSQL (Recomendado):**

1. Abre Laragon → Click en "Database" → HeidiSQL se abre
2. Conecta a MySQL (usuario: `root`, sin contraseña)
3. Click en "File" → "Load SQL file"
4. Selecciona `c:\laragon\www\gestor-tareas\script.sql`
5. Click en "Execute" (F9)

**Método Línea de Comandos:**

```bash
# Abrir terminal de Laragon
mysql -u root < c:\laragon\www\gestor-tareas\script.sql
```

#### Paso 3: Configurar Groq API Key

1. Obtén tu API key en: https://console.groq.com/keys
2. Edita `config/config.php`:

```php
define('GROQ_API_KEY', 'gsk_TU_API_KEY_AQUI');
```

#### Paso 4: Acceder a la Aplicación

```
http://localhost/gestor-tareas/login.php
```

**Usuario Demo:**

- Email: `demo@gestor.com`
- Password: `123456`

---

### Opción 2: Producción (Rocky Linux)

#### Paso 1: Transferir Archivos al Servidor

```bash
# Comprimir proyecto
tar -czf gestor-tareas.tar.gz gestor-tareas/

# Transferir al servidor (desde tu máquina local)
scp gestor-tareas.tar.gz user@IP_DEL_SERVIDOR:/tmp/

# En el servidor
ssh user@IP_DEL_SERVIDOR
cd /tmp
tar -xzf gestor-tareas.tar.gz
```

#### Paso 2: Ejecutar Script de Instalación Automatizada

```bash
cd gestor-tareas
sudo bash deploy/install.sh
```

El script hará:

- ✅ Actualizar sistema
- ✅ Instalar Apache, PHP, MariaDB
- ✅ Configurar firewall
- ✅ Copiar archivos a `/var/www/html/gestor-tareas`
- ✅ Establecer permisos correctos
- ✅ Configurar SELinux
- ✅ Ejecutar `mysql_secure_installation`
- ✅ Crear base de datos

#### Paso 3: Configurar Groq API

```bash
sudo nano /var/www/html/gestor-tareas/config/config.php
# Cambiar: define('GROQ_API_KEY', 'gsk_TU_API_KEY_AQUI');
```

#### Paso 4: Acceder desde tu Navegador

```
http://IP_DEL_SERVIDOR/gestor-tareas/login.php
```

#### Paso 5: Resetear Base de Datos (Opcional)

```bash
sudo bash /var/www/html/gestor-tareas/deploy/reset_database.sh
```

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
├── deploy/
│   ├── install.sh            # Instalación automatizada Rocky Linux
│   └── reset_database.sh     # Resetear base de datos
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

## 🗄️ Base de Datos

### Tablas

#### `usuarios`

Almacena información de usuarios registrados.

```sql
id, nombre_completo, email (unique), password (bcrypt), fecha_creacion, fecha_actualizacion
```

#### `tareas`

Todas las tareas con referencias a usuarios.

```sql
id, usuario_id (FK), titulo, descripcion, fecha_limite,
prioridad (enum: low/medium/high), curso, imagen (path),
columna (enum: todo/inprogress/done), orden_posicion,
fecha_creacion, fecha_actualizacion
```

#### `orden_tareas`

Almacena ordenes sugeridos por IA y personalizados.

```sql
id, usuario_id (FK), columna, orden_tipo (enum: ai_suggested/user_custom),
orden_ids (JSON), explicacion_ia, fecha_creacion, fecha_actualizacion
```

### Comandos Útiles

```bash
# Conectar a MariaDB
mysql -u root -p

# Ver bases de datos
SHOW DATABASES;

# Usar la base de datos
USE gestor_tareas;

# Ver tareas de un usuario
SELECT * FROM tareas WHERE usuario_id = 1;

# Ver orden sugerido por IA
SELECT * FROM orden_tareas WHERE usuario_id = 1;
```

---

## 🔌 API Endpoints

### Autenticación (`api/auth.php`)

#### POST `?action=register`

Crear nueva cuenta de usuario.

```json
{
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "password": "contraseña123",
  "confirmPassword": "contraseña123"
}
```

#### POST `?action=login`

Iniciar sesión.

```json
{
  "email": "juan@example.com",
  "password": "contraseña123"
}
```

#### POST `?action=logout`

Cerrar sesión.

#### GET `?action=check`

Verificar estado de sesión.

---

### Tareas (`api/tasks.php`)

#### GET `?action=list`

Obtener todas las tareas del usuario.

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "titulo": "Tarea ejemplo",
      "descripcion": "...",
      "fecha_limite": "2025-12-31",
      "prioridad": "high",
      "curso": "Matemáticas",
      "imagen": "uploads/1/1/imagen.jpg",
      "imagen_url": "http://localhost/gestor-tareas/uploads/1/1/imagen.jpg",
      "columna": "todo",
      "orden_posicion": 1
    }
  ]
}
```

#### POST `?action=create`

Crear nueva tarea.

**Content-Type:** `multipart/form-data` (con imagen) o `application/json` (sin imagen)

**FormData:**

```
title: "Título"
description: "Descripción"
date: "2025-12-31"
priority: "high" | "medium" | "low"
tag: "Curso"
column: "todo" | "inprogress" | "done"
image: <File>
```

#### POST `?action=update`

Actualizar tarea existente.

**FormData:**

```
id: 1
title: "Título actualizado"
... (mismo que create)
keep_image: "true" | "false"
```

#### POST `?action=delete`

Eliminar tarea (y su imagen).

```json
{
  "id": 1
}
```

#### POST `?action=move`

Mover tarea entre columnas.

```json
{
  "id": 1,
  "column": "inprogress"
}
```

#### POST `?action=reorder`

Reordenar tareas dentro de una columna.

```json
{
  "column": "todo",
  "order": [3, 1, 2, 4] // IDs en el nuevo orden
}
```

#### POST `?action=suggest_order`

Obtener orden sugerido por IA para columna "Hacer".

**Response:**

```json
{
  "success": true,
  "data": {
    "order": [3, 1, 4, 2],
    "explanation": "Se priorizó la tarea 3 por su urgencia...",
    "count": 4
  }
}
```

---

## 🤖 Configuración de IA (Groq)

### Obtener API Key

1. Visita: https://console.groq.com/
2. Crea una cuenta gratuita
3. Ve a "API Keys"
4. Crea una nueva key
5. Copia la key (comienza con `gsk_...`)

### Configurar en la Aplicación

**Archivo:** `config/config.php`

```php
define('GROQ_API_KEY', 'gsk_TU_API_KEY_AQUI');
define('GROQ_MODEL', 'llama-3.3-70b-versatile');  // Modelo recomendado
```

### Funcionamiento

Cuando haces click en **"Sugerir Orden"**:

1. La aplicación envía tus tareas de "Hacer" a Groq
2. La IA analiza:
   - **Urgencia**: Fechas límite
   - **Prioridad**: Alta/Media/Baja
   - **Dificultad**: Estimada del título y descripción
3. Devuelve:
   - Orden optimizado de IDs
   - Explicación del razonamiento
4. Se muestra modal con la explicación
5. Las tareas se reordenan automáticamente

### Sin API Key

Si no configuras la API key, el sistema usa un **algoritmo de fallback** que ordena solo por fecha límite y prioridad (sin análisis de dificultad).

---

## 📸 Sistema de Imágenes

### Almacenamiento

Las imágenes se guardan en: `uploads/{usuario_id}/{tarea_id}/imagen.{ext}`

**Ejemplo:**

```
uploads/
├── 1/                    # Usuario ID 1
│   ├── 5/
│   │   └── imagen.jpg   # Imagen de tarea ID 5
│   ├── 12/
│   │   └── imagen.png   # Imagen de tarea ID 12
├── 2/                    # Usuario ID 2
│   └── 8/
│       └── imagen.webp  # Imagen de tarea ID 8
```

### Validaciones

- **Tipos permitidos**: JPG, PNG, GIF, WEBP
- **Tamaño máximo**: 5MB
- **Seguridad**:
  - No ejecución de PHP en carpeta uploads
  - Validación de tipo MIME
  - Sin acceso directo a archivos PHP

### Gestión

- **Al crear tarea con imagen**: Se crea carpeta y se guarda
- **Al actualizar tarea**:
  - Nueva imagen → Elimina anterior, guarda nueva
  - Sin cambio → Mantiene imagen existente
- **Al eliminar tarea**: Se elimina imagen y carpeta si está vacía

---

## 🎨 Sistema de Notificaciones

### Tipos de Notificaciones

#### 1. **Toasts** (Notificaciones Rápidas)

Aparecen arriba a la derecha, desaparecen automáticamente.

**Uso:**

```javascript
showToast("Tarea creada exitosamente", "success");
showToast("Error al guardar", "error", 5000); // 5 segundos
showToast("Campos incompletos", "warning");
```

**Tipos**: `success`, `error`, `warning`, `info`

#### 2. **Modales** (Notificaciones Impor tantes)

Requieren acción del usuario.

**Uso:**

```javascript
showModal("Título", "Mensaje descriptivo", "success", () =>
  console.log("Usuario hizo click en Aceptar")
);
```

#### 3. **Confirmaciones**

Para acciones destructivas o importantes.

**Uso:**

```javascript
showConfirm(
  "Eliminar Tarea",
  "¿Estás seguro? Esta acción no se puede deshacer.",
  () => deletarTarea(), // onConfirm
  () => console.log("Cancelado") // onCancel (opcional)
);
```

#### 4. **Loading Overlay**

Mientras se procesa una operación async.

**Uso:**

```javascript
showLoading("Procesando...");
// ... operación async ...
hideLoading();
```

#### 5. **Explicación de IA**

Modal especial para mostrar el razonamiento de la IA.

**Uso:**

```javascript
showAIExplanation("La tarea 3 se priorizó porque...", 5);
```

---

## 🐛 Solución de Problemas

### Error al subir imágenes

**Síntoma:** "Error al guardar el archivo"

**Solución Rocky Linux:**

```bash
# Verificar permisos
ls -la /var/www/html/gestor-tareas/uploads

# Corregir permisos
sudo chown -R apache:apache /var/www/html/gestor-tareas/uploads
sudo chmod -R 775 /var/www/html/gestor-tareas/uploads

# Si tienes SELinux enforcing
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/gestor-tareas/uploads(/.*)?"
sudo restorecon -Rv /var/www/html/gestor-tareas/uploads
```

**Solución Windows/Laragon:**

```
Asegúrate de que la carpeta uploads existe y tiene permisos de escritura.
```

### Base de datos no se conecta

**Síntoma:** "Error de conexión a la base de datos"

**Rocky Linux:**

```bash
# Verificar que MariaDB está corriendo
sudo systemctl status mariadb

# Si no está corriendo
sudo systemctl start mariadb

# Verificar acceso
mysql -u root -p
```

**Windows/Laragon:**

```
Verifica que MySQL esté iniciado en el panel de Laragon.
```

### Error de IA: "Error en respuesta de IA"

**Causas comunes:**

1. API key incorrecta o expirada
2. Sin créditos en Groq
3. Límite de rate alcanzado

**Solución:**

1. Verifica tu API key en `config/config.php`
2. Visita https://console.groq.com/ para verificar estado
3. El sistema automáticamente usa fallback si falla

### Drag & Drop no funciona

**Solución:**

1. Asegúrate de estar usando un navegador moderno (Chrome, Firefox, Edge)
2. Limpia el caché del navegador
3. Verifica la consola del navegador (F12) para errores JavaScript

### Sesión se pierde constantemente

**Síntoma:** Te redirige a login frecuentemente

**Rocky Linux:**

```bash
# Verificar configuración de sesiones PHP
sudo nano /etc/php.ini

# Asegúrate de que session.save_path existe y tiene permisos
# Por defecto: /var/lib/php/session
```

---

## 🚀 Comandos Útiles Rocky Linux

### Servicios

```bash
# Ver estado de servicios
sudo systemctl status httpd mariadb

# Reiniciar Apache
sudo systemctl restart httpd

# Reiniciar MariaDB
sudo systemctl restart mariadb

# Ver logs de Apache
sudo tail -f /var/log/httpd/error_log
```

### Firewall

```bash
# Ver reglas
sudo firewall-cmd --list-all

# Abrir puerto
sudo firewall-cmd --permanent --add-port=80/tcp
sudo firewall-cmd --reload
```

### Permisos

```bash
# Cambiar dueño
sudo chown -R apache:apache /var/www/html/gestor-tareas

# Cambiar permisos
sudo chmod -R 755 /var/www/html/gestor-tareas
sudo chmod -R 775 /var/www/html/gestor-tareas/uploads
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

---

## 🎯 Próximas Mejoras

- [ ] Recuperación de contraseña por email
- [ ] Colaboración en tareas (compartir tableros)
- [ ] Notificaciones push
- [ ] Exportar tareas a PDF/Excel
- [ ] Etiquetas/categorías con colores personalizados
- [ ] Subtareas
- [ ] Comentarios en tareas
- [ ] Tema oscuro/claro
- [ ] App móvil nativa

---

## 📄 Licencia

Este proyecto es de código abierto bajo la licencia MIT.

---

## 👨‍💻 Autor

Desarrollado con ❤️ para gestión eficiente de tareas.

**Contacto:**

- GitHub: [Tu GitHub]
- Email: [Tu Email]

---

## 🙏 Agradecimientos

- **Groq**: Por proporcionar API de IA gratuita y rápida
- **Rocky Linux**: Por la distribución empresarial estable
- **PHP Community**: Por las excelentes herramientas y documentación

---

**¿Necesitas ayuda?** Revisa la sección de [Solución de Problemas](#-solución-de-problemas) o abre un issue en GitHub.

**¡Disfruta gestionando tus tareas!** 🎉
