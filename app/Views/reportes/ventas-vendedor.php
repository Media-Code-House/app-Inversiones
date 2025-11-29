<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-person-badge"></i> Reporte: Ventas por Vendedor
            </h1>
            <p class="text-muted mb-0">Desempeño individual y comisiones generadas</p>
        </div>
        <a href="/reportes" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Reportes
        </a>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros de Búsqueda</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="/reportes/ventas-vendedor">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="fecha_desde" class="form-label">Desde</label>
                        <input type="date" name="fecha_desde" id="fecha_desde" class="form-control" 
                               value="<?= $filtros['fecha_desde'] ?? '' ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="fecha_hasta" class="form-label">Hasta</label>
                        <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control" 
                               value="<?= $filtros['fecha_hasta'] ?? '' ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center border-info">
                <div class="card-body">
                    <h6 class="text-muted">Vendedores Activos</h6>
                    <h2 class="text-info mb-0"><?= count($vendedores) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h6 class="text-muted">Total Ventas</h6>
                    <h2 class="text-success mb-0"><?= formatMoney($totalVentasGeneral) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h6 class="text-muted">Total Comisiones</h6>
                    <h2 class="text-primary mb-0"><?= formatMoney($totalComisionesGeneral) ?></h2>
                </div>
            </div>
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

    <!-- Tabla de Vendedores -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Vendedor</th>
                            <th>Email</th>
                            <th class="text-center">Lotes Vendidos</th>
                            <th class="text-end">Total Ventas</th>
                            <th class="text-end">Comisiones (3%)</th>
                            <th>Primera Venta</th>
                            <th>Última Venta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($vendedores)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mt-2">No se encontraron ventas en el periodo seleccionado</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($vendedores as $vendedor): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-info bg-opacity-10 p-2 me-2">
                                                <i class="bi bi-person-fill text-info"></i>
                                            </div>
                                            <strong><?= htmlspecialchars($vendedor['vendedor_nombre']) ?></strong>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($vendedor['vendedor_email']) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-success"><?= $vendedor['total_lotes_vendidos'] ?></span>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success"><?= formatMoney($vendedor['total_ventas']) ?></strong>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-primary"><?= formatMoney($vendedor['total_comisiones']) ?></span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($vendedor['primera_venta'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($vendedor['ultima_venta'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($vendedores)): ?>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="3" class="text-end">TOTALES:</td>
                                <td class="text-end text-success"><?= formatMoney($totalVentasGeneral) ?></td>
                                <td class="text-end text-primary"><?= formatMoney($totalComisionesGeneral) ?></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Ranking Top 3 -->
    <?php if (count($vendedores) >= 3): ?>
        <div class="row mt-4">
            <div class="col-12">
                <h5 class="mb-3"><i class="bi bi-trophy"></i> Top 3 Vendedores del Periodo</h5>
            </div>
            <?php 
            $top3 = array_slice($vendedores, 0, 3);
            $medals = ['🥇', '🥈', '🥉'];
            $colors = ['warning', 'secondary', 'danger'];
            ?>
            <?php foreach ($top3 as $index => $vendedor): ?>
                <div class="col-md-4">
                    <div class="card border-<?= $colors[$index] ?>">
                        <div class="card-body text-center">
                            <div style="font-size: 3rem;"><?= $medals[$index] ?></div>
                            <h5><?= htmlspecialchars($vendedor['vendedor_nombre']) ?></h5>
                            <p class="mb-1"><strong><?= $vendedor['total_lotes_vendidos'] ?></strong> lotes vendidos</p>
                            <h4 class="text-success"><?= formatMoney($vendedor['total_ventas']) ?></h4>
                            <p class="text-muted mb-0">Comisión: <?= formatMoney($vendedor['total_comisiones']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function exportarPDF() {
    alert('Funcionalidad de exportación a PDF en desarrollo.');
}

function exportarExcel() {
    alert('Funcionalidad de exportación a Excel en desarrollo.');
}
</script>
