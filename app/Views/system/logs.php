<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-file-text"></i> Logs del Sistema
            </h1>
            <p class="text-muted mb-0">Últimas 100 líneas del archivo de logs</p>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-primary" id="btnRefresh">
                <i class="bi bi-arrow-clockwise"></i> Actualizar
            </button>
            <a href="/logs/download" class="btn btn-info">
                <i class="bi bi-download"></i> Descargar
            </a>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalClear">
                <i class="bi bi-trash"></i> Limpiar
            </button>
        </div>
    </div>

    <!-- Info del archivo -->
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        <strong>Archivo:</strong> <code><?= htmlspecialchars($logFile) ?></code><br>
        <strong>Total de líneas:</strong> <span id="logCount"><?= count($logs) ?></span>
    </div>

    <!-- Log Viewer -->
    <div class="card shadow">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-terminal"></i> Visor de Logs</h5>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="autoRefresh">
                <label class="form-check-label" for="autoRefresh">
                    Auto-actualizar (5s)
                </label>
            </div>
        </div>
        <div class="card-body p-0">
            <div id="logViewer" style="background: #1e1e1e; color: #d4d4d4; font-family: 'Courier New', monospace; font-size: 13px; padding: 20px; max-height: 600px; overflow-y: auto;">
                <?php if (empty($logs)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-3">No hay logs disponibles</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <?php
                        $line = htmlspecialchars($log);
                        // Colorear según nivel
                        if (strpos($line, '[ERROR]') !== false) {
                            $color = '#f48771';
                        } elseif (strpos($line, '[WARNING]') !== false) {
                            $color = '#dcdcaa';
                        } elseif (strpos($line, '[INFO]') !== false) {
                            $color = '#4ec9b0';
                        } elseif (strpos($line, '[DEBUG]') !== false) {
                            $color = '#9cdcfe';
                        } else {
                            $color = '#d4d4d4';
                        }
                        ?>
                        <div style="color: <?= $color ?>; padding: 2px 0; border-bottom: 1px solid #2d2d2d;">
                            <?= $line ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">ERRORES</h6>
                    <h2 class="text-danger mb-0" id="errorCount">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">ADVERTENCIAS</h6>
                    <h2 class="text-warning mb-0" id="warningCount">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">INFO</h6>
                    <h2 class="text-info mb-0" id="infoCount">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">DEBUG</h6>
                    <h2 class="text-secondary mb-0" id="debugCount">0</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Limpiar -->
<div class="modal fade" id="modalClear" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Confirmar Limpieza</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>¿Está seguro que desea limpiar todos los logs?</strong></p>
                <p class="text-muted mb-0">Esta acción no se puede deshacer. Se recomienda descargar los logs antes de limpiarlos.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" action="/logs/clear" style="display: inline;">
                    <?= csrfField() ?>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Sí, Limpiar Logs
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const logViewer = document.getElementById('logViewer');
    const autoRefreshCheckbox = document.getElementById('autoRefresh');
    let refreshInterval = null;

    // Función para actualizar logs
    function refreshLogs() {
        fetch('/logs/fetch?lines=100')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.logs.length > 0) {
                    let html = '';
                    let errorCount = 0;
                    let warningCount = 0;
                    let infoCount = 0;
                    let debugCount = 0;

                    data.logs.forEach(log => {
                        const line = escapeHtml(log);
                        let color = '#d4d4d4';

                        if (line.includes('[ERROR]')) {
                            color = '#f48771';
                            errorCount++;
                        } else if (line.includes('[WARNING]')) {
                            color = '#dcdcaa';
                            warningCount++;
                        } else if (line.includes('[INFO]')) {
                            color = '#4ec9b0';
                            infoCount++;
                        } else if (line.includes('[DEBUG]')) {
                            color = '#9cdcfe';
                            debugCount++;
                        }

                        html += `<div style="color: ${color}; padding: 2px 0; border-bottom: 1px solid #2d2d2d;">${line}</div>`;
                    });

                    logViewer.innerHTML = html;
                    logViewer.scrollTop = logViewer.scrollHeight;

                    // Actualizar contadores
                    document.getElementById('errorCount').textContent = errorCount;
                    document.getElementById('warningCount').textContent = warningCount;
                    document.getElementById('infoCount').textContent = infoCount;
                    document.getElementById('debugCount').textContent = debugCount;
                    document.getElementById('logCount').textContent = data.count;
                }
            })
            .catch(error => console.error('Error al actualizar logs:', error));
    }

    // Botón de refrescar manual
    document.getElementById('btnRefresh').addEventListener('click', refreshLogs);

    // Auto-refresh
    autoRefreshCheckbox.addEventListener('change', function() {
        if (this.checked) {
            refreshInterval = setInterval(refreshLogs, 5000);
        } else {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        }
    });

    // Función auxiliar para escapar HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Scroll automático al final
    logViewer.scrollTop = logViewer.scrollHeight;

    // Contar logs iniciales
    const initialLogs = logViewer.textContent;
    document.getElementById('errorCount').textContent = (initialLogs.match(/\[ERROR\]/g) || []).length;
    document.getElementById('warningCount').textContent = (initialLogs.match(/\[WARNING\]/g) || []).length;
    document.getElementById('infoCount').textContent = (initialLogs.match(/\[INFO\]/g) || []).length;
    document.getElementById('debugCount').textContent = (initialLogs.match(/\[DEBUG\]/g) || []).length;
});
</script>
