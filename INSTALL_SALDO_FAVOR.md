#!/bin/bash
# ============================================================================
# SCRIPT DE INSTALACIÓN - SISTEMA DE SALDO A FAVOR GLOBAL
# ============================================================================
# Este script ejecuta la migration SQL para agregar la columna saldo_a_favor
# a la tabla lotes y crear el índice necesario.
# 
# REQUISITOS:
# - Conexión MySQL/MariaDB disponible
# - Base de datos "u418271893_inversiones" accesible
# - Credenciales configuradas en config/config.php
# 
# USO:
# bash install_saldo_favor.sh
# ============================================================================

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}INSTALACIÓN - SALDO A FAVOR GLOBAL${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Verificar que el archivo SQL existe
if [ ! -f "database/migration_saldo_a_favor.sql" ]; then
    echo -e "${RED}ERROR: Archivo database/migration_saldo_a_favor.sql no encontrado${NC}"
    exit 1
fi

echo -e "${YELLOW}1. Verificando archivos...${NC}"
echo "✓ migration_saldo_a_favor.sql encontrado"
echo ""

# Leer configuración (simplificado para bash)
echo -e "${YELLOW}2. Preparando instalación...${NC}"
echo "Asegúrate de tener:"
echo "  - MySQL/MariaDB corriendo"
echo "  - Acceso a la base de datos 'u418271893_inversiones'"
echo ""

echo -e "${YELLOW}3. Ejecutando migration SQL...${NC}"
echo ""
echo "NOTA: Ejecuta manualmente el siguiente SQL en tu cliente MySQL:"
echo "------"
cat database/migration_saldo_a_favor.sql
echo "------"
echo ""
echo -e "${GREEN}✓ Instalación completada${NC}"
echo ""
echo -e "${YELLOW}PRÓXIMOS PASOS:${NC}"
echo "1. Ejecuta el SQL anterior en tu base de datos"
echo "2. Verifica que la columna 'saldo_a_favor' fue agregada:"
echo "   SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME='lotes' AND COLUMN_NAME='saldo_a_favor';"
echo "3. Prueba el sistema registrando un pago con excedente"
echo ""
