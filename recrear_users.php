<?php
/**
 * Recrear tabla users con la estructura del schema.sql (Módulo 3)
 */

require_once __DIR__ . '/config/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "Recreando tabla users...\n\n";
    
    // Eliminar tabla anterior
    $pdo->exec("DROP TABLE IF EXISTS users");
    echo "✓ Tabla anterior eliminada\n";
    
    // Crear tabla con estructura del Módulo 3
    $sql = "CREATE TABLE users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        rol ENUM('administrador', 'vendedor', 'consulta') DEFAULT 'consulta',
        activo TINYINT(1) DEFAULT 1,
        reset_token VARCHAR(64),
        reset_token_expira DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✓ Tabla users creada con nueva estructura\n";
    
    // Insertar usuario admin por defecto
    $password = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);
    $sql = "INSERT INTO users (email, password, nombre, rol, activo) 
            VALUES ('admin@inversiones.com', ?, 'Administrador', 'administrador', 1)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$password]);
    
    echo "✓ Usuario admin creado\n\n";
    echo "========================================\n";
    echo "CREDENCIALES:\n";
    echo "========================================\n";
    echo "Email: admin@inversiones.com\n";
    echo "Password: admin123\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
