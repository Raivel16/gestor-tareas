#!/bin/bash
# Instalación automatizada de Gestor de Tareas en Rocky Linux
# Ejecutar como root: sudo bash install.sh

set -e  # Detener en caso de error

echo "======================================"
echo "Instalador de Gestor de Tareas"
echo "Rocky Linux - Apache + PHP + MariaDB"
echo "======================================"
echo ""

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Verificar si se ejecuta como root
if [ "$EUID" -ne 0 ]; then 
   echo -e "${RED}Por favor ejecuta este script como root (sudo bash install.sh)${NC}"
   exit 1
fi

echo -e "${YELLOW}[1/8] Actualizando sistema...${NC}"
dnf update -y

echo -e "${YELLOW}[2/8] Instalando Apache...${NC}"
dnf install -y httpd
systemctl enable httpd
systemctl start httpd

echo -e "${YELLOW}[3/8] Instalando PHP 8.0+...${NC}"
dnf install -y php php-mysqlnd php-json php-mbstring php-xml php-curl php-gd
php -v

echo -e "${YELLOW}[4/8] Instalando MariaDB...${NC}"
dnf install -y mariadb-server
systemctl enable mariadb
systemctl start mariadb

echo -e "${YELLOW}[5/8] Configurando firewall...${NC}"
firewall-cmd --permanent --add-service=http
firewall-cmd --permanent --add-service=https
firewall-cmd --reload

echo -e "${YELLOW}[6/8] Copiando archivos de la aplicación...${NC}"
APP_DIR="/var/www/html/gestor-tareas"

# Detectar si los archivos están en el directorio actual
if [ -f "./script.sql" ]; then
    # Copiar desde el directorio actual
    mkdir -p $APP_DIR
    cp -r ./* $APP_DIR/
    echo -e "${GREEN}Archivos copiados desde directorio actual${NC}"
else
    echo -e "${RED}Error: No se encontraron los archivos de la aplicación${NC}"
    echo "Por favor ejecuta este script desde el directorio raíz del proyecto"
    exit 1
fi

echo -e "${YELLOW}[7/8] Configurando permisos...${NC}"
chown -R apache:apache $APP_DIR
chmod -R 755 $APP_DIR

# Crear directorio de uploads con permisos especiales
mkdir -p $APP_DIR/uploads
chown -R apache:apache $APP_DIR/uploads
chmod -R 775 $APP_DIR/uploads

# SELinux: Permitir escritura en uploads y lectura de config
if [ "$(getenforce)" = "Enforcing" ]; then
    echo -e "${YELLOW}Configurando SELinux...${NC}"
    semanage fcontext -a -t httpd_sys_rw_content_t "$APP_DIR/uploads(/.*)?"
    restorecon -Rv $APP_DIR/uploads
fi

echo -e "${YELLOW}[8/8] Configurando base de datos...${NC}"
echo ""
echo -e "${GREEN}=== Configuración de MariaDB ===${NC}"
echo "A continuación se ejecutará mysql_secure_installation"
echo "Recomendaciones:"
echo "  - Establecer contraseña de root"
echo "  - Remover usuarios anónimos: Y"
echo "  - Deshabilitar login remoto de root: Y"
echo "  - Remover base datos de prueba: Y"
echo "  - Recargar privilegios: Y"
echo ""
read -p "Presiona ENTER para continuar..."

mysql_secure_installation

echo ""
echo -e "${GREEN}=== Creando base de datos ===${NC}"
echo "Ingresa la contraseña de root de MariaDB que acabas de establecer:"
mysql -u root -p < $APP_DIR/script.sql

echo ""
echo -e "${GREEN}======================================"
echo "Instalación completada exitosamente!"
echo "======================================${NC}"
echo ""
echo -e "${YELLOW}Próximos pasos:${NC}"
echo ""
echo "1. Configurar Groq API Key:"
echo "   Editar: $APP_DIR/config/config.php"
echo "   Cambiar: GROQ_API_KEY por tu API key"
echo ""
echo "2. Acceder a la aplicación:"
echo "   http://$(hostname -I | awk '{print $1}')/gestor-tareas/login.php"
echo ""
echo "3. Usuario demo:"
echo "   Email: demo@gestor.com"
echo "   Password: 123456"
echo ""
echo -e "${YELLOW}Comandos útiles:${NC}"
echo "  - Reiniciar Apache: systemctl restart httpd"
echo "  - Ver logs: tail -f /var/log/httpd/error_log"
echo "  - Estado de servicios: systemctl status httpd mariadb"
echo ""
echo -e "${GREEN}¡Disfruta tu Gestor de Tareas!${NC}"
