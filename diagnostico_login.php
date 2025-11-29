<?php
/**
 * Script de diagnóstico para verificar la tabla users
 * y validar la compatibilidad con el login
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';

// Estilo CSS
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Diagnóstico de Login</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        h2 { color: #34495e; margin-top: 30px; }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .warning { color: #f39c12; font-weight: bold; }
        .info { background: #e8f4f8; padding: 15px; border-left: 4px solid #3498db; margin: 15px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #34495e; color: white; }
        tr:hover { background: #f5f5f5; }
        code { background: #ecf0f1; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        .step { background: #fff; border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .step-number { background: #3498db; color: white; width: 30px; height: 30px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico de Sistema de Login</h1>
        <p>Verificando la configuración de la tabla <code>users</code> y compatibilidad con el sistema de autenticación.</p>

        <?php
        try {
            $db = Database::getInstance();

            // Paso 1: Verificar conexión
            echo "<h2>✓ Paso 1: Conexión a Base de Datos</h2>";
            echo "<p class='success'>✓ Conexión exitosa a la base de datos</p>";

            // Paso 2: Verificar estructura de tabla users
            echo "<h2>📋 Paso 2: Estructura de la Tabla Users</h2>";
            
            $columns = $db->fetchAll("SHOW COLUMNS FROM users");
            
            echo "<table>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Key</th><th>Default</th></tr>";
            
            $columnsNames = [];
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td><strong>" . htmlspecialchars($col['Field']) . "</strong></td>";
                echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
                echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
                echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
                echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
                echo "</tr>";
                
                $columnsNames[] = $col['Field'];
            }
            echo "</table>";

            // Paso 3: Verificar columnas requeridas
            echo "<h2>🔍 Paso 3: Validación de Columnas Requeridas</h2>";
            
            $requiredColumns = [
                'id' => 'Identificador único del usuario',
                'email' => 'Email para login',
                'password' => 'Contraseña hasheada',
                'nombre' => 'Nombre completo del usuario',
                'rol' => 'Rol del usuario (enum: admin, usuario)',
                'activo' => 'Estado activo/inactivo'
            ];

            $allCorrect = true;
            foreach ($requiredColumns as $colName => $description) {
                if (in_array($colName, $columnsNames)) {
                    echo "<p class='success'>✓ <code>{$colName}</code> - {$description}</p>";
                } else {
                    echo "<p class='error'>✗ <code>{$colName}</code> - {$description} <strong>NO EXISTE</strong></p>";
                    $allCorrect = false;
                }
            }

            // Verificar columnas obsoletas
            $deprecatedColumns = ['password_hash', 'is_active', 'rol_id'];
            $hasDeprecated = false;
            
            foreach ($deprecatedColumns as $depCol) {
                if (in_array($depCol, $columnsNames)) {
                    echo "<p class='warning'>⚠ Columna obsoleta detectada: <code>{$depCol}</code></p>";
                    $hasDeprecated = true;
                }
            }

            // Paso 4: Verificar usuarios existentes
            echo "<h2>👥 Paso 4: Usuarios en el Sistema</h2>";
            
            $users = $db->fetchAll("SELECT id, email, nombre, rol, activo, created_at FROM users ORDER BY id ASC");
            
            if (empty($users)) {
                echo "<p class='warning'>⚠ No hay usuarios registrados en el sistema</p>";
                echo "<div class='info'><strong>Acción recomendada:</strong> Necesitas crear al menos un usuario administrador para acceder al sistema.</div>";
            } else {
                echo "<table>";
                echo "<tr><th>ID</th><th>Email</th><th>Nombre</th><th>Rol</th><th>Activo</th><th>Creado</th></tr>";
                
                foreach ($users as $user) {
                    $activoClass = $user['activo'] ? 'success' : 'error';
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['nombre']) . "</td>";
                    echo "<td><strong>" . htmlspecialchars($user['rol']) . "</strong></td>";
                    echo "<td class='{$activoClass}'>" . ($user['activo'] ? '✓ Activo' : '✗ Inactivo') . "</td>";
                    echo "<td>" . htmlspecialchars($user['created_at']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
                
                echo "<p class='success'>✓ Total de usuarios: <strong>" . count($users) . "</strong></p>";
            }

            // Paso 5: Resumen y Recomendaciones
            echo "<h2>📊 Paso 5: Resumen y Recomendaciones</h2>";

            if ($hasDeprecated) {
                echo "<div class='step'>";
                echo "<span class='step-number'>1</span>";
                echo "<strong>Migrar estructura de tabla users</strong>";
                echo "<p>Tu tabla users tiene columnas obsoletas. Ejecuta el script de migración:</p>";
                echo "<code>https://inversiones.mch.com.co/migrar_bd.php</code>";
                echo "</div>";
            }

            if (!$allCorrect) {
                echo "<div class='step'>";
                echo "<span class='step-number'>2</span>";
                echo "<strong>Faltan columnas requeridas</strong>";
                echo "<p>La estructura de la tabla no coincide con la esperada. Ejecuta la migración o verifica manualmente.</p>";
                echo "</div>";
            }

            if (empty($users)) {
                echo "<div class='step'>";
                echo "<span class='step-number'>3</span>";
                echo "<strong>Crear usuario administrador</strong>";
                echo "<p>No hay usuarios en el sistema. Puedes usar este SQL:</p>";
                echo "<pre style='background:#2c3e50;color:#ecf0f1;padding:15px;border-radius:5px;overflow:auto;'>";
                echo "INSERT INTO users (email, password, nombre, rol, activo)\n";
                echo "VALUES (\n";
                echo "  'admin@sistema.com',\n";
                echo "  '" . password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]) . "',\n";
                echo "  'Administrador',\n";
                echo "  'admin',\n";
                echo "  1\n";
                echo ");</pre>";
                echo "<p><strong>Credenciales:</strong> admin@sistema.com / admin123</p>";
                echo "</div>";
            }

            if ($allCorrect && !$hasDeprecated && !empty($users)) {
                echo "<div class='step' style='background:#d4edda;border-color:#c3e6cb;'>";
                echo "<h3 style='color:#155724;margin-top:0;'>✓ Sistema Configurado Correctamente</h3>";
                echo "<p>Tu tabla users está correctamente configurada y puedes usar el sistema de login sin problemas.</p>";
                echo "<p><a href='/auth/login' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;margin-top:10px;'>Ir al Login →</a></p>";
                echo "</div>";
            }

        } catch (Exception $e) {
            echo "<div class='step' style='background:#f8d7da;border-color:#f5c6cb;'>";
            echo "<h3 style='color:#721c24;margin-top:0;'>✗ Error de Conexión</h3>";
            echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p>Verifica tu configuración en <code>config/config.php</code></p>";
            echo "</div>";
        }
        ?>

        <hr style="margin: 40px 0;">
        <p style="text-align: center; color: #7f8c8d;">
            <small>Sistema de Gestión de Lotes e Inversiones | Diagnóstico v1.0</small>
        </p>
    </div>
</body>
</html>
