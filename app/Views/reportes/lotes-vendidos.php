<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-cash-coin"></i> Reporte: Lotes Vendidos
            </h1>
            <p class="text-muted mb-0">Detalle completo de ventas realizadas</p>
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
            <form method="GET" action="/reportes/lotes-vendidos">
                <div class="row g-3">
                    <div class="col-md-3">
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
                    <div class="col-md-3">
                        <label for="vendedor_id" class="form-label">Vendedor</label>
                        <select name="vendedor_id" id="vendedor_id" class="form-select">
                            <option value="">Todos los vendedores</option>
                            <?php foreach ($vendedores as $vendedor): ?>
                                <option value="<?= $vendedor['id'] ?>" <?= $filtros['vendedor_id'] == $vendedor['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($vendedor['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="fecha_desde" class="form-label">Desde</label>
                        <input type="date" name="fecha_desde" id="fecha_desde" class="form-control" 
                               value="<?= $filtros['fecha_desde'] ?? '' ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="fecha_hasta" class="form-label">Hasta</label>
                        <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control" 
                               value="<?= $filtros['fecha_hasta'] ?? '' ?>">
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

    <!-- Resumen -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h6 class="text-muted">Total Lotes Vendidos</h6>
                    <h2 class="text-success mb-0"><?= count($lotes) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h6 class="text-muted">Valor Total Ventas</h6>
                    <h2 class="text-primary mb-0"><?= formatMoney($totalVentas) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-info">
                <div class="card-body">
                    <h6 class="text-muted">Total Comisiones</h6>
                    <h2 class="text-info mb-0"><?= formatMoney($totalComisiones) ?></h2>
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

    <!-- Tabla de Lotes Vendidos -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaLotesVendidos">
                    <thead class="table-light">
                        <tr>
                            <th>Código Lote</th>
                            <th>Proyecto</th>
                            <th>Cliente</th>
                            <th>Documento</th>
                            <th>Vendedor</th>
                            <th>Fecha Venta</th>
                            <th class="text-end">Precio Venta</th>
                            <th class="text-end">Comisión (3%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($lotes)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mt-2">No se encontraron lotes vendidos con los filtros seleccionados</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($lotes as $lote): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($lote['codigo_lote']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?= htmlspecialchars($lote['proyecto_codigo']) ?></span>
                                        <?= htmlspecialchars($lote['proyecto_nombre']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($lote['cliente_nombre'] ?? 'Sin asignar') ?></td>
                                    <td><?= htmlspecialchars($lote['cliente_documento'] ?? '-') ?></td>
                                    <td>
                                        <i class="bi bi-person-badge text-info"></i>
                                        <?= htmlspecialchars($lote['vendedor_nombre'] ?? 'Sin vendedor') ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($lote['fecha_venta'])) ?></td>
                                    <td class="text-end">
                                        <strong class="text-success"><?= formatMoney($lote['precio_venta']) ?></strong>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-info"><?= formatMoney($lote['comision_vendedor']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($lotes)): ?>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="6" class="text-end">TOTALES:</td>
                                <td class="text-end text-success"><?= formatMoney($totalVentas) ?></td>
                                <td class="text-end text-info"><?= formatMoney($totalComisiones) ?></td>
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
    alert('Funcionalidad de exportación a PDF en desarrollo.\nSe implementará con librería TCPDF o Dompdf.');
}

function exportarExcel() {
    alert('Funcionalidad de exportación a Excel en desarrollo.\nSe implementará con librería PhpSpreadsheet.');
}
</script>
