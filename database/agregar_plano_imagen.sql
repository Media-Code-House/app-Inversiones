-- =====================================================
-- AGREGAR COLUMNA plano_imagen A LA TABLA proyectos
-- =====================================================
-- Fecha: 2026-01-02
-- Descripción: Agregar campo para almacenar la ruta de la imagen del plano del proyecto

-- Verificar si la columna ya existe
SELECT COUNT(*) as existe 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'u418271893_developIvercio' 
AND TABLE_NAME = 'proyectos' 
AND COLUMN_NAME = 'plano_imagen';

-- Si el resultado anterior es 0, ejecutar:
ALTER TABLE proyectos 
ADD COLUMN plano_imagen VARCHAR(255) NULL 
COMMENT 'Ruta de la imagen del plano del proyecto' 
AFTER fecha_inicio;

-- Verificar que se agregó correctamente
DESCRIBE proyectos;

-- Mostrar registros actuales
SELECT id, codigo, nombre, plano_imagen FROM proyectos LIMIT 5;
