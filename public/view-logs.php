<?php
// Visor de logs simple para debugging
session_start();

// Verificar autenticación
if (!isset($_SESSION['user'])) {
    die('Acceso denegado. Debe iniciar sesión.');
}

$logFile = __DIR__ . '/storage/logs/app.log';
$lines = 100;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs - Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1e1e1e; color: #d4d4d4; font-family: 'Courier New', monospace; }
        .log-viewer { background: #252526; padding: 20px; border-radius: 5px; max-height: 80vh; overflow-y: auto; }
        .log-line { padding: 3px 0; border-bottom: 1px solid #333; }
        .error { color: #f48771; }
        .warning { color: #dcdcaa; }
        .info { color: #4ec9b0; }
        .debug { color: #9cdcfe; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between mb-3">
            <h2 class="text-white">📋 Logs del Sistema</h2>
            <div>
                <button onclick="location.reload()" class="btn btn-sm btn-primary">🔄 Actualizar</button>
                <a href="/dashboard" class="btn btn-sm btn-secondary">← Volver</a>
            </div>
        </div>
        
        <div class="alert alert-dark">
            <strong>Archivo:</strong> <?= htmlspecialchars($logFile) ?><br>
            <strong>Existe:</strong> <?= file_exists($logFile) ? '✅ Sí' : '❌ No' ?>
        </div>

        <div class="log-viewer">
            <?php
            if (file_exists($logFile)) {
                $allLines = file($logFile);
                $displayLines = array_slice($allLines, -$lines);
                
                if (empty($displayLines)) {
                    echo '<p class="text-muted">No hay logs disponibles</p>';
                } else {
                    foreach ($displayLines as $line) {
                        $line = htmlspecialchars($line);
                        $class = '';
                        
                        if (strpos($line, '[ERROR]') !== false) {
                            $class = 'error';
                        } elseif (strpos($line, '[WARNING]') !== false) {
                            $class = 'warning';
                        } elseif (strpos($line, '[INFO]') !== false) {
                            $class = 'info';
                        } elseif (strpos($line, '[DEBUG]') !== false) {
                            $class = 'debug';
                        }
                        
                        echo "<div class='log-line {$class}'>{$line}</div>";
                    }
                }
            } else {
                echo '<p class="text-warning">⚠️ El archivo de logs no existe todavía. Se creará cuando ocurra el primer evento.</p>';
            }
            ?>
        </div>
    </div>
    
    <script>
        // Auto-scroll al final
        window.onload = function() {
            const viewer = document.querySelector('.log-viewer');
            viewer.scrollTop = viewer.scrollHeight;
        };
    </script>
</body>
</html>
