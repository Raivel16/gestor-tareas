#!/bin/bash
# Script para resetear la base de datos en Rocky Linux
# Ejecutar: bash reset_database.sh

echo "=== Reset de Base de Datos ==="
echo "ADVERTENCIA: Esto eliminará TODOS los datos existentes"
echo ""
read -p "¿Estás seguro? (escribir 'SI' para continuar): " confirmacion

if [ "$confirmacion" != "SI" ]; then
    echo "Operación cancelada"
    exit 0
fi

echo ""
echo "Ingresa la contraseña de root de MariaDB:"
mysql -u root -p < /var/www/html/gestor-tareas/script.sql

echo ""
echo "Base de datos reseteada exitosamente"
echo "Usuario demo: demo@gestor.com / 123456"
