# Uploads Directory

This directory stores user-uploaded task images.

**Structure**: `uploads/{usuario_id}/{tarea_id}/imagen.ext`

**Security**:

- PHP execution is disabled via .htaccess
- Only image files are accessible
- Directory listing is disabled

**Permissions** (for Rocky Linux):

```bash
sudo chown -R apache:apache uploads/
sudo chmod -R 755 uploads/
```
