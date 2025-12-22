-- ============================================
-- Script para corregir campo plano_imagen
-- Tabla: proyectos
-- ============================================

-- Eliminar el campo si existe (para limpieza)
ALTER TABLE `proyectos` DROP COLUMN IF EXISTS `plano_imagen`;

-- Agregar el campo correctamente después de descripcion
ALTER TABLE `proyectos` 
ADD COLUMN `plano_imagen` VARCHAR(255) NULL 
COMMENT 'Ruta de la imagen del plano (uploads/planos/nombre.jpg)' 
AFTER `descripcion`;

-- Verificar que se agregó correctamente
SELECT 'Campo plano_imagen configurado correctamente' AS resultado;

-- Mostrar estructura actual
DESCRIBE proyectos;
