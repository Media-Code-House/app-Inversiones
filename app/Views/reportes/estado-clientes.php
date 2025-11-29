<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-people"></i> Reporte: Estado de Clientes
            </h1>
            <p class="text-muted mb-0">Resumen financiero consolidado por cliente</p>
        </div>
        <a href="/reportes" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Reportes
        </a>
    </div>

    <!-- Estadísticas Generales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h6 class="text-muted">Total Clientes</h6>
                    <h2 class="text-primary mb-0"><?= $estadisticas['total_clientes'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <h6 class="text-muted">Clientes Críticos</h6>
                    <h2 class="text-danger mb-0"><?= $estadisticas['clientes_criticos'] ?></h2>
                    <small class="text-muted">Mora > 30 días</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h6 class="text-muted">En Mora</h6>
                    <h2 class="text-warning mb-0"><?= $estadisticas['clientes_en_mora'] ?></h2>
                    <small class="text-muted">Con atrasos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h6 class="text-muted">Al Día</h6>
                    <h2 class="text-success mb-0"><?= $estadisticas['clientes_al_dia'] ?></h2>
                    <small class="text-muted">Sin atrasos</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Saldo Total de Cartera -->
    <div class="alert alert-info d-flex align-items-center justify-content-between">
        <div>
            <h5 class="mb-0"><i class="bi bi-wallet2"></i> Saldo Total de Cartera Consolidada</h5>
        </div>
        <div>
            <h3 class="mb-0 text-primary"><?= formatMoney($estadisticas['saldo_total_cartera']) ?></h3>
        </div>
    </div>

    <!-- Botones de Exportación -->
    <div class="d-flex justify-content-end gap-2 mb-3">
        <button class="btn btn-outline-danger" onclick="exportarPDF()">
            <i class="bi bi-file-pdf"></i> Exportar PDF
        </button>
        <button class="btn btn-outline-success" onclick="exportarExcel()">
            <i class="bi bi-file-excel"></i> Exportar Excel
        </button>
    </div>

    <!-- Tabla de Clientes -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Cliente</th>
                            <th>Documento</th>
                            <th class="text-center">Lotes</th>
                            <th class="text-end">Valor Compras</th>
                            <th class="text-end">Saldo Pendiente</th>
                            <th class="text-center">Cuotas Vencidas</th>
                            <th class="text-center">Días Mora Máx.</th>
                            <th class="text-center">Estado Crédito</th>
                            <th>Contacto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clientes)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mt-2">No hay clientes registrados con compras</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($clientes as $cliente): ?>
                                <?php
                                // Determinar clase de fila según estado
                                $filaClass = '';
                                if ($cliente['estado_credito'] === 'CRÍTICO') $filaClass = 'table-danger';
                                elseif ($cliente['estado_credito'] === 'EN MORA') $filaClass = 'table-warning';
                                elseif ($cliente['estado_credito'] === 'PAGADO') $filaClass = 'table-success';
                                
                                // Badge del estado
                                $badgeClass = 'secondary';
                                if ($cliente['estado_credito'] === 'CRÍTICO') $badgeClass = 'danger';
                                elseif ($cliente['estado_credito'] === 'EN MORA') $badgeClass = 'warning';
                                elseif ($cliente['estado_credito'] === 'AL DÍA') $badgeClass = 'success';
                                elseif ($cliente['estado_credito'] === 'PAGADO') $badgeClass = 'info';
                                ?>
                                <tr class="<?= $filaClass ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                                <i class="bi bi-person-fill text-primary"></i>
                                            </div>
                                            <div>
                                                <strong><?= htmlspecialchars($cliente['cliente_nombre']) ?></strong>
                                                <?php if ($cliente['estado_credito'] === 'CRÍTICO'): ?>
                                                    <br><small class="text-danger fw-bold">⚠️ REQUIERE ATENCIÓN</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($cliente['tipo_documento']) ?>
                                        <?= htmlspecialchars($cliente['numero_documento']) ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?= $cliente['total_lotes_comprados'] ?></span>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-info"><?= formatMoney($cliente['valor_total_compras']) ?></strong>
                                    </td>
                                    <td class="text-end">
                                        <strong class="<?= $cliente['saldo_pendiente_global'] > 0 ? 'text-danger' : 'text-success' ?>">
                                            <?= formatMoney($cliente['saldo_pendiente_global']) ?>
                                        </strong>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($cliente['cuotas_vencidas'] > 0): ?>
                                            <span class="badge bg-danger"><?= $cliente['cuotas_vencidas'] ?></span>
                                        <?php else: ?>
                                            <span class="text-success">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($cliente['dias_mora_maxima'] > 0): ?>
                                            <span class="badge bg-danger"><?= $cliente['dias_mora_maxima'] ?> días</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $badgeClass ?>">
                                            <?= $cliente['estado_credito'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($cliente['telefono']): ?>
                                            <a href="tel:<?= $cliente['telefono'] ?>" class="btn btn-sm btn-outline-info" 
                                               title="<?= htmlspecialchars($cliente['telefono']) ?>">
                                                <i class="bi bi-telephone"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($cliente['email']): ?>
                                            <a href="mailto:<?= $cliente['email'] ?>" class="btn btn-sm btn-outline-secondary"
                                               title="<?= htmlspecialchars($cliente['email']) ?>">
                                                <i class="bi bi-envelope"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($clientes)): ?>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="4" class="text-end">TOTAL CARTERA:</td>
                                <td class="text-end text-danger"><?= formatMoney($estadisticas['saldo_total_cartera']) ?></td>
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Distribución por Estado -->
    <?php if (!empty($clientes)): ?>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-pie-chart"></i> Distribución por Estado</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="graficoEstadoClientes" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-exclamation-circle"></i> Recomendaciones</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i>
                                <strong><?= $estadisticas['clientes_al_dia'] ?> clientes</strong> están al día con sus pagos
                            </li>
                            <?php if ($estadisticas['clientes_en_mora'] > 0): ?>
                                <li class="mb-2">
                                    <i class="bi bi-exclamation-triangle text-warning"></i>
                                    <strong><?= $estadisticas['clientes_en_mora'] ?> clientes</strong> requieren seguimiento por atrasos
                                </li>
                            <?php endif; ?>
                            <?php if ($estadisticas['clientes_criticos'] > 0): ?>
                                <li class="mb-2">
                                    <i class="bi bi-x-circle text-danger"></i>
                                    <strong><?= $estadisticas['clientes_criticos'] ?> clientes</strong> en estado crítico - acción inmediata
                                </li>
                            <?php endif; ?>
                            <li class="mt-3 text-muted">
                                <small><i class="bi bi-info-circle"></i> Se recomienda contactar prioritariamente a clientes en mora > 15 días</small>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
<?php if (!empty($clientes)): ?>
// Gráfico de distribución por estado
const ctx = document.getElementById('graficoEstadoClientes').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Crítico', 'En Mora', 'Al Día', 'Pagado'],
        datasets: [{
            data: [
                <?= $estadisticas['clientes_criticos'] ?>,
                <?= $estadisticas['clientes_en_mora'] ?>,
                <?= $estadisticas['clientes_al_dia'] ?>,
                <?= $estadisticas['total_clientes'] - $estadisticas['clientes_criticos'] - $estadisticas['clientes_en_mora'] - $estadisticas['clientes_al_dia'] ?>
            ],
            backgroundColor: [
                'rgba(220, 53, 69, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(25, 135, 84, 0.8)',
                'rgba(13, 202, 240, 0.8)'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
<?php endif; ?>

function exportarPDF() {
    alert('Funcionalidad de exportación a PDF en desarrollo.');
}

function exportarExcel() {
    alert('Funcionalidad de exportación a Excel en desarrollo.');
}
</script>
