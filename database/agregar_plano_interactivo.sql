-- ==========================================
-- SCRIPT: Agregar funcionalidad de plano interactivo
-- Fecha: 2025-12-22
-- Descripción: Agrega campos para manejar planos interactivos con puntos de lotes
-- ==========================================

-- Agregar campo plano_imagen a proyectos (si no existe)
ALTER TABLE `proyectos` 
ADD COLUMN `plano_imagen` VARCHAR(255) NULL COMMENT 'Ruta de la imagen del plano del proyecto' 
AFTER `descripcion`;

-- Agregar campos de coordenadas a lotes para posicionar en el plano
ALTER TABLE `lotes` 
ADD COLUMN `plano_x` DECIMAL(6,2) NULL COMMENT 'Coordenada X en el plano (porcentaje 0-100)' 
AFTER `observaciones`,
ADD COLUMN `plano_y` DECIMAL(6,2) NULL COMMENT 'Coordenada Y en el plano (porcentaje 0-100)' 
AFTER `plano_x`;

-- Crear índice para búsqueda rápida de lotes con coordenadas
CREATE INDEX idx_lotes_plano ON lotes(proyecto_id, plano_x, plano_y);

-- Mensaje de confirmación
SELECT 'Campos para plano interactivo agregados exitosamente' AS mensaje;
