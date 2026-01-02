<?php
require_once 'core/Database.php';
require_once 'config/config.php';

echo "=== Instalando Campos para Plano Interactivo ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Agregar campo plano_imagen a proyectos
    echo "1. Agregando campo 'plano_imagen' a tabla proyectos...\n";
    try {
        $db->exec("ALTER TABLE `proyectos` 
                   ADD COLUMN `plano_imagen` VARCHAR(255) NULL COMMENT 'Ruta de la imagen del plano del proyecto' 
                   AFTER `descripcion`");
        echo "   ✓ Campo agregado exitosamente\n\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "   ℹ️ El campo ya existe\n\n";
        } else {
            throw $e;
        }
    }
    
    // 2. Agregar campos de coordenadas a lotes
    echo "2. Agregando campos 'plano_x' y 'plano_y' a tabla lotes...\n";
    try {
        $db->exec("ALTER TABLE `lotes` 
                   ADD COLUMN `plano_x` DECIMAL(6,2) NULL COMMENT 'Coordenada X en el plano (porcentaje 0-100)' 
                   AFTER `observaciones`");
        echo "   ✓ Campo 'plano_x' agregado\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "   ℹ️ Campo 'plano_x' ya existe\n";
        } else {
            throw $e;
        }
    }
    
    try {
        $db->exec("ALTER TABLE `lotes` 
                   ADD COLUMN `plano_y` DECIMAL(6,2) NULL COMMENT 'Coordenada Y en el plano (porcentaje 0-100)' 
                   AFTER `plano_x`");
        echo "   ✓ Campo 'plano_y' agregado\n\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "   ℹ️ Campo 'plano_y' ya existe\n\n";
        } else {
            throw $e;
        }
    }
    
    // 3. Crear índice
    echo "3. Creando índice para búsqueda rápida...\n";
    try {
        $db->exec("CREATE INDEX idx_lotes_plano ON lotes(proyecto_id, plano_x, plano_y)");
        echo "   ✓ Índice creado\n\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "   ℹ️ El índice ya existe\n\n";
        } else {
            throw $e;
        }
    }
    
    echo "===========================================\n";
    echo "✓ INSTALACIÓN COMPLETADA EXITOSAMENTE\n";
    echo "===========================================\n\n";
    echo "Ahora puedes:\n";
    echo "1. Editar un proyecto y subir una imagen del plano\n";
    echo "2. Posicionar los lotes en el plano\n";
    echo "3. Ver el plano interactivo en la vista del proyecto\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
