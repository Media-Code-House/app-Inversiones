<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-exclamation-triangle"></i> Reporte: Cartera Pendiente
            </h1>
            <p class="text-muted mb-0">Control de cuotas pendientes y en mora</p>
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
            <form method="GET" action="/reportes/cartera">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label for="proyecto_id" class="form-label">Proyecto</label>
                        <select name="proyecto_id" id="proyecto_id" class="form-select">
                            <option value="">Todos los proyectos</option>
                            <?php foreach ($proyectos as $proyecto): ?>
                                <option value="<?= $proyecto['id'] ?>" <?= $filtros['proyecto_id'] == $proyecto['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($proyecto['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label for="estado_mora" class="form-label">Estado de Mora</label>
                        <select name="estado_mora" id="estado_mora" class="form-select">
                            <option value="">Todos</option>
                            <option value="vencidas" <?= $filtros['estado_mora'] === 'vencidas' ? 'selected' : '' ?>>Solo Vencidas</option>
                            <option value="vigentes" <?= $filtros['estado_mora'] === 'vigentes' ? 'selected' : '' ?>>Solo Vigentes</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen de Cartera -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h6 class="text-muted">Total Cartera</h6>
                    <h2 class="text-primary mb-0"><?= formatMoney($totalCartera) ?></h2>
                    <small class="text-muted"><?= $cantidadCuotasTotal ?> cuotas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <h6 class="text-muted">En Mora</h6>
                    <h2 class="text-danger mb-0"><?= formatMoney($totalMora) ?></h2>
                    <small class="text-muted"><?= $cantidadCuotasVencidas ?> cuotas vencidas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h6 class="text-muted">Vigente</h6>
                    <h2 class="text-success mb-0"><?= formatMoney($totalVigente) ?></h2>
                    <small class="text-muted">Al día</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h6 class="text-muted">% Mora</h6>
                    <h2 class="text-warning mb-0">
                        <?= $totalCartera > 0 ? round(($totalMora / $totalCartera) * 100, 1) : 0 ?>%
                    </h2>
                    <small class="text-muted">Índice de morosidad</small>
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

    <!-- Tabla de Cartera -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Cliente</th>
                            <th>Proyecto</th>
                            <th>Lote</th>
                            <th class="text-center">Cuota #</th>
                            <th>Fecha Vencimiento</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Días Mora</th>
                            <th class="text-end">Saldo Pendiente</th>
                            <th>Contacto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cuotas)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-check-circle" style="font-size: 3rem;"></i>
                                    <p class="mt-2">¡Excelente! No hay cuotas pendientes con los filtros seleccionados</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cuotas as $cuota): ?>
                                <tr class="<?= $cuota['estado_mora'] === 'VENCIDA' ? 'table-danger' : ($cuota['estado_mora'] === 'POR VENCER' ? 'table-warning' : '') ?>">
                                    <td>
                                        <strong><?= htmlspecialchars($cuota['cliente_nombre']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($cuota['proyecto_nombre']) ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?= htmlspecialchars($cuota['codigo_lote']) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <strong>#<?= $cuota['numero_cuota'] ?></strong>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($cuota['fecha_vencimiento'])) ?></td>
                                    <td class="text-center">
                                        <?php
                                        $badgeClass = 'secondary';
                                        if ($cuota['estado_mora'] === 'VENCIDA') $badgeClass = 'danger';
                                        elseif ($cuota['estado_mora'] === 'POR VENCER') $badgeClass = 'warning';
                                        elseif ($cuota['estado_mora'] === 'VIGENTE') $badgeClass = 'success';
                                        ?>
                                        <span class="badge bg-<?= $badgeClass ?>"><?= $cuota['estado_mora'] ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($cuota['dias_mora'] > 0): ?>
                                            <span class="badge bg-danger"><?= $cuota['dias_mora'] ?> días</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <strong class="<?= $cuota['dias_mora'] > 0 ? 'text-danger' : 'text-primary' ?>">
                                            <?= formatMoney($cuota['saldo']) ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?php if ($cuota['cliente_telefono']): ?>
                                            <a href="tel:<?= $cuota['cliente_telefono'] ?>" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-telephone"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($cuota['cliente_email']): ?>
                                            <a href="mailto:<?= $cuota['cliente_email'] ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-envelope"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($cuotas)): ?>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="7" class="text-end">TOTAL CARTERA:</td>
                                <td class="text-end text-primary"><?= formatMoney($totalCartera) ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function exportarPDF() {
    alert('Funcionalidad de exportación a PDF en desarrollo.');
}

function exportarExcel() {
    alert('Funcionalidad de exportación a Excel en desarrollo.');
}
</script>
