<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-building"></i> Reporte: Ventas por Proyecto
            </h1>
            <p class="text-muted mb-0">Análisis comparativo de rendimiento por proyecto</p>
        </div>
        <a href="/reportes" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Reportes
        </a>
    </div>

    <!-- Resumen General -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h6 class="text-muted">Total Proyectos</h6>
                    <h2 class="text-primary mb-0"><?= count($proyectos) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h6 class="text-muted">Total Ventas</h6>
                    <h2 class="text-success mb-0"><?= formatMoney($totalVentasGeneral) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-info">
                <div class="card-body">
                    <h6 class="text-muted">Lotes Vendidos</h6>
                    <h2 class="text-info mb-0"><?= array_sum(array_column($proyectos, 'lotes_vendidos')) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h6 class="text-muted">Lotes Disponibles</h6>
                    <h2 class="text-warning mb-0"><?= array_sum(array_column($proyectos, 'lotes_disponibles')) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Ventas -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-bar-chart-fill"></i> Comparativa de Ventas por Proyecto</h5>
        </div>
        <div class="card-body">
            <canvas id="graficoVentasProyecto" height="80"></canvas>
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

    <!-- Tabla Detallada -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Nombre Proyecto</th>
                            <th>Ubicación</th>
                            <th class="text-center">Total Lotes</th>
                            <th class="text-center">Disponibles</th>
                            <th class="text-center">Vendidos</th>
                            <th class="text-center">% Avance</th>
                            <th class="text-end">Valor Ventas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proyectos as $proyecto): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($proyecto['codigo']) ?></strong></td>
                                <td><?= htmlspecialchars($proyecto['nombre']) ?></td>
                                <td><?= htmlspecialchars($proyecto['ubicacion']) ?></td>
                                <td class="text-center"><?= $proyecto['total_lotes'] ?></td>
                                <td class="text-center">
                                    <span class="badge bg-warning"><?= $proyecto['lotes_disponibles'] ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success"><?= $proyecto['lotes_vendidos'] ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: <?= $proyecto['porcentaje_vendido'] ?>%"
                                             aria-valuenow="<?= $proyecto['porcentaje_vendido'] ?>" 
                                             aria-valuemin="0" aria-valuemax="100">
                                            <?= $proyecto['porcentaje_vendido'] ?>%
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <strong class="text-success"><?= formatMoney($proyecto['valor_ventas']) ?></strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="7" class="text-end">TOTAL GENERAL:</td>
                            <td class="text-end text-success"><?= formatMoney($totalVentasGeneral) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Gráfico de Ventas por Proyecto
const ctx = document.getElementById('graficoVentasProyecto').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($grafico['labels']) ?>,
        datasets: [{
            label: 'Valor Ventas',
            data: <?= json_encode($grafico['valores']) ?>,
            backgroundColor: 'rgba(25, 135, 84, 0.7)',
            borderColor: 'rgba(25, 135, 84, 1)',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Ventas: $' + context.parsed.y.toLocaleString('es-CO');
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + (value / 1000000).toFixed(1) + 'M';
                    }
                }
            }
        }
    }
});

function exportarPDF() {
    alert('Funcionalidad de exportación a PDF en desarrollo.');
}

function exportarExcel() {
    alert('Funcionalidad de exportación a Excel en desarrollo.');
}
</script>
